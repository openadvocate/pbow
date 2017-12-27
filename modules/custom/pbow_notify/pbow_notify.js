/**
 * @file
 * Notification button behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.pbow_notify = {
    attach: function (context, settings) {
      var $btn = $('#block-pbow-account-menu .menu a[href="#notices"]');

      if ($btn.length && $btn.dropdown) {
        $btn.html('<i class="fa fa-bell"></i>');
        $btn.attr('data-toggle', 'dropdown');
        $btn.after('<ul class="dropdown-menu notification-dropdown" style="background: #fff"></ul>');
        $btn.dropdown();

        $.get('/api/pbow-notify/has-new-notices', function(data) {
          if (data === true) {
            $btn.find('.fa-bell').addClass('text-danger');
          }
        });

        $btn.click(function (e) {
          e.preventDefault();

          var $icon = $(this).find('.fa-bell');

          if ($icon.hasClass('text-danger')) {
            $icon.removeClass('text-danger');
          }

          var $ul = $btn.next('ul');

          if ($ul.children().length == 0) {
            $.get('/api/pbow-notify/get-notices', function(data) {
              if (data.length == 0) {
                $ul.append('<li>No notifications</li>');
                return;
              }

              for (var i = 0, len = data.length; i < len; i++) {
                var notice = data[i].status == 'new' ? '<span class="text-danger">new</span> ' : '';
                notice = '<div class="notice-title">' + notice + data[i].title + '</div>'
                       + '<div class="notice-time">' +  data[i].time + '</div>';

                $ul.append('<li>' + notice + '</li>');

                if (i < len - 1) {
                  $ul.append('<li role="separator" class="divider"></li>');
                }
              }
            });
          }
        });
      }
    }
  };

  function dateFormat(timestamp) {
    timestamp = timestamp || 0;
    var date = new Date(timestamp * 1000);

    return date.toLocaleString('en-US', {
      weekday: 'long',
      month: 'numeric',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit'
    });
  }

})(jQuery, Drupal, drupalSettings);
