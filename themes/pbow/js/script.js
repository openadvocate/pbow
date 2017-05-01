(function($){

  $(function() {


    if ($("body.path-activate").length) {
      $( ".terms-textarea" ).resizable({
        minHeight: 300,
        minWidth: 300,
        maxWidth: 766
      });
    }


  });
})(jQuery);