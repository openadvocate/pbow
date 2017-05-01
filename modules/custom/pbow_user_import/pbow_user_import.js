/**
 * @file
 * User import behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.pbow_user_import = {
    attach: function (context, settings) {
      // Force initially checked (even if submitted with error and remembers as unchecked)
      $('#edit-check-data').prop('checked', true);

      // Render Upload button red when checkbox is unchecked.
      $('#edit-check-data').click(function() {
        if (this.checked) {
          $('#edit-submit').css('border-color', '#1e5c90')
            .css('background-color', '#0071b8');
        }
        else {
          $('#edit-submit').css('border-color', '#c00')
            .css('background', '#c00');
        }
      })
    }
  };

})(jQuery, Drupal, drupalSettings);
