/**
 * @file
 * Search Case page behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.pbow_fix_my_zip = {
    attach: function (context, settings) {

      // Render Upload button red when checkbox is unchecked.
      $('body.path-search-case #edit-my-zip').click(function() {
        if ($(this).data('my-zips')) {
          if (this.checked) {
            $('#edit-zip-code').val($(this).data('my-zips'));
          }
          else {
            $('#edit-zip-code').val('');
          }
        }
      })
    }
  };

})(jQuery, Drupal, drupalSettings);
