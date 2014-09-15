<?php

/**
 * @file
 * Contains \Drupal\hme\Normalizer\FieldItemNormalizer.
 */

namespace Drupal\hme\Normalizer;

use Drupal\Core\Field\FieldItemInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Converts the Drupal field item object structure to SIREN array structure.
 */
class FieldItemNormalizer extends NormalizerBase
{
    /**
     * The interface or class that this Normalizer supports.
     *
     * @var string
     */
    protected $supportedInterfaceOrClass = 'Drupal\Core\Field\FieldItemInterface';

    /**
     * Implements \Symfony\Component\Serializer\Normalizer\NormalizerInterface::normalize()
     */
    public function normalize($field_item, $format = NULL, array $context = array())
    {
        $values = $field_item->toArray();

        $mapped_name = $context['mapped_name'];
        $field = $field_item->getParent();
        return array(
            "properties" => array(
                $mapped_name => $values['value'],
            ),
        );
    }
}
