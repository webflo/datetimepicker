<?php

/**
 * @file
 * Contains \Drupal\datetimepicker\Plugin\Field\FieldWidget\DateComboDateTimePicker.
 */

namespace Drupal\datetimepicker\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\date_combo\Plugin\Field\FieldFormatter\DateComboDefaultFormatter;
use Drupal\date_combo\Plugin\Field\FieldWidget\DateComboDefaultWidget;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Plugin implementation of the 'datetimepicker_date_combo' widget.
 *
 * @FieldWidget(
 *   id = "datetimepicker_date_combo",
 *   label = @Translation("DateTimePicker (Date Combo)"),
 *   field_types = {
 *     "date_combo"
 *   }
 * )
 */
class DateComboDateTimePicker extends DateComboDefaultWidget {

  use DateTimePickerTrait;

  /**
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, \Drupal\Core\Entity\EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $date_storage);
    $this->dateFormatter = \Drupal::service('date.formatter');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();

    $settings['date_format'] = 'html_date';
    $settings['time_format'] = 'html_time';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
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

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element =  parent::formElement($items, $delta, $element, $form, $form_state);

    $settings = [
      'timepicker' => (int) ($element['value']['#date_time_format'] == '') ? FALSE : TRUE,
      'format' => $this->getPattern($this->getSetting('date_format')),
    ];

    if ($settings['timepicker'] == TRUE && $this->getSetting('time_format') !== '') {
      $settings['format'] .= ' ' . $this->getPattern($this->getSetting('time_format'));
    }
    else {
      $settings['timepicker'] = FALSE;
    }


    foreach (['value', 'value2'] as $name) {
      $element[$name]['#date_date_format'] = $this->getPattern($this->getSetting('date_format'));
      $element[$name]['#date_time_format'] = $this->getPattern($this->getSetting('time_format'));

      $element[$name]['#attached']['library'][] = 'datetimepicker/datetimepicker.widget';
      $element[$name]['#datetimepicker_settings'] = $settings;

      if ($settings['timepicker'] == FALSE) {
        $element[$name]['#date_date_callbacks'][] = 'datetimepicker_element_date_callback';
        $element[$name]['#date_date_element'] = 'text';
        $element[$name]['#date_time_element'] = 'none';
      }
      else {
        $element[$name]['#date_time_callbacks'][] = 'datetimepicker_element_time_callback';
        $element[$name]['#date_date_element'] = 'text';
        $element[$name]['#date_time_element'] = 'text';
      }
    }

    $element_group = Crypt::randomBytesBase64(8);
    $element['value']['#datetimepicker_settings']['datetimepicker_group'] = $element_group;
    $element['value']['#datetimepicker_settings']['datetimepicker_element'] = 'min';

    $element['value2']['#datetimepicker_settings']['datetimepicker_group'] = $element_group;
    $element['value2']['#datetimepicker_settings']['datetimepicker_element'] = 'max';

    return $element;
  }

  protected function massageDateValue(DrupalDateTime $date) {
    if ($this->getSetting('time_format') === '') {
      $date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $date->setTime(0, 0, 0);
    }
    return parent::massageDateValue($date);
  }

}
