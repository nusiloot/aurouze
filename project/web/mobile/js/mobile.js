(function ($)
{

    $(document).on("pageshow", "[data-role='page']", function () {
      $('div.ui-loader').hide();
    });


    $(document).ready(function ()
    {
        $.initPhoenix();
    });

    $.initPhoenix = function(){
      $('.phoenix').each(function(){
        $(this).phoenix();
      });
    }

}
)(jQuery);
