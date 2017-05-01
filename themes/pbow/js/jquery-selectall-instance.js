(function($){

  $(function() {


    if ($('body.path-user').length) {

        $("fieldset").selectAll( {

            buttonParent: "legend", // or ".classname" etc.
            buttonWrapperHTML : '<span class="pull-right"></span>',

            buttonSelectText: "Select All",
            buttonSelectBeforeHTML: '<span class="fa fa-check"></span>',
            buttonSelectAfterHTML: "",

            buttonDeSelectText: "Deselect All",
            buttonDeSelectBeforeHTML: '<span class="fa fa-close"></span>',
            buttonDeSelectAfterHTML: "",

            buttonExtraClasses: "btn btn-sm btn-default"
        });

    } else if($('body.user-logged-in.path-node.page-node-type-case').length)  {

        $(".panel-default fieldset").selectAll( {
            buttonParent: "legend", // or ".classname" etc.
            buttonWrapperHTML : '<span class="pull-right"></span>',

            buttonSelectText: "Select All",
            buttonSelectBeforeHTML: '<span class="fa fa-check"></span>',
            buttonSelectAfterHTML: "",

            buttonDeSelectText: "Deselect All",
            buttonDeSelectBeforeHTML: '<span class="fa fa-close"></span>',
            buttonDeSelectAfterHTML: "",

            buttonExtraClasses: "btn btn-sm btn-default"
        });
    }


  });

})(jQuery);