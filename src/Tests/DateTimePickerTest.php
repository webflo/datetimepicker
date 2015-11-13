<?php

/**
 * @file
 * Contains \Drupal\datetimepicker\Tests\DateTimePickerTest.
 */

namespace Drupal\datetimepicker\Tests;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\simpletest\WebTestBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Tests Datetimepicker widget functionality.
 *
 * @group datetimepicker
 */
class DateTimePickerTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'entity_test', 'datetime', 'datetimepicker', 'field_ui');

  /**
   * A field storage to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The field used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $web_user = $this->drupalCreateUser(array(
      'access content',
      'view test entity',
      'administer entity_test content',
      'administer entity_test form display',
      'administer content types',
      'administer node fields',
    ));
    $this->drupalLogin($web_user);

    // Create a field with settings to validate.
    $field_name = Unicode::strtolower($this->randomMachineName());
    $this->fieldStorage = entity_create('field_storage_config', array(
      'field_name' => $field_name,
      'entity_type' => 'entity_test',
      'type' => 'datetime',
      'settings' => array('datetime_type' => 'date'),
    ));
    $this->fieldStorage->save();
    $this->field = entity_create('field_config', array(
      'field_storage' => $this->fieldStorage,
      'bundle' => 'entity_test',
      'required' => TRUE,
    ));
    $this->field->save();
  }

  /**
   * Tests DateTimePicker widget with Date only.
   */
  function testDatePickerWidget() {

    $field_name = $this->fieldStorage->getName();

    // Ensure field is set to a date only field.
    $this->fieldStorage->setSetting('datetime_type', 'date');
    $this->fieldStorage->save();

    // Change the widget to a datelist widget.
    entity_get_form_display($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle(), 'default')
      ->setComponent($field_name, array(
        'type' => 'datetimepicker',
        'settings' => array(
          'date_format' => 'html_date',
        ),
      ))
      ->save();

    \Drupal::entityManager()->clearCachedFieldDefinitions();

    // Display creation form.
    $this->drupalGet('entity_test/add');
    // Assert that Hour and Minute Elements do not appear on Date Only
    $this->assertNoFieldByXPath("//*[@id=\"edit-$field_name-0-value-time\"]", NULL, 'Time element not found on Date Only.');

    // Go to the form display page to assert that increment option does not appear on Date Only
    $fieldEditUrl = 'entity_test/structure/entity_test/form-display';
    $this->drupalGet($fieldEditUrl);

    // Click on the widget settings button to open the widget settings form.
    $this->drupalPostAjaxForm(NULL, array(), $field_name . "_settings_edit");
    $xpathIncrDate = "//select[starts-with(@id, \"edit-fields-$field_name-settings-edit-form-settings-date-format\")]";
    $this->assertFieldByXPath($xpathIncrDate, NULL, 'Date element found for Date and time.');
    $xpathIncrTime = "//select[starts-with(@id, \"edit-fields-$field_name-settings-edit-form-settings-time-format\")]";
    $this->assertNoFieldByXPath($xpathIncrTime, NULL, 'Time element not found on Date Only.');
  }

  /**
   * Tests DateTimePicker widget with Date and Time.
   */
  function testDateTimePickerWidget() {

    $field_name = $this->fieldStorage->getName();

    // Ensure field is set to a date & time field.
    $this->fieldStorage->setSetting('datetime_type', 'datetime');
    $this->fieldStorage->save();

    // Change the widget to a datetimepicker widget.
    entity_get_form_display($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle(), 'default')
      ->setComponent($field_name, array(
        'type' => 'datetimepicker',
        'settings' => array(
          'date_format' => 'html_date',
          'time_format' => 'html_time',
        ),
      ))
      ->save();
    \Drupal::entityManager()->clearCachedFieldDefinitions();

    // Display creation form.
    $this->drupalGet('entity_test/add');

    $this->assertFieldByXPath("//*[@id=\"edit-$field_name-0-value-date\"]", NULL, 'Date element found.');
    $this->assertFieldByXPath("//*[@id=\"edit-$field_name-0-value-time\"]", NULL, 'Time element found.');

    // Submit a valid date and ensure it is accepted.
    $value = '2012-12-31 00:00:00';
    $date = new DrupalDateTime($value, 'UTC');
    $date_format = entity_load('date_format', 'html_date')->getPattern();

    $edit = array(
      "{$field_name}[0][value][date]" => $date->format($date_format),
      "{$field_name}[0][value][time]" => '12:00:00',
    );

    $this->drupalPostForm(NULL, $edit, t('Save'));
    preg_match('|entity_test/manage/(\d+)|', $this->url, $match);
    $id = $match[1];
    $this->assertText(t('entity_test @id has been created.', array('@id' => $id)));
    // Go to the form display page to assert that increment option does appear on Date Time
    $fieldEditUrl = 'entity_test/structure/entity_test/form-display';
    $this->drupalGet($fieldEditUrl);

    // Click on the widget settings button to open the widget settings form.
    $this->drupalPostAjaxForm(NULL, array(), $field_name . "_settings_edit");
    $xpathIncrDate = "//select[starts-with(@id, \"edit-fields-$field_name-settings-edit-form-settings-date-format\")]";
    $this->assertFieldByXPath($xpathIncrDate, NULL, 'Date element found for Date and time.');
    $xpathIncrTime = "//select[starts-with(@id, \"edit-fields-$field_name-settings-edit-form-settings-time-format\")]";
    $this->assertFieldByXPath($xpathIncrTime, NULL, 'Time element found for Date and time.');

    // Display creation form.
    $this->drupalGet('entity_test/add');

    // Submit a partial date and ensure and error message is provided.
    $edit = array(
      "{$field_name}[0][value][date]" => '',
      "{$field_name}[0][value][time]" => '12:00:00',
    );

    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertResponse(200);
    $this->assertText('Please enter a date in the format');

    // Test the widget for complete input with zeros as part of selections.
    $this->drupalGet('entity_test/add');

    $date_value = array('date' => 2012-12-31, 'time' => '');
    $edit = array();
    foreach ($date_value as $part => $value) {
      $edit["{$field_name}[0][value][$part]"] = $value;
    }

    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertResponse(200);

    $date_format_date = DateFormat::create([
      'id' => 'datetimepicker_date',
      'label' => 'Date',
      'pattern' => 'dmY'
    ]);
    $date_format_date->save();

    $date_format_time = DateFormat::create([
      'id' => 'datetimepicker_time',
      'label' => 'Time',
      'pattern' => 'HHMM'
    ]);
    $date_format_time->save();

    // Change the widget to a datetimepicker widget.
    entity_get_form_display($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle(), 'default')
      ->setComponent($field_name, array(
        'type' => 'datetimepicker',
        'settings' => array(
          'date_format' => 'datetimepicker_date',
          'time_format' => 'datetimepicker_time',
        ),
      ))
      ->save();
    \Drupal::entityManager()->clearCachedFieldDefinitions();

    // Ensure field is set to a date and time field.
    $this->fieldStorage->setSetting('datetime_type', 'datetime');
    $this->fieldStorage->save();

    // Test the widget for wrong inputformat.
    $this->drupalGet('entity_test/add');

    $edit = array(
      "{$field_name}[0][value][date]" => '2012/02/12',
      "{$field_name}[0][value][time]" => '12:00:00',
    );

    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertResponse(200);
    $this->assertText("The {$field_name} date is invalid.");

  }

}
