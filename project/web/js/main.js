(function ($)
{

    $(document).ready(function ()
    {
        $.initAjaxPost();
        $.initSelect2();
        $.initSelect2Ajax();
        $.initTooltips();
        $.initQueryHash();
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
        var unique = function(a) {var n = {},r=[];for(var i = 0; i < a.length; i++) {if (!n[a[i]]) {n[a[i]] = true; r.push(a[i]); }}return r;};
        var words = unique(words.sort());

        var data = [];
        for (key in words) {
            if (words[key] + "") {
                data.push({id: (words[key] + ""), text: (words[key] + "")});
            }
        }

        select2.select2({
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
                    select2Data.push({id: filtres[key], text: filtres[key]});
                }

                $(document).find('.hamzastyle').select2("data", select2Data);

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

(function($) { var re = /([^&=]+)=?([^&]*)/g; var decodeRE = /\+/g; var decode = function (str) {return decodeURIComponent( str.replace(decodeRE, " ") );}; $.parseParams = function(query) { var params = {}, e; while ( e = re.exec(query) ) { var k = decode( e[1] ), v = decode( e[2] ); if (k.substring(k.length - 2) === '[]') { k = k.substring(0, k.length - 2); (params[k] || (params[k] = [])).push(v); } else params[k] = v; } return params; }; })(jQuery);