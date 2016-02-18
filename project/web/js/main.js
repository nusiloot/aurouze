(function ($)
{

    $(document).ready(function ()
    {
        $.initAjaxPost();
        $.initSelect2();
        $.initSelect2Ajax();
        $.initTooltips();
    });
    
    $.initTooltips = function () {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    $.initAjaxPost = function ()
    {

        var notificationError = $('#ajax_form_error_notification');
        var notificationProgress = $('#ajax_form_progress_notification');

        $(document).ajaxError(
                function (event, xhr, settings) {
                    if (settings.type === "POST") {
                        notificationError.show();
                    }
                }
        );

        $(document).ajaxSuccess(
                function (event, xhr, settings) {
                    if (settings.type === "POST") {
                        notificationError.hide();
                    }
                }
        );

        $(document).ajaxSend(
                function (event, xhr, settings) {
                    if (settings.type === "POST") {
                        notificationError.hide();
                        notificationProgress.show();
                    }
                }
        );

        $(document).ajaxComplete(
                function (event, xhr, settings) {
                    if (settings.type === "POST") {
                        notificationProgress.hide();
                    }
                }
        );
    };

    $.initSelect2 = function () {
        $('.select2-simple').each(function () {
            $(this).select2({
                language: 'fr'
            });
        });
    }

    $.initSelect2Ajax = function () {

        $('.select2-ajax').each(function () {
            var urlComponent = $(this).data('url');
            $(this).select2({
                minimumInputLength: 3,
                language: 'fr',
                allowClear: true,
                ajax: {
                    type: "POST",
                    url: urlComponent,
                    dataType: 'json',
                    quietMillis: 100,
                    data: function (params) {

                        var queryParameters = {
                            term: params.term
                        }
                        return queryParameters;
                    },
                    processResults: function (data) {

                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.term,
                                    id: item.id
                                }
                            })
                        };
                    }

                },
                formatResult: function (data, term) {
                    return data;
                },
                formatSelection: function (data) {
                    return data;
                }
            });
        });
    }
})(jQuery);