(function ($) {
  $(function() {
    if ($('body.path-search-case').length) {
      var $list = $('.case-row');
      var $widgets = $('.views-exposed-form');

      // Refine widgets and assign behaviors.
      // addNoneToResearchFocus();
      $('input:text', $widgets).on('keyup', filterByCriteria);
      $('input:checkbox', $widgets).on('click', filterByCriteria);

      // Disable form submission as search is performed by JS on already loaded
      // rows. It also prevents PDO error that's shown when form is submitted
      // without submit button.
      $('.views-exposed-form').submit(function(e) {
        e.preventDefault();
      })

      // Helper to check if array contains the other.
      function arrayContains(arr, subarr) {
        for(var i = 0; i < subarr.length; i++) {
          if(arr.indexOf(subarr[i]) === -1)
             return false;
        }
        return true;
      }

      function filterByCriteria() {
        var opt_population = $('.views-exposed-form input[id^=edit-population]:checked')
                              .map(function() { return this.value; }).get();
        var opt_case_type = $('.views-exposed-form input[id^=edit-case-type]:checked')
                              .map(function() { return this.value; }).get();
        var opt_county = $('.views-exposed-form input[id^=edit-county]:checked')
                              .map(function() { return this.value; }).get();
        var keyword = $('.views-exposed-form input:text').val().toLowerCase();

        if (!opt_population.length && !opt_case_type.length && !opt_county.length && !keyword) {
          $list.show();
          return;
        }

        $list.show().filter(function() {
          var hide = false;
          var text = $('.views-field-body', this).text().trim().toLowerCase();
          var tids = text.split('~~~')[0].split('|').filter(function(val) { return val; });

          if (!hide && opt_population.length) {
            hide = !arrayContains(tids, opt_population);
          }

          if (!hide && opt_case_type.length) {
            hide = !arrayContains(tids, opt_case_type);
          }

          if (!hide && opt_county.length) {
            hide = !arrayContains(tids, opt_county);
          }

          if (!hide && keyword) {
            hide = text.indexOf(keyword) < 0;
          }

          return hide;
        }).hide();
      }
    }
  });
})(jQuery);
