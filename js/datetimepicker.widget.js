/**
 * @file
 * DateTimePicker integration
 *
 */
(function ($) {

  Drupal.behaviors.datetimepicker = {
    attach: function (context, settings) {
      $('[data-datetimepicker-widget]').once().each(function (i, v) {
        var element = $(this);
        element.attr('type', 'text');

        var element_settings = element.data('datetimepicker-settings');
        element.datetimepicker(element_settings);
      });
    }
  };

})(jQuery);
