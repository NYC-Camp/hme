<?php

/**
 * @file
 * Contains \Drupal\hme\Form\SirenMapperForm.
 */

namespace Drupal\hme\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;

/**
 * Class SirenMapperForm
 *
 * Form class for adding/editing siren mapper config entities.
 */
class SirenMapperForm extends EntityForm
{
    /**
     * {@inheritdoc}
     */
    public function form(array $form, array &$form_state)
    {
        $form = parent::form($form, $form_state);

        $sirenMapper = $this->entity;

        // Change page title for the edit operation
        if ($this->operation == 'edit') {
            $form['#title'] = $this->t('Edit Siren Mapper: @name', array('@name' => $sirenMapper->name));
        }

        $form['name'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Name'),
            '#maxlength' => 255,
            '#default_value' => $sirenMapper->name,
            '#description' => $this->t("Siren Mapper name."),
            '#required' => TRUE,
        );

        // The unique machine name of the mapper.
        $form['id'] = array(
            '#type' => 'machine_name',
            '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
            '#default_value' => $sirenMapper->id,
            '#disabled' => !$sirenMapper->isNew(),
            '#machine_name' => array(
                'source' => array('name'),
                'exists' => 'siren_mapper_load',
            ),
        );

        // The entity type this mapper applies to
        $form['entityType'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Entity Type'),
            '#maxlength' => 255,
            '#default_value' => $sirenMapper->entityType,
            '#description' => $this->t("The entity type this mapper applies to"),
            '#required' => TRUE,
        );

        // The bundle type this mapper applies to
        $form['bundleType'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Bundle'),
            '#maxlength' => 255,
            '#default_value' => $sirenMapper->bundleType,
            '#description' => $this->t("The bundle this mapper applies to"),
            '#required' => TRUE,
        );

        // The field mappings for this mapper
        $form['fieldMappings'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('Field Mappings'),
            '#tree' => true,
        );
        $form['fieldMappings']['fieldName'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Field Name'),
            '#maxlength' => 255,
            '#default_value' => $sirenMapper->fieldMappings['fieldName'],
            '#description' => $this->t("The field to be mapped"),
            '#required' => TRUE,
        );

        $form['fieldMappings']['sirenName'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Export Name'),
            '#maxlength' => 255,
            '#default_value' => $sirenMapper->fieldMappings['sirenName'],
            '#description' => $this->t("The siren property to map to"),
            '#required' => TRUE,
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, array &$form_state)
    {
        $sirenMapper = $this->entity;

        dpm($sirenMapper);
        $status = $sirenMapper->save();

        if ($status) {
          // Setting the success message.
          drupal_set_message($this->t('Saved the siren mapper: @name.', array(
            '@name' => $sirenMapper->name,
          )));
        }
        else {
          drupal_set_message($this->t('The @name siren mapper was not saved.', array(
            '@name' => $sirenMapper->name,
          )));
        }
        $url = new Url('siren_mapper.list');
        $form_state['redirect'] = $url->toString();
    }
}
