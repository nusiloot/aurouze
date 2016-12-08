(function ($)
{

    $(document).on("pageshow", "[data-role='page']", function () {
      $('div.ui-loader').hide();
    });


    $(document).ready(function ()
    {
        $.initPhoenix();
        $.signatureCanvas();
    });

    $.initPhoenix = function(){
      $('.phoenix').each(function(){
        $(this).phoenix();
      });
    }

    $.signatureCanvas = function () {

          var divs = document.querySelectorAll('canvas');
          [].forEach.call(divs, function(div) {
              var signaturePad = new SignaturePad(div);
              var idCanva = div.id;
              var input = $("#"+idCanva).parent().find("input");
              
              if (input.val()) {
                signaturePad.fromDataURL(input.val());
              }
          });
    }
}
)(jQuery);
