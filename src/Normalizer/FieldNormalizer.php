<?php

/**
 * @file
 * Contains \Drupal\hme\Normalizer\FieldNormalizer.
 */

namespace Drupal\hme\Normalizer;

use Drupal\Component\Utility\NestedArray;
use Symfomy\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Converts the Drupal field structure to SIREN array structure.
 */
class FieldNormalizer extends NormalizerBase
{
    /**
     * The interface or class that this Normalizer supports.
     *
     * @var string
     */
    protected $supportedInterfaceOrClass = 'Drupal\Core\Field\FieldItemListInterface';

    /**
     * Implements \Symfony\Component\Serializer\Normalizer\NormalizerInterface::normalize()
     */
    public function normalize($field, $format = NULL, array $context = array())
    {
        $normalized_field_items = array();

        $entity = $field->getEntity();
        $field_name = $field->getName();
        $field_definition = $field->getFieldDefinition();

        $normalized_field_items = $this->normalizeFieldItems($field, $format, $context);

        $normalized = NestedArray::mergeDeepArray($normalized_field_items);
        return $normalized;
    }

  /**
   * Helper function to normalize field items.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field object.
   * @param string $format
   *   The format.
   * @param array $context
   *   The context array.
   *
   * @return array
   *   The array of normalized field items.
   */
  protected function normalizeFieldItems($field, $format, $context) {
    $normalized_field_items = array();
    if (!$field->isEmpty()) {
      foreach ($field as $field_item) {
        $normalized_field_items[] = $this->serializer->normalize($field_item, $format, $context);
      }
    }
    return $normalized_field_items;
  }

}
