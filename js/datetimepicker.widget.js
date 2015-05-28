/**
 * @file
 * DateTimePicker integration
 *
 */
(function ($) {

  Drupal.behaviors.datetimepicker = {
    attach: function (context, settings) {
      $('[data-datetimepicker-widget]', context).once().each(function (i, v) {
        var element = $(this);
        element.attr('type', 'text');

        var element_settings = element.data('datetimepicker-settings');

        /**
         * @todo: Localize datetimepicker
         */
        element_settings['lang'] = 'de';
        element_settings['dayOfWeekStart'] = 1;

        element.datetimepicker(element_settings);
      });
    }
  };

})(jQuery);
