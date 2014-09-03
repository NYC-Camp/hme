<?php

/**
 * @file
 * Contains \Drupal\hme\JsonEncoder.
 */

namespace Drupal\hme\Encoder;

use Symfony\Component\Serializer\Encoder\JsonENcoder as SymfonyJsonEncoder;

/**
 * Encodes SIREN data in JSON.
 *
 * Simply respond to application/vnd.siren+json requests using the JSON encoder.
 */
class JsonEncoder extends SymfonyJsonEncoder
{
    /**
     * The formats that this Encoder supports.
     *
     * @var string
     */
    protected $format = 'siren_json';

    /**
     * Overrides \Symfony\Component\Serializer\Encoder\JsonEncoder::supportEncoding()
     */
    public function supportsEncoding($format)
    {
        return $format == $this->format;
    }

    /**
     * Overrides \Symfony\Component\Serializer\Encoder\JsonEncoder::supportsDecoding()
     */
    public function supportsDecoding($format)
    {
        return $format == $this->format;
    }
}
