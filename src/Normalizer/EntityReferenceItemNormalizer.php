<?php

/**
 * @file
 * Contains \Drupal\hme\Normalizer\EntityReferenceItemNormalizer.
 */

namespace Drupal\hme\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\rest\LinkManager\LinkManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\serialization\EntityResolver\EntityResolverInterface;
use Drupal\serialization\EntityResolver\UuidReferenceInterface;
use Drupal\Core\Url;

/**
 * Converts the Drupal entity reference item object to SIREN subentity array structure.
 */
class EntityReferenceItemNormalizer extends FieldItemNormalizer implements UuidReferenceInterface
{
    /**
     * The interface or class that this Normalizer supports.
     *
     * @var string
     */
    protected $supportedInterfaceOrClass = 'Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem';

    /**
     * The hypermedia link manager.
     *
     * @var \Drupal\rest\LinkManager\LinkManagerInterface
     */
    protected $linkManager;

    /**
     * The entity resolver.
     *
     * @var \Drupal\serialization\EntityResolver\EntityResolverInterface
     */
    protected $entityResolver;

    /**
     * The entity query factory.
     *
     * @var \Drupal\Core\Entity\Query\QueryFactory
     */
    protected $queryFactory;

    /**
     * Constructs an EntityReferenceItemNormalizer object.
     *
     * @param \Drupal\rest\LinkManager\LinkManagerInterface $link_manager
     *   The hypermedia link manager.
     * @param \Drupal\serialization\EntityResolver\EntityResolverInterface $entity_resolver
     *   The entity resolver.
     */
    public function __construct(LinkManagerInterface $link_manager, EntityResolverInterface $entity_resolver, QueryFactory $entity_query_factory)
    {
        $this->linkManager = $link_manager;
        $this->entityResolver = $entity_resolver;
        $this->queryFactory = $entity_query_factory;
    }

    /**
     * Override the supportsNormalization function because entities require a mapper.
     */
    public function supportsNormalization($data, $format = NULL)
    {
        $entityRefl = new \ReflectionClass($data);
        if($entityRefl->isSubclassOf($this->supportedInterfaceOrClass)) {
            $entity = entity_load($data->getFieldDefinition()->getSetting('target_type'), $data->get("target_id")->getValue());
            $query = $this->queryFactory->get('siren_mapper')
                ->condition('entityType', $entity->getEntityTypeId())
                ->condition('bundleType', $entity->bundle());
            $ids = $query->execute();
            if(empty($ids)) {
                return false;
            }
        }
        return in_array($format, $this->formats) && parent::supportsNormalization($data, $format);
    }


    /**
     * Implements \Symfony\Component\Serializer\NOrmalizer\NormalizerInterface::normalize()
     */
    public function normalize($field_item, $format = NULL, array $context = array())
    {
        /** @var $field_item \Drupal\Core\Field\FieldItemInterface */
        $target_entity = $field_item->get('entity')->getValue();

        $mapper = null;
        $query = $this->queryFactory->get('siren_mapper')
            ->condition('entityType', $target_entity->getEntityTypeId())
            ->condition('bundleType', $target_entity->bundle());
        $ids = $query->execute();
        if(isset($ids)) {
            $mapper = entity_load('siren_mapper', array_keys($ids)[0]);
        }

        // If this is not a content entity, let the parent implementation handle it,
        // only content entities are supported as embedded resources.
        if(!($target_entity instanceof ContentEntityInterface)) {
            return parent::normalize($field_item, $format, $context);
        }

        // Setup the subentity structure
        // Use embedded link for now until we can make a full sub-entit
        // representation feasible (using an on off switch)
        $subentity = array(
            "class" => $mapper->classes,
            "rel" => $mapper->relations,
            "href" => $target_entity->url('canonical', array("absolute" => TRUE)),
        );

        // If the parent entity passed in a langcode, unset it before normalizing
        // the target entity. Otherwise, untranslateable fields of the target entity
        // will include the langcode.
        $langcode = isset($context['langcode']) ? $context['langcode'] : NULL;
        unset($context['langcode']);
        $context['included_fields'] = array('uuid');

        // Normalize the target entity.
        $context['subentity'] = true;
        $embedded = $this->serializer->normalize($target_entity, $format, $context);
        // If the field is translateable, add the langcode to the link relation
        // object. This does not indicate the langage of the target entity.
       if ($langcode) {
            $embedded['lang'] = $link['lang'] = $langcode;
       }

        // The returned structure will be recursively merged into the normalized
        // entity so that the items are properly added to the entites object.
        $field_name = $field_item->getParent()->getName();
        $entity = $field_item->getEntity();
        $field_uri = $this->linkManager->getRelationUri($entity->getEntityTypeId(), $entity->bundle(), $field_name);
        return array(
            "entities" => array(
               $subentity
            ),
        );
    }

    /**
     * Implements \Drupal\serialization\EntityResolver\UuidReferenceInterface::getUuid().
     */
    public function getUuid($data)
    {
        if (isset($data['uuid'])) {
            $uuid = $data['uuid'];
            if (is_array($uuid)) {
                $uuid = reset($uuid);
            }
            return $uuid;
        }
    }
}
