$(function () {
    /**
     * Dropping Elements
     */
    $('#fc-events .event').each(function () {
        $(this).data('event', {
            title: $.trim($(this).data('title')),
            stick: true
        });
        $(this).draggable({
            zIndex: 999,
            revert: true,
            revertDuration: 0
        });
        $(this).click(function () {
            var passage = $(this).data('passage');
            $.get(
            $('#calendrier').data('urlRead'), {
                passage: passage,
                service: encodeURI(location.href)
            }, function (response) {
                $('#modal-calendrier-infos').html(response);
                $('#modal-calendrier-infos').modal();
                $.callbackEventForm();
            }
            );
        });

    });

    /**
     * FullCalendar Settings
     */
    $('#calendrier').fullCalendar({
        minTime: '05:00:00',
        maxTime: '20:00:00',
        height: 810,
        customButtons: {
            prevButton: {
                text: '',
                click: function () {
                    window.location.href = $('#calendrier').data('urlPrev');
                },
                icon: 'left-single-arrow'
            },
            nextButton: {
                text: '',
                click: function () {
                    window.location.href = $('#calendrier').data('urlNext');
                },
                icon: 'right-single-arrow'
            }
        },
        dayRender: function (date, cell) {
            cell.css("background-color", "transparent");
        },
        header: false,
        lang: 'fr',
        defaultDate: $('#calendrier').data('gotoDate'),
        timeFormat: 'H:mm',
        allDaySlot: false,
        eventBackgroundColor: "#fff",
        editable: true,
        droppable: true,
        slotEventOverlap: false,
        weekends:  $('#calendrier').data('weekends'),
        defaultView: $('#calendrier').data('view'),
        eventSources: [
            {
                url: $('#calendrier').data('urlPopulate'),
                type: 'GET',
                data: {
                    title: 1,
                }
            }
        ],
        eventClick: function (event) {
            $.get(
                $('#calendrier').data('urlRead'), {
                id: event.id,
                service: encodeURI(location.href)
            }, function (response) {
                $('#modal-calendrier-infos').html(response);
                $('#modal-calendrier-infos').modal();
                $.callbackEventForm();
            }
            );
        },
        dayClick: function(date, jsEvent, view) {
            $.get(
                $('#calendrier').data('urlAddLibre'), {'start': date.format()}
             , function (response) {
                $('#modal-calendrier-infos').html(response);
                $('#modal-calendrier-infos').on('shown.bs.modal', function() {
                    $('#modal-calendrier-infos').find('[autofocus="autofocus"]').focus();
                    $.callbackEventForm();
                });
                $('#modal-calendrier-infos').modal();
            }
            );
        },
        eventReceive: function (event) {
            $('#retour_technicien_btn').removeClass('hidden');
            $.post(
                $('#calendrier').data('urlAdd'), {
                id: null,
                start: event.start.format(),
                end: event.end.format()
            }, function (data) {
                event.id = data.id;
                event.backgroundColor = data.backgroundColor;
                event.textColor = data.textColor;
                event.retourMap = data.retourMap;
                $('#calendrier').fullCalendar('updateEvent', event);
            }
            );
        },
        drop: function () {
            $(this).remove();
        },
        eventResize: function (event) {
            $.post(
                    $('#calendrier').data('urlUpdate'), {
                id: event.id,
                start: event.start.format(),
                end: event.end.format()
            });
        },
        eventDrop: function (event) {
            $.post(
                    $('#calendrier').data('urlUpdate'), {
                id: event.id,
                start: event.start.format(),
                end: event.end.format()
            });
        },
        eventRender: function(event, element) {
          if(event.retourMap){
             var url = event.retourMap;
             var dayOfMonth = event.start.format().substr(8,2);
             var month = event.start.format().substr(0,7).replace('-','');
             if(dayOfMonth > "20"){
                 var nextMonth = ""+(parseInt(month)+1);
                 url = url.replace("mois="+month,"mois="+nextMonth);
             }
             element.find(".fc-title").append('<a style="position:absolute; top: 0; right:0; opacity:0.2;" class="btn btn-default btn-xs " href="'+url+'"><span class="mdi mdi-map"></span></a>');
          }
        },
        eventAfterRender: function(event, element) {
          $.callbackCalendarDynamicButton();
        }
    });
});
