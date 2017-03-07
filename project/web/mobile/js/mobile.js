(function ($)
{

    $(document).on("pageshow", "[data-role='page']", function () {
      $('div.ui-loader').hide();
    });

    window.onresize = updateSignaturesPads;

    var signaturesPad = [];
    var forms = [];
    var produitsCount = [];
    var niveauInfestationCount = [];
    var version = null;

    $(document).ready(function ()
    {
        $.initTourneeDate();
        $.initPhoenix();
        $.initNiveauInfestation();
        $.initProduits();
        updateSignaturesPads();
        $.initSaisie();
        $.initTransmission();
        $.initVersion();

    });

    $.initPhoenix = function(){
      $('.phoenix').each(function(){
        $(this).phoenix();
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

    function resizeCanvas() {
      setCanvasSize();
      var divs = document.querySelectorAll('canvas');
      [].forEach.call(divs, function(canvas) {
        var idCanva = canvas.id;
        var input = $("#"+idCanva).parent().find("input");
        if(typeof signaturesPad != undefined){
          console.log('ici',signaturesPad[idCanva]);
          signaturesPad[idCanva].clear();
          if (input.val()) {
            signaturesPad[idCanva].fromDataURL(input.val());
          }
        }
      });
    }

    function updateSignaturesPads(){
      var divs = document.querySelectorAll('canvas');
      [].forEach.call(divs, function(canvas) {
        var idCanva = canvas.id;
        var parent = $("#"+idCanva).parent();
        $("#"+idCanva).remove();
        parent.append("<canvas id='"+idCanva+"'></canvas>");
      });
      var newHeight = ($(document).width() < 400)? '250' : $(document).height()*0.50;
      $('.signature-pad').each(function(){
        $(this).css('height',newHeight);
        $(this).css('width',$(document).width()*0.92);
      });
      var divs = document.querySelectorAll('canvas');
      [].forEach.call(divs, function(canvas) {
        var ratio =  Math.max(window.devicePixelRatio || 1, 1);
        var idCanva = canvas.id;
        canvas.height = $("#"+idCanva).parent().height() ;
        canvas.width = $("#"+idCanva).parent().width() ;
        canvas.getContext("2d").scale(ratio, ratio);
      });
      var divs = document.querySelectorAll('canvas');
      [].forEach.call(divs, function(div) {
          var idCanva = div.id;
          signaturesPad[idCanva] = new SignaturePad(div);
          var idPassage = $("#"+idCanva).parents('.passage_signature').attr('data-id');
          var signatureHiddenCible = "input[data-cible='passage_mobile_"+idPassage+"_signatureBase64']";
          $(signatureHiddenCible).each(function(){
            if ($(this).val()) {
              signaturesPad[idCanva].fromDataURL($(this).val());
            }
          });
       });
    }

    $.initSaisie = function () {

      $('form').each(function(){
          forms[$(this).closest('.passage_rapport').attr('data-id')] = $(this);
      });

      $('.passage_signature_valider').on("click",function(){
        var signaturePadIndex = "signature_pad_"+$(this).attr('data-id');
        var signatureHiddenCible = "input[data-cible='passage_mobile_"+$(this).attr('data-id')+"_signatureBase64']";

        var signaturePad = signaturesPad[signaturePadIndex];
        if(typeof signaturePad != undefined)
        $(signatureHiddenCible).each(function(){ $(this).val(signaturePad.toDataURL()); });
      });

      $('.passage_signature_vider').on("click",function(){
        var signaturePadIndex = "signature_pad_"+$(this).attr('data-id');
        var divs = document.querySelectorAll('canvas');
        [].forEach.call(divs, function(div) {
            if(signaturePadIndex == div.id){
              var idCanva = div.id;
              signaturesPad[idCanva].clear();
              var signatureHiddenCible = "input[data-cible='passage_mobile_"+$(this).attr('data-id')+"_signatureBase64']";
              $(signatureHiddenCible).each(function(){ $(this).val(""); });
            }
          });
        });
    }

    $.initTransmission = function () {
      $(".transmission_rapport").on("click",function(){

        var formToPost = forms[$(this).attr('data-id')];
        formToPost.serialize();
        formToPost.submit();

      });
    }

    $.initVersion = function () {

      version = $("#version").attr('data-version');
      $.checkVersion();
      var urlVersion = $("#version").attr("data-url");
      if (urlVersion) {
         setInterval(function(){
          $.ajax({
                   type: "GET",
                   url: urlVersion,
                   success: function (data) {
                        result = JSON.parse(data);
                        $("#version").attr('data-version',result.version);
                        $.checkVersion();
                   }
               });
        }
      , 20000);
      }
    }

    $.checkVersion = function () {
      versionDiv = $("#version").attr('data-version');
      if(versionDiv != version){
        $(".reloadWarning").each(function(){
          $(this).show();
        });
      }else{
        $(".reloadWarning").each(function(){
          $(this).hide();
        });
      }
    }

    $.initTourneeDate = function(){
      $("#tourneesDate").on('change',function(){
        var date = $(this).val();
        var dateiso = date.split('/').reverse().join('-');
        window.location = $(this).attr('data-url-cible')+"/"+dateiso;
      })
    }
}
)(jQuery);
