(function ($)
{

    $(document).ready(function ()
    {
        $.initPhoenix();
        $.initNavigation();
    });

    $.initPhoenix = function(){
      $('.phoenix').each(function(){
        $(this).phoenix();
      });
    }

    $.initNavigation = function(){
      $('.tournee_passage_visualisation').each(function(){
        $(this).on("click",function(){
          var passage_id = $(this).data("id");
          $('.passage_visualisation[data-id="'+passage_id+'"]').show();
          $('.tournee_accueil').hide();
        });
      });

      $('.passage_rapport_saisie').each(function(){
        $(this).on("click",function(){
          var passage_id = $(this).data("id");
          $('.passage_visualisation[data-id="'+passage_id+'"]').hide();
          $('.passage_rapport[data-id="'+passage_id+'"]').show();
        });
      });
    }

}
)(jQuery);
