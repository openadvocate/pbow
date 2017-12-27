/**
 * @file
 * Notification button behaviors.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.pbow_report = {
    attach: function (context, settings) {
      // As placing the links to switch between chart and table is tricky on the
      // server side, do it with javascript.
      var path = window.location.pathname;

      if (path == '/report') {
        $('h1.page-header')
          .before('<div style="float: right; padding-top: 1em;"><a href="/report/table">View Table</a></div>');
      }

      if (path == '/report/table') {
        $('h1.page-header')
          .before('<div style="float: right; padding-top: 1em;"><a href="/report">View Chart</a></div>');
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
