<?php

/**
 * @file
 * Contains \Drupal\datetimepicker\Field\Plugin\FieldWidget\DateTimePicker.
 */

namespace Drupal\datetimepicker\Plugin\Field\FieldWidget;

use Drupal\Component\Serialization\Json;
use Drupal\config_translation\FormElement\DateFormat;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDefaultWidget;

/**
 * Plugin implementation of the 'datetimepicker_default' widget.
 *
 * @FieldWidget(
 *   id = "datetimepicker_default",
 *   label = @Translation("DateTimePicker"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class DateTimePicker extends DateTimeDefaultWidget {

  /**
   * @var \Drupal\Core\Datetime\Date
   */
  protected $dateService;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings);
    $this->dateService = \Drupal::service('date');
  }

  public static function defaultSettings() {
    $settings = parent::defaultSettings();

    $settings['date_format'] = 'html_date';
    $settings['time_format'] = 'html_time';

    return $settings;
  }

  public function settingsForm(array $form, array &$form_state) {
    $form = parent::settingsForm($form, $form_state);
    $options = $this->dateFormatList();

    $form['date_format'] = array(
      '#title' => $this->t('Date'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->getSetting('date_format'),
    );

    $form['time_format'] = array(
      '#title' => $this->t('Time'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->getSetting('time_format'),
    );

    return $form;
  }

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $settings = array(
      'timepicker' => (int) ($element['value']['#date_time_format'] == '') ? FALSE : TRUE,
      'format' => trim($this->getPattern($this->getSetting('date_format')) . ' ' . $this->getPattern($this->getSetting('time_format'))),
    );

    $element['value']['#date_date_format'] = $this->getPattern($this->getSetting('date_format'));
    $element['value']['#date_time_format'] = $this->getPattern($this->getSetting('time_format'));

    $element['value']['#attached']['library'][] = 'datetimepicker/datetimepicker';
    $element['value']['#attached']['js'][] = drupal_get_path('module', 'datetimepicker') . '/js/datetimepicker.widget.js';
    $element['value']['#datetimepicker_settings'] = $settings;

    if ($settings['timepicker'] == FALSE) {
      $element['value']['#date_date_callbacks'][] = 'datetimepicker_element_date_callback';
      $element['value']['#date_date_element'] = 'custom';
      $element['value']['#date_time_element'] = 'none';
    }
    else {
      $element['value']['#date_time_callbacks'][] = 'datetimepicker_element_time_callback';
      $element['value']['#date_date_element'] = 'custom';
      $element['value']['#date_time_element'] = 'custom';
    }

    return $element;
  }

  public function dateFormatList() {
    $formats = entity_load_multiple('date_format');
    $options = array('' => 'None');

    foreach ($formats as $entity) {
      $options[$entity->id()] = $entity->label() . ' / ' . $this->dateService->format(REQUEST_TIME, $entity->id());
    }
    return $options;
  }

  public function getPattern($format = NULL) {
    if (empty($format)) {
      return '';
    }

    $entity = $this->dateStorage->load($format);
    $value = $entity->getPattern(DrupalDateTime::PHP);
    return $value;
  }

}
