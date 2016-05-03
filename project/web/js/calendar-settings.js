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
        	var id = $(this).data('identifiant');
        	var title = $.trim($(this).data('title'));
        	$.post(
            $('#calendrier').data('urlRead'), {
                id: id,
                light: 0
            }, function (response) {
                $('#modal-title').text(title);
                $('#modal-body').html(response);
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
        maxTime: '18:00:00',
        height: 698,
        customButtons: {
            prevButton: {
                text: '',
                click: function() {
                    window.location.href = $('#calendrier').data('urlPrev');
                },
                icon: 'left-single-arrow'
            },
            nextButton: {
                text: '',
                click: function() {
                    window.location.href = $('#calendrier').data('urlNext');
                },
                icon: 'right-single-arrow'
            }
        },
        header: {
            left: 'prevButton, title, nextButton',
            right: 'month, agendaWeek, agendaDay',
        },
        lang: 'fr',
        timeFormat: 'H:mm',
        allDaySlot: false,
        eventBackgroundColor: "#fff",
        editable: true,
        droppable: true,
        slotEventOverlap: false,
        hiddenDays: [0],
        defaultView: "agendaWeek",
        eventSources: [
            {
                url: $('#calendrier').data('urlPopulate'),
                type: 'POST',
                data: {
                    title: 1,
                }
            }
        ],
        eventClick: function (event) {
            console.log(event);
            $.post(
                    $('#calendrier').data('urlRead'), {
                id: event.id,
                light: 0
            }, function (response) {
                $('#modal-title').text(event.title);
                $('#modal-body').html(response);
                $('#modal-calendrier-infos').modal();
                $.callbackEventForm();
            }
            );
        },
        eventReceive: function (event) {
            $('#retour_technicien_btn').removeClass('hidden');
            $.post(
                    $('#calendrier').data('urlUpdate'), {
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
    $('#calendrier').fullCalendar('gotoDate', $('#calendrier').data('gotoDate'));
});
