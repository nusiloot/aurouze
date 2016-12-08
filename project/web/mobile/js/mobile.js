(function ($)
{

    $(document).on("pageshow", "[data-role='page']", function () {
      $('div.ui-loader').hide();
    });

    var signaturesPad = [];

    $(document).ready(function ()
    {
        $.initPhoenix();
        $.signatureCanvas();
        $.initTransmission();
    });

    $.initPhoenix = function(){
      $('.phoenix').each(function(){
        $(this).phoenix();
      });
    }

    $.signatureCanvas = function () {

          var divs = document.querySelectorAll('canvas');
          [].forEach.call(divs, function(div) {
              var idCanva = div.id;
              signaturesPad[idCanva] = new SignaturePad(div);
              console.log($("#"+idCanva).parent().find("input").val());
              var input = $("#"+idCanva).parent().find("input");

              if (input.val()) {
                signaturesPad[idCanva].fromDataURL(input.val());
              }
          });
    }

    $.initTransmission = function () {
      $('.passage_rapport_signature').on("click",function(){
        var signaturePadIndex = "signature_pad_"+$(this).attr('data-id');
        var signatureHiddenCible = "input[data-cible='passage_mobile_"+$(this).attr('data-id')+"_signatureBase64']";
        signaturePad = signaturesPad[signaturePadIndex];
        if (!signaturePad.isEmpty()) {
          $(signatureHiddenCible).val(signaturePad.toDataURL());
        }
        var formToPost = $(this).closest( "form" );
        formToPost.submit();
      });

    }
}
)(jQuery);
