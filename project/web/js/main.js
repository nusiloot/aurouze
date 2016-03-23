(function ($)
{

    $(document).ready(function ()
    {
        $.initAjaxPost();
        $.initSelect2();
        $.initSelect2Ajax();
        $.initTooltips();
        $.initQueryHash();
        $.initDynamicCollection();
        $('.datepicker').datepicker({autoclose: true, todayHighlight: true, toggleActive: true, language: "fr", orientation: "bottom right", daysOfWeekDisabled: "0"});
        $('.input-timepicker').timepicker({showMeridian: false, defaultTime: '01:00'});
    });

    $.initDynamicCollection = function () {

    	var collectionHolder = $('.dynamic-collection');
        var addLink = $('.dynamic-collection-add');
        collectionHolder.data('index', collectionHolder.find(':input').length);

        $('.dynamic-collection-item').on('click', '.dynamic-collection-remove', function(e) {
            e.preventDefault();
            $(e.delegateTarget).remove();
        });

        addLink.on('click', function(e) {
            e.preventDefault();
            var prototype = collectionHolder.data('prototype');
            var index = collectionHolder.data('index');
            var item = $(prototype.replace(/__name__/g, index));
            collectionHolder.data('index', index + 1);
            collectionHolder.append(item);
            $(item).on('click', '.dynamic-collection-remove', function(e) {
                e.preventDefault();
                $(e.delegateTarget).remove();
            });

            $(item).find('input, select').eq(0).focus();
        });
    }

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
                theme: 'bootstrap',
                allowClear: true
            });
        });
    }

    $.initSelect2Ajax = function () {
        $('.select2-ajax').each(function () {
            var urlComponent = $(this).data('url');
            $(this).select2({
                theme: 'bootstrap',
                minimumInputLength: 3,
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

    $(".select2SubmitOnChange").on("change", function (e) {
        if ($(this).val()) {
            $(this).parents('form').submit();
        }
    });

    $('.hamzastyle').each(function () {
        var select2 = $(this);
        var words = [];
        $('.hamzastyle-item').each(function () {
            words = words.concat(JSON.parse($(this).attr('data-words')));
        });

        var words = unique(words.sort());

        var data = [];
        for (key in words) {
            if (words[key] + "") {
                data.push({id: words[key]+"", text: (words[key] + "")});
            }
        }

        select2.select2({
            theme: 'bootstrap',
            multiple: true,
            data: data
        })
    });

    $(document).find('.hamzastyle').on("change", function (e) {
        var select2Data = $(this).select2("data");
        var selectedWords = [];
        for (key in select2Data) {
            selectedWords.push(select2Data[key].text);
        }

        if (!selectedWords.length) {
            document.location.hash = "";
        } else {
            document.location.hash = encodeURI("#filtre=" + JSON.stringify(selectedWords));
        }
    });

    $.initQueryHash = function () {
        $(window).on('hashchange', function () {
            if ($(document).find('.hamzastyle').length) {
                var params = jQuery.parseParams(location.hash.replace("#", ""));
                var filtres = [];
                if (params.filtre && params.filtre.match(/\[/)) {
                    filtres = JSON.parse(params.filtre);
                } else if (params.filtre) {
                    filtres.push(params.filtre);
                }

                var select2Data = [];
                for (key in filtres) {
                    select2Data.push(filtres[key]);
                }

                $(document).find('.hamzastyle').val(select2Data).trigger("change");

                $(document).find('.hamzastyle-item').each(function () {
                    var words = $(this).attr('data-words');
                    var find = true;
                    for (key in filtres) {
                        var word = filtres[key];
                        if (words.indexOf(word) === -1) {
                            find = false;
                        }
                    }
                    if (find) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
            if ($(document).find('.nav.nav-tabs').length) {
                var params = jQuery.parseParams(location.hash.replace("#", ""));
                if (params.tab) {
                    $('.nav.nav-tabs a[aria-controls="' + params.tab + '"]').tab('show');
                }
            }
        });

        if (location.hash) {
            $(window).trigger('hashchange');
        }
    }

})(jQuery);
