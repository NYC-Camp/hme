<?php

/**
 * @file
 *
 * Contains Drupal\hme\SirenMapperListBuilder
 */

namespace Drupal\hme;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

class SirenMapperListBuilder extends ConfigEntityListBuilder
{
    /**
     * {@inheritdoc}
     */
    public function buildHeader()
    {
        $header['label'] = $this->t('Name');
        $header['entityType'] = $this->t('Entity');
        $header['bundleType'] = $this->t('Bundle');
        return $header + parent::buildHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity)
    {
        // Label
        $row['label'] = $this->getLabel($entity);

        // Entity
        $row['entityType'] = $entity->entityType;

        // Bundle
        $row['bundleType'] = $entity->bundleType;

        return $row + parent::buildRow($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $build = parent::render();

        $build['#empty'] = $this->t('There are no siren mappers available.');
        return $build;
    }
}
