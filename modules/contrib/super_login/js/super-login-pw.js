(function ($) {
  Drupal.behaviors.superLoginBehavior = {
    attach: function (context, settings) {
      
      $(".messages").prependTo("#user-login-form");      
      $(".alert").prependTo("#user-login-form");
     
    }
  };
})(jQuery);


