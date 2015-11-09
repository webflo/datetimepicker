<?php

/**
 * @file
 * Contains \Drupal\datetimepicker\Field\Plugin\FieldWidget\DateTimePicker.
 */

namespace Drupal\datetimepicker\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
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
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $date_storage);
    $this->dateFormatter = \Drupal::service('date.formatter');
  }

  public static function defaultSettings() {
    $settings = parent::defaultSettings();

    $settings['date_format'] = 'html_date';
    $settings['time_format'] = 'html_time';

    return $settings;
  }

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $options = $this->dateFormatList();

    $form['date_format'] = array(
      '#title' => $this->t('Date'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->getSetting('date_format'),
    );

    if($this->getFieldSetting('datetime_type') == 'datetime') {
      $form['time_format'] = array(
        '#title' => $this->t('Time'),
        '#type' => 'select',
        '#options' => $options,
        '#default_value' => $this->getSetting('time_format'),
      );
    }

    return $form;
  }

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $settings = array(
      'timepicker' => (int) ($element['value']['#date_time_format'] == '') ? FALSE : TRUE,
      'format' => trim($this->getPattern($this->getSetting('date_format')) . ' ' . $this->getPattern($this->getSetting('time_format'))),
    );

    $element['value']['#date_date_format'] = $this->getPattern($this->getSetting('date_format'));
    $element['value']['#date_time_format'] = $this->getPattern($this->getSetting('time_format'));

    $element['value']['#attached']['library'][] = 'datetimepicker/datetimepicker.widget';
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
    $formats = $this->dateStorage->loadMultiple();
    $options = array('' => 'None');

    foreach ($formats as $entity) {
      $options[$entity->id()] = $entity->label() . ' / ' . $this->dateFormatter->format(REQUEST_TIME, $entity->id());
    }
    return $options;
  }

  public function getPattern($format = NULL) {
    if (empty($format)) {
      return '';
    }

    /**
     * @var \Drupal\Core\Datetime\DateFormatInterface $entity
     */
    $entity = $this->dateStorage->load($format);
    $value = $entity->getPattern();
    return $value;
  }

}
