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

        if (element_settings.hasOwnProperty('datetimepicker_group')) {
          if (element_settings.datepicker && element_settings['datetimepicker_element'] == 'min') {
            element.addClass('js-' + element_settings.datetimepicker_group + '-min');
            element_settings.onShow = function(ct) {
              var element_class = '.js-' + element_settings.datetimepicker_group + '-max';
              var date = jQuery(element_class).val() ? jQuery(element_class).val() : false;
              this.setOptions({
                maxDate: date
              })
           };
          }
          if (element_settings.datepicker && element_settings['datetimepicker_element'] == 'max') {
            element.addClass('js-' + element_settings.datetimepicker_group + '-max');
            element_settings.onShow = function(ct) {
              var element_class = '.js-' + element_settings.datetimepicker_group + '-min';
              var date = jQuery(element_class).val() ? jQuery(element_class).val() : false;
              this.setOptions({
                minDate: date
              })
           };
          }
          if (element_settings.timepicker) {
            element_settings.onSelectTime = function(current_time, input, event) {
              input.val(input.val().substr(0,5) + ':00');
            };
          }
        }

        element.datetimepicker(element_settings);
      });
    }
  };

})(jQuery);
