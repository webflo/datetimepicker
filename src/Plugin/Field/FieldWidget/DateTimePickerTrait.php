<?php

namespace Drupal\datetimepicker\Plugin\Field\FieldWidget;

trait DateTimePickerTrait {

  public function dateFormatList() {
    $formats = $this->dateStorage->loadMultiple();
    $options = array('' => 'None');

    foreach ($formats as $entity) {
      $options[$entity->id()] = $entity->label() . ' / ' . $this->dateFormatter->format(\Drupal::time()->getRequestTime(), $entity->id());
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
