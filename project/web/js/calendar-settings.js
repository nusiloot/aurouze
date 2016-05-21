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
                passage: passage
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
        minTime: '06:00:00',
        maxTime: '19:00:00',
        height: 703,
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
                id: event.id
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
    });
});
