(function ($) {
  Drupal.behaviors.superLoginBehavior = {
    attach: function (context, settings) {
      
      $(".messages").prependTo("#user-login-form");      
      $(".alert").prependTo("#user-login-form");
      
      
      $('#edit-pass').keypress(function(e) {           
          var s = String.fromCharCode( e.which );
          if ( s.toUpperCase() === s && s.toLowerCase() !== s && !e.shiftKey ) {
            $('#capslockdiv P').show();            
          }
          else { 
            $('#capslockdiv P').hide();            
          }
      });    
    }
  };
})(jQuery);


