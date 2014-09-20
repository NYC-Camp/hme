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
        if(!isset($form_state['fields'])) {
            $form_state['fields']['hme']['siren_mapper']['fieldMappings'] = count($sirenMapper->fieldMappings) ?: 1;
            $form_state['fields']['hme']['classes'] = count($sirenMapper->classes) ?: 1;
            $form_state['fields']['hme']['relations'] = count($sirenMapper->relations) ?: 1;
        }

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
        $max = $form_state['fields']['hme']['classes'];
        $form['classes'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('Classes'),
            '#tree' => true,
            '#prefix' => '<div id="classes">',
            '#suffix' => '</div>',
        );

        for($delta = 0; $delta < $max; $delta++) {
            $form['classes'][$delta] = array(
                '#type' => 'textfield',
                '#title' => $this->t('Class name'),
                '#maxlength' => 255,
                '#default_value' => $sirenMapper->classes[$delta],
                '#description' => $this->t("A class that describe the nature of an entity's content."),
            );
        }

        $form['classAdd'] = array(
            '#type' => 'submit',
            '#name' => 'add-class',
            '#value' => t('Add Classes'),
            '#submit' => array(array($this, 'addMoreClassesSubmit')),
            '#ajax' => array(
                'callback' => array($this, 'addMoreClassesCallback'),
                'wrapper' => 'classes',
                'effect' => 'fade',
            ),
        );

        // The relations for the mapped entity
        $max = $form_state['fields']['hme']['relations'];
        $form['relations'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('Relations'),
            '#tree' => true,
            '#prefix' => '<div id="relations">',
            '#suffix' => '</div>',
        );

        for($delta = 0; $delta < $max; $delta++) {
            $form['relations'][$delta] = array(
                '#type' => 'textfield',
                '#title' => $this->t('Relation name'),
                '#maxlength' => 255,
                '#default_value' => $sirenMapper->relations[$delta],
                '#description' => $this->t("A relation that describes the sematics of this entity type"),
            );
        }

        $form['relationAdd'] = array(
            '#type' => 'submit',
            '#name' => 'add-relation',
            '#value' => t('Add Relations'),
            '#submit' => array(array($this, 'addMoreRelationsSubmit')),
            '#ajax' => array(
                'callback' => array($this, 'addMoreRelationsCallback'),
                'wrapper' => 'relations',
                'effect' => 'fade',
            ),
        );


        // The field mappings for this mapper
        $max = $form_state['fields']['hme']['siren_mapper']['fieldMappings'];
        $form['fieldMappings'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('Field Mappings'),
            '#tree' => true,
            '#prefix' => '<div id="field-mappings">',
            '#suffix' => '</div>',
        );

        for($delta = 0; $delta < $max; $delta++) {
            $form['fieldMappings'][$delta]['fieldName'] = array(
                '#type' => 'textfield',
                '#title' => $this->t('Field Name ' . $delta),
                '#maxlength' => 255,
                '#default_value' => $sirenMapper->fieldMappings[$delta]['fieldName'],
                '#description' => $this->t("The field to be mapped"),
                '#required' => TRUE,
            );

            $form['fieldMappings'][$delta]['sirenName'] = array(
                '#type' => 'textfield',
                '#title' => $this->t('Export Name ' . $delta),
                '#maxlength' => 255,
                '#default_value' => $sirenMapper->fieldMappings[$delta]['sirenName'],
                '#description' => $this->t("The siren property to map to"),
                '#required' => TRUE,
            );
        }

        $form['fieldMappingsAdd'] = array(
            '#type' => 'submit',
            '#name' => 'add-mapping',
            '#value' => t('Add Mapping'),
            '#submit' => array(array($this, 'addMoreMappingsSubmit')),
            '#ajax' => array(
                'callback' => array($this, 'addMoreMappingsCallback'),
                'wrapper' => 'field-mappings',
                'effect' => 'fade',
            ),
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, array &$form_state)
    {
        $sirenMapper = $this->entity;

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

    public function addMoreMappingsCallback(array &$form, array &$form_state)
    {
        return $form['fieldMappings'];
    }

    public function addMoreMappingsSubmit(array &$form, array &$form_state)
    {
        $form_state['fields']['hme']['siren_mapper']['fieldMappings']++;
        $form_state['rebuild'] = TRUE;
    }

    public function addMoreClassesCallback(array &$form, array &$form_state)
    {
        return $form['classes'];
    }

    public function addMoreClassesSubmit(array &$form, array &$form_state)
    {
        $form_state['fields']['hme']['classes']++;
        $form_state['rebuild'] = TRUE;
    }

    public function addMoreRelationsCallback(array &$form, array &$form_state)
    {
        return $form['relations'];
    }

    public function addMoreRelationsSubmit(array &$form, array &$form_state)
    {
        $form_state['fields']['hme']['relations']++;
        $form_state['rebuild'] = TRUE;
    }
}
