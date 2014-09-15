<?php

/**
 * @file
 * Contains \Drupal\hme\Normalizer\EntityReferenceItemNormalizer.
 */

namespace Drupal\hme\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\rest\LinkManager\LinkManagerInterface;
use Drupal\serialization\EntityResolver\EntityResolverInterface;
use Drupal\serialization\EntityResolver\UuidReferenceInterface;

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
     * Constructs an EntityReferenceItemNormalizer object.
     *
     * @param \Drupal\rest\LinkManager\LinkManagerInterface $link_manager
     *   The hypermedia link manager.
     * @param \Drupal\serialization\EntityResolver\EntityResolverInterface $entity_resolver
     *   The entity resolver.
     */
    public function __construct(LinkManagerInterface $link_manager, EntityResolverInterface $entity_resolver)
    {
        $this->linkManager = $link_manager;
        $this->entityResolver = $entity_resolver;
    }
}
