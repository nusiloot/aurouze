(function ($)
{

    $(document).ready(function ()
    {
        $.initAjaxPost();
        $.initSelect2();
        $.initSelect2Ajax();
        $.initTooltips();
        $.initHamzaStyle();
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
        $.initMap();
        $.initTypeheadFacture();
        $.initTypeheadSearchable();
        $.initSomme();
    });

    $.initSomme = function () {
        $('.nombreSomme').blur(function () {
            var total = 0.0;
            $('.nombreSomme').each(function () {
                if ($(this).val() && $(this).val()!= "") {
                    total += parseFloat($(this).val().replace(',', '.'));
                }
            });
            var totalToPrint = total.toFixed(2).toString().replace('.', ',');
            $(".sommeTotal").html(totalToPrint);
        });
    }
    $.initLinkCalendar = function () {
        $('#calendrier .fc-day-header').each(function () {
            if ($(this).data('date')) {
                var content = '<a href="' + ($('#calendrier').data('url-date')).replace('-d', $(this).data('date')) + '">' + $(this).text() + '</a>';
                $(this).html(content);
            }
        });
        $('#calendrier .fc-day-number').each(function () {
            if ($(this).data('date')) {
                var content = '<a href="' + ($('#calendrier').data('url-date')).replace('-d', $(this).data('date')) + '">' + $(this).text() + '</a>';
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
            var defaultTiming = ($(this).attr('data-default')) ? $(this).attr('data-default') : '';
            $(this).timepicker({
                format: 'HH:ii p',
                autoclose: true,
                showMeridian: false,
                startView: 1,
                maxView: 1,
                defaultTime: "" + defaultTiming
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
        $.initTypeheadFacture();
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

    $.initTypeheadFacture = function () {
        if (!$('#factureLibre').length) {
            return;
        }
        var produits = $('#factureLibre').data('produits');

        var produitsSource = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('libelle'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            local: produits
        });

        $('td > .typeahead').typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        },
        {
            limit: 10,
            name: 'produits',
            display: 'libelle',
            source: produitsSource,
            templates: {
                suggestion: function (e) {
                    var libelle = e.libelle + "<span class='text-muted'> à " + e.prix + " €</span>";
                    if (e.conditionnement) {
                        libelle += "<small> (" + e.conditionnement + ")</small>";
                    }
                    return $("<div>" + libelle + "</div>");
                }
            }
        });

        $('.typeahead').bind('typeahead:select', function (ev, suggestion) {
            $(this).parents(".dynamic-collection-item").find('.prix-unitaire').val(suggestion.prix);
        });
    }
    
    

    $.initTypeheadSearchable = function () {
        if (!$('#searchable').length) {
            return;
        }
        var url = $('#searchable').data('url');
        var type = $('#searchable').data('type');
        var target = $('#searchable').data('target');
        var checkbox = $('#searchable').find("input[type=checkbox]");
        
        $('#searchable .typeahead').typeahead({
    	  hint: true,
    	  highlight: true,
    	  minLength: 3
    	},
    	{
    	  async: true,
    	  source: function (query, processSync, processAsync) {
    	    return $.ajax({
    	      url: url, 
    	      type: 'GET',
    	      data: {q: query, inactif: (checkbox && checkbox.is(':checked'))? 1 : 0},
    	      dataType: 'json',
    	      success: function (json) {
    	        return processAsync(json);
    	      }
    	    });
    	  },
          templates: {
              suggestion: function (e) {
            	  if (type == 'societe') {
	            	  var result = '<i class="mdi mdi-'+e.icon+' mdi-lg"></i>&nbsp;'+e.libelle+' <small>'+e.instance+'&nbsp;'+e.identifiant+'</small>';
	            	  if (!e.actif) {
	            		  result = result+' <small><label class="label label-xs label-danger">SUSPENDU</label></small>';
	            	  }
	                  return $('<div class="searchable_result"><a href="'+target.replace('_id_', e.id)+'">'+result+'</a></div>');
            	  }
            	  if (type == 'contrat') {

	            	  var result = e.type+' <small class="text-'+e.color+'">'+e.statut+'</small> n°<strong>'+e.identifiant+'</strong> '+e.periode+' <small class="text-muted">'+e.garantie+'</small> '+e.prix+' €';
	                  return $('<div class="searchable_result"><a href="'+target.replace('_id_', e.id)+'">'+result+'</a></div>');
            	  }
              }
          }
    	});
    }

    $.initFactureLibre = function () {


    }

    $.initModalPassage = function () {
        $('#modal-calendrier-infos').on('show.bs.modal', function (event) {
            var link = $(event.relatedTarget);
            if (link.length) {
                $('#modal-calendrier-infos').html("");
                $('#modal-calendrier-infos').load(link.attr('href'), function () {
                    $.callbackEventForm();
                });
            }
        })
    }

    $.initHamzaStyle = function () {
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
    }

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
                    var words = JSON.parse($(this).attr('data-words'));
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

    $.initMap = function () {
        if ($('#map').length) {
            var lat = 48.8593829;
            var lon = 2.347227;
            if ($('#map').attr('data-lat') && $('#map').attr('data-lon')) {
                lat = $('#map').data('lat');
                lon = $('#map').data('lon');
            }

            var map = L.map('map').setView([lat, lon], 2);

            L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var geojson = JSON.parse($('#map').attr('data-geojson'));
            var markers = [];
            var hoverTimeout = null;

            L.geoJson(geojson,
                    {
                        onEachFeature: function (feature, layer) {
                            if ($('#liste_passage').length) {
                                layer.on('mouseover', function (e) {
                                    $('.leaflet-marker-icon').css('opacity', '0.5');
                                    $(e.target._icon).css('opacity', '1');
                                    e.target.setZIndexOffset(1001);
                                    if (hoverTimeout) {
                                        clearTimeout(hoverTimeout);
                                    }
                                    hoverTimeout = setTimeout(function () {
                                        $('#liste_passage .list-group-item').blur();
                                        var element = $('#' + e.target.feature.properties._id);
                                        var list = $('#liste_passage');
                                        list.scrollTop(0);
                                        list.scrollTop(element.position().top - (list.height() / 2) + (element.height()));
                                        element.focus();
                                    }, 400);
                                });
                                layer.on('mouseout', function (e) {
                                    if (hoverTimeout) {
                                        clearTimeout(hoverTimeout);
                                    }
                                    e.target.setZIndexOffset(900);
                                    $('#' + e.target.feature.properties._id).blur();
                                    $('.leaflet-marker-icon').css('opacity', '1');
                                });

                                layer.on('click', function (e) {
                                    document.location.href = $('#' + e.target.feature.properties._id).attr('href');
                                });
                            }
                        },
                        pointToLayer: function (feature, latlng) {
                            var marker = L.marker(latlng, {icon: L.ExtraMarkers.icon({
                                    icon: feature.properties.icon,
                                    markerColor: feature.properties.color,
                                    iconColor: feature.properties.colorText,
                                    shape: 'circle',
                                    prefix: 'mdi',
                                    svg: true
                                })});
                            markers[feature.properties._id] = marker;
                            return marker;
                        }
                    }
            ).addTo(map);

            var markersArr = [];
            for (id in markers) {
                var latlng = markers[id]._latlng;
                markersArr.push(latlng);
            }
            var bounds = new L.LatLngBounds(markersArr);

            map.fitBounds(bounds);

            $('#liste_passage .list-group-item').hover(function () {
                var marker = markers[$(this).attr('id')];
                $('.leaflet-marker-icon').css('opacity', '0.3');
                $(marker._icon).css('opacity', '1');
                marker.setZIndexOffset(1001);
            }, function () {
                var marker = markers[$(this).attr('id')];
                marker.setZIndexOffset(900);
                $('.leaflet-marker-icon').css('opacity', '1');
            });

            $(window).on('hashchange', function () {
                $('#liste_passage .list-group-item').each(function () {
                    if (!$(this).is(':visible')) {
                        var marker = markers[$(this).attr('id')];
                        $(marker._icon).css('opacity', '0');
                        $(marker._icon).addClass('hidden');
                        $(marker._shadow).addClass('hidden');
                        marker.setZIndexOffset(1001);
                    } else {
                        var marker = markers[$(this).attr('id')];
                        $(marker._icon).css('opacity', '1');
                        $(marker._icon).removeClass('hidden');
                        $(marker._shadow).removeClass('hidden');
                        marker.setZIndexOffset(900);
                    }

                });
            });
        }
    }

}
)(jQuery);
