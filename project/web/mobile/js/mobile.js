(function ($)
{

    $(document).on("pageshow", "[data-role='page']", function () {
      $('div.ui-loader').hide();
    });

    var signaturesPad = [];
    var forms = [];
    var produitsCount = [];
    var niveauInfestationCount = [];

    $(document).ready(function ()
    {
        $.initPhoenix();
        $.initNiveauInfestation();
        $.initProduits();
        $.signatureCanvas();
        $.initSaisie();
        $.initTransmission();
    });

    $.initPhoenix = function(){
      $('.phoenix').each(function(){
      //  $(this).phoenix();
      });
    }

    $.initProduits = function(){
      $('.produits-list').each(function(){
        produitsCount[$(this).attr("data-id")] = $(this).children('li').length;
      });

      $('a.produits-ajout-lien').click(function(e) {
               e.preventDefault();
               var passageId = $(this).attr("data-id");

               var produitsList = $('#produits-liste-'+passageId);

               // grab the prototype template
               var newWidget = produitsList.attr('data-prototype');

               // replace the "__name__" used in the id and name of the prototype
               // with a number that's unique to your emails
               // end name attribute looks like name="contact[emails][2]"
                newWidget = newWidget.replace(/__name__/g, produitsCount[passageId]);

              // create a new list element and add it to the list
               var newLi = $('<li class="ui-li-static ui-body-inherit" ></li>').html(newWidget);
               newLi.appendTo(produitsList);
               var idPassageReplaced = passageId.replace(/-/g,'_');
               var newIdRow = "#passage_mobile_"+idPassageReplaced+"_produits_"+produitsCount[passageId];
               $(newIdRow).find('select').selectmenu();
               $(newIdRow).find('input').textinput();
               produitsCount[passageId] = produitsCount[passageId] + 1;
           });
      }

  $.initNiveauInfestation = function(){
      $('.niveauInfestation-list').each(function(){
        niveauInfestationCount[$(this).attr("data-id")] = $(this).children('li').length;
      });

      $('a.niveauInfestation-ajout-lien').click(function(e) {
               e.preventDefault();
               var passageId = $(this).attr("data-id");

               var niveauInfestationList = $('#niveauInfestation-liste-'+passageId);

               // grab the prototype template
               var newWidget = niveauInfestationList.attr('data-prototype');

               // replace the "__name__" used in the id and name of the prototype
               // with a number that's unique to your emails
               // end name attribute looks like name="contact[emails][2]"
                newWidget = newWidget.replace(/__name__/g, niveauInfestationCount[passageId]);

              // create a new list element and add it to the list
               var newLi = $('<li class="ui-li-static ui-body-inherit" ></li>').html(newWidget);
               newLi.appendTo(niveauInfestationList);
               var idPassageReplaced = passageId.replace(/-/g,'_');
               var newIdRow = "#passage_mobile_"+idPassageReplaced+"_niveauInfestation_"+niveauInfestationCount[passageId];
               $(newIdRow).find('select').selectmenu();
               $(newIdRow).find('input').textinput();
               niveauInfestationCount[passageId] = niveauInfestationCount[passageId] + 1;
           });
    }

    $.signatureCanvas = function () {

          var divs = document.querySelectorAll('canvas');
          [].forEach.call(divs, function(div) {
              var idCanva = div.id;
              signaturesPad[idCanva] = new SignaturePad(div);
              var input = $("#"+idCanva).parent().find("input");

              if (input.val()) {
                signaturesPad[idCanva].fromDataURL(input.val());
              }
          });
    }

    $.initSaisie = function () {

      $('form').each(function(){
          forms[$(this).closest('.passage_rapport').attr('data-id')] = $(this);
      });

      $('.passage_rapport_signature').on("click",function(){
        var signaturePadIndex = "signature_pad_"+$(this).attr('data-id');
        var signatureHiddenCible = "input[data-cible='passage_mobile_"+$(this).attr('data-id')+"_signatureBase64']";
        signaturePad = signaturesPad[signaturePadIndex];
        if (!signaturePad.isEmpty()) {
          $(signatureHiddenCible).val(signaturePad.toDataURL());
        }
      });
    }

    $.initTransmission = function () {
      $(".transmission_rapport").on("click",function(){

        var formToPost = forms[$(this).attr('data-id')];
        formToPost.serialize();
        formToPost.submit();

      });
    }
}
)(jQuery);
