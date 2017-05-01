/**
 * @file
 * Case behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.caseAssignOverlap = {
    attach: function (context, settings) {
      $(".add_overlap_card_action").click(function(event) {
        var info = $(this).data('requester-info');

        $('#requester_overlap img.img-thumbnail').prop('src', info.picture);
        $('#requester_overlap .req-name').text(info.name);
        $('#requester_overlap .req-since').text(info.since);
        $('#requester_overlap .req-email').text(info.email);
        $('#requester_overlap .req-requested-cnt').text(info.requested);
        $('#requester_overlap .req-assigned-cnt').text(info.assigned);
        $('#requester_overlap .req-resolved-cnt').text(info.resolved);
        $('#requester_overlap .req-view-profile').prop('href', '/user/' + info.uid);
        $('#requester_overlap input#edit-uid').val(info.uid);

        $('#requester_overlap').removeClass("is_out");
        setTimeout(function(){
          $('#requester_overlap').removeClass("move_out");
        }, 8);
      });

      $(".remove_overlap_card_action").click(function(event) {
        var overlap_card = $(event.target).closest(".overlap_card");
        $(overlap_card).addClass("move_out");
        setTimeout(function() {
          $(overlap_card).addClass("is_out");
        }, 800);
      });
    }
  };


  Drupal.behaviors.caseManagementCount = {
    attach: function (context, settings) {
      $('#management-tab-data .tab-info').each(function(){
        var number = $(this);
        var place = $(this).attr("data-placement");
        $(".tabs--primary li a[data-drupal-link-system-path='"+place+"']").append(number);

      });
      $('#block-pbowcasemanagementcounts').hide();
    }
  };

})(jQuery, Drupal, drupalSettings);
