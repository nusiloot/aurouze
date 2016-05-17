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
        $.initDatePicker();
        $.initTimePicker();
        $.initFormEventAjax();
        $.initSwitcher();
        $.initModalPassage();
        $.initBtnSwitch();
        $.initCollapseCheckbox();
        $.initTextSelector();
        $.initLinkInPanels();
        $.initRdvLink();
        $.initSearchActif();
        $.initListingPassage();
        $.initLinkCalendar();
    });

    $.initLinkCalendar = function () {
    	$('#calendrier .fc-day-header').each(function() {
    		if ($(this).data('date')) {
	    		var content = '<a href="'+($('#calendrier').data('url-date')).replace('-d', $(this).data('date'))+'">' + $(this).text() + '</a>';
	    		$(this).html(content);
    		}
    	});
    	$('#calendrier .fc-day-number').each(function() {
    		if ($(this).data('date')) {
	    		var content = '<a href="'+($('#calendrier').data('url-date')).replace('-d', $(this).data('date'))+'">' + $(this).text() + '</a>';
	    		$(this).html(content);
    		}
    	});
    };

    $.initListingPassage = function () {
        $('.calendar_lien').click(function (event) {
            event.preventDefault();
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
    };

    $.initSearchActif = function () {
        $('form input[type="checkbox"][data-search-actif="1"]').each(function () {

            $(this).parents('form').find('select').attr('data-nonactif', "0");
            $(this).click(function () {
                $(this).parents('form').find('select').attr('data-nonactif', ($(this).is(':checked') ? "1" : "0"));
                $.initSelect2Ajax();
            })
        });
    }

    $.initRdvLink = function () {
        $('.rdv-deplanifier-link').click(function (e) {
            e.preventDefault();
            var link = $(this).attr('href');
            $.post(link, function (data) {
                document.location.reload();
            });
        });

        $('.rdv-modifier-link').click(function (e) {
            e.preventDefault();
            $('#modal-calendrier-infos').load($(this).attr('href'), function () {
                $.callbackEventForm();
            });
        });
    }

    $.initLinkInPanels = function () {
        $('.panel-heading a.stopPropagation').click(function (e) {
            e.stopPropagation();
        });
    }

    $.initTextSelector = function () {
        $('.text-selector').click(function () {
            $(this).select();
        });
    }
    $.initCollapseCheckbox = function () {

        $('.collapse-checkbox').click(function () {
            if ($(this).is(':checked')) {
                $($(this).data('target')).collapse('hide');
            } else {
                $($(this).data('target')).collapse('show');
            }
        });
    }

    $.initSwitcher = function () {
        $('.switcher').each(function () {
            var state = $(this).is(':checked');
            $(this).bootstrapSwitch('state', state);
        });
        $('.switcher').on('switchChange.bootstrapSwitch', function (event, state) {

            var checkbox = $(this);
            var etat = state ? 1 : 0;
            if (checkbox.attr("data-url")) {
                $.ajax({
                    type: "POST",
                    url: checkbox.data('url'),
                    data: {etat: etat}
                });
            }
        });
    }

    $.initBtnSwitch = function () {
        $('.btn-switcher').click(function () {
            $($(this).data('hide')).hide();
            $($(this).data('show')).show();
        });
    }

    $.initDatePicker = function () {
        $('.datepicker').datepicker({autoclose: true, todayHighlight: true, toggleActive: true, language: "fr", orientation: "right"});
    }

    $.initTimePicker = function () {
        $('.input-timepicker').each(function () {
            var defaultTiming = ($(this).attr('data-default'))? $(this).attr('data-default') : '';
            $(this).timepicker({
                format: 'HH:ii p',
                autoclose: true,
                showMeridian: false,
                startView: 1,
                maxView: 1,
                defaultTime: ""+defaultTiming
            });
        });
    }

    $.initDynamicCollection = function () {
        var addLink = $('.dynamic-collection-add');
        $('.dynamic-collection-item').on('click', '.dynamic-collection-remove', function (e) {
            e.preventDefault();
            $(e.delegateTarget).remove();
        });
        addLink.on('click', function (e) {
            e.preventDefault();
            var collectionTarget = $(this).data('collection-target');
            var collectionHolder = $(collectionTarget);
            collectionHolder.data('index', collectionHolder.find(':input').length);
            var prototype = collectionHolder.data('prototype');
            var index = collectionHolder.data('index');
            var item = $(prototype.replace(/__name__/g, index));
            collectionHolder.data('index', index + 1);
            collectionHolder.append(item);
            $(item).on('click', '.dynamic-collection-remove', function (e) {
                e.preventDefault();
                $(e.delegateTarget).remove();
            });
            $(item).find('input, select').eq(0).focus();
            $.callbackDynamicCollection();
        });
    }

    $.initFormEventAjax = function () {
        $('#eventForm').submit(function () {
            $('#modal-calendrier-infos').find('button[type="submit"]').button('loading');
            var form = $(this);
            var request = $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize()
            });
            request.done(function (msg) {
                try {
                    $.parseJSON(msg);
                    location.reload();
                } catch (e) {
                    $('#modal-calendrier-infos').html(msg);
                    $.callbackEventForm();
                }
            });
            request.fail(function (jqXHR, textStatus) {
                $('#modal-calendrier-infos').html(jqXHR.responseText);
                $.callbackEventForm();
                $.callbackDynamicCollection();
            });
            return false;
        });
    }

    $.callbackDynamicCollection = function () {
        $.initSelect2();
        $.initSelect2Ajax();
        $.initDatePicker();
        $.initTimePicker();
    }

    $.callbackEventForm = function () {
        $.initSelect2();
        $.initSelect2Ajax();
        $.initDatePicker();
        $.initTimePicker();
        $.initFormEventAjax();
        $.initRdvLink();
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
            var urlComponent = $(this).attr('data-url') + "?";
            if ($(this).attr('data-nonactif') == '1') {
                urlComponent += "nonactif=1";
            } else {
                urlComponent += "nonactif=0";
            }
            $(this).select2({
                theme: 'bootstrap',
                minimumInputLength: 3,
                allowClear: true,
                ajax: {
                    type: "GET",
                    url: urlComponent,
                    delay: 500,
                    data: function (params) {
                        var queryParameters = {
                            term: params.term

                        }
                        return queryParameters;
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });
        });
    }

    $(".select2SubmitOnChange").on("change", function (e) {
        if ($(this).val()) {
            $(this).parents('form').submit();
        }
    });
    $.initModalPassage = function () {
        $('#modal-calendrier-infos').on('show.bs.modal', function (event) {
            var link = $(event.relatedTarget);
            if(link.length) {
                $('#modal-calendrier-infos').html("");
                $('#modal-calendrier-infos').load(link.attr('href'), function () {
                    $.callbackEventForm();
                });
            }
        })
    }

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
                data.push({id: words[key] + "", text: (words[key] + "")});
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

}
)(jQuery);
