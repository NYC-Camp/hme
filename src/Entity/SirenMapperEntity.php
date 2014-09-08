<?php

/**
 * @file
 * Contains \Drupal\hme\Entity\SirenMapperEntity.
 */

namespace Drupal\hme\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\hme\SirenMapperInterface;

/**
 * Defines a Siren Mapper configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "siren_mapper",
 *   label = @Translation("SIREN Mapper"),
 *   fieldable = FALSE,
 *   controllers = {
 *      "list_builder" = "Drupal\hme\SirenMapperListBuilder",
 *      "form" = {
 *          "add" = "Drupal\hme\Form\SirenMapperForm",
 *          "edit" = "Drupal\hme\Form\SirenMapperForm",
 *          "delete" = "Drupal\hme\Form\SirenMapperDeleteForm"
 *      }
 *    },
 *    config_prefix = "siren_mapper",
 *    admin_permission = "administer site configuration",
 *    entity_keys = {
 *      "id" = "id",
 *      "label" = "name"
 *    },
 *    links = {
 *      "edit-form" = "siren_mapper.edit",
 *      "delete-form" = "siren_mapper.delete"
 *    }
 * )
 */
class SirenMapperEntity extends ConfigEntityBase implements SirenMapperInterface
{
    /**
     * The ID of the Siren Mapper.
     *
     * @var string
     */
    public $id;

    /**
     * The name of this Siren Mapper.
     *
     * @var string
     */
    public $name;

    /**
     * The Entity Type this Siren Mapper applies to.
     *
     * @var string
     */
    public $entityType;

    /**
     * The bundle this Siren Mapper applies to.
     *
     * @var string
     */
    public $bundleType;

    /**
     * The classes for this entity.
     *
     * @var array
     */
    public $classes = array();

    /**
     * The Drupal field to Siren field mappings for this entity bundle type.
     *
     * @var array
     */
    public $fieldMappings = array();
}
