<?php

/**
 * @file
 * Contains \Drupal\hme\Normalizer\ContentEntityNormalizer.
 */

namespace Drupal\hme\Normalizer;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\rest\LinkManager\LinkManagerInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Converts the Drupal entity object structure to a SIREN array structure.
 */
class ContentEntityNormalizer extends NormalizerBase
{
    /**
     * The interface or class that this Normalizer supports.
     *
     * @var string
     */
    protected $supportedInterfaceOrClass = 'Drupal\Core\Entity\ContentEntityInterface';

    /**
     * The hypermedia link manager.
     *
     * @var \Drupal\rest\LinkManager\LinkManagerInterface
     */
    protected $linkManager;

    /**
     * The entity manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The module handler.
     *
     * @var \Drupal\Core\Extension\ModuleHandlerInterface
     */
    protected $moduleHandler;

    /**
     * The entity query factory.
     *
     * @var \Drupal\Core\Entity\Query\QueryFactory
     */
    protected $queryFactory;

    /**
     * Constructs an ContentENtityNormalizer object.
     *
     * @param \Drupal\rest\LinkManager\LinkManagerInterface $link_manager
     *   The hypermedia link manager.
     */
    public function __construct(LinkManagerInterface $link_manager, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, QueryFactory $entity_query_factory)
    {
        $this->linkManager = $link_manager;
        $this->entityManager = $entity_manager;
        $this->moduleHandler = $module_handler;
        $this->queryFactory = $entity_query_factory;
    }

    /**
     * Override the supportsNormalization function because entities require a mapper.
     */
    public function supportsNormalization($data, $format = NULL)
    {
        $entityRefl = new \ReflectionClass($data);
        if($entityRefl->implementsInterface($this->supportedInterfaceOrClass)) {
            $query = $this->queryFactory->get('siren_mapper')
                ->condition('entityType', $data->getEntityTypeId())
                ->condition('bundleType', $data->bundle());
            $ids = $query->execute();
            if(empty($ids)) {
                return false;
            }
        }
        return in_array($format, $this->formats) && parent::supportsNormalization($data, $format);
    }

    /**
     * Implements \Symfony\Component\Serializer\Normalizer\NormalizerInterface::normalize()
     */
    public function normalize($entity, $format = NULL, array $context = array())
    {
        // Create the array of normalized fields, starting with the URI.
        /** @var $entity \Drupal\Core\Entity\ContentEntityInterface */
        $normalized = array(
            "class" => array(),
            "properties" => array(),
            "entities" => array(),
            "actions" => array(),
            "links" => array(
                array(
                    "rel" => array("self"),
                    "href" => $this->getEntityUri($entity),
                ),
            ),
        );
        $mapper = null;
        $query = $this->queryFactory->get('siren_mapper')
            ->condition('entityType', $entity->getEntityTypeId())
            ->condition('bundleType', $entity->bundle());
        $ids = $query->execute();
        if(isset($ids)) {
            $mapper = entity_load('siren_mapper', array_keys($ids)[0]);
        }


        // Get the fields to include
        $map_fields = array();
        // If we have a mapper
        if($mapper) {
            $normalized['class'] = $mapper->classes;
            foreach($mapper->fieldMappings as $mapping)
            {
                $map_fields[] = $mapping['fieldName'];
            }
        }
        $fields = $entity->getProperties();
        // Ignore the entity ID and revision ID.
        $exclude = array($entity->getEntityType()->getKey('id'), $entity->getEntityType()->getKey('revision'));
        foreach ($fields as $field) {
            if(in_array($field->getFieldDefinition()->getName(), $exclude)) {
                continue;
            }
            if(!in_array($field->getName(), $map_fields)) {
                continue;
            }
            $normalized_property = $this->serializer->normalize($field, $format, $context);
            $normalized = NestedArray::mergeDeep($normalized, $normalized_property);
        }

        return $normalized;
    }

    /**
     * Constructs the entity URI.
     *
     * @param $entity
     *   The entity.
     * @return string
     *   The entity URI.
     */
    protected function getEntityUri($entity)
    {
        return $entity->url('canonical', array('absolute' => TRUE));
    }
}
