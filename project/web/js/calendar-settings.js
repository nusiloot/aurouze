$(function () {
	/**
	 * FullCalendar Settings
	 */
    $('#calendrier').fullCalendar({
        header: {
            left: 'prev, title, next',
            right: 'month, agendaWeek, agendaDay'
        },
        lang: 'fr',
		timeFormat: 'H:mm',
		allDaySlot: false,
		scrollTime: "08:00:00",
		editable: true,
		hiddenDays: [0],
		defaultView: "agendaWeek",
        eventSources: [
            {
                url: $('#calendrier').data('urlPopulate'),
                type: 'POST'
            }
        ],
        eventClick: function(event) {
        		$.post(
        			$('#calendrier').data('urlRead'), { 
        			id: event.id
        		}, function(response) {
        			$('#modal-title').text(event.title);
        			$('#modal-body').html(response);
        			$('#modal-calendrier-infos').modal();
        		}
        	);
        },
        dayClick: function(moment, jsEvent, view) {
        	$.post(
        			$('#calendrier').data('urlUpdate'), { 
        			id: null, 
        			start: moment.format(),
        			end: null,
        		}, function(data) {
        			$('#calendrier').fullCalendar('addEventSource', [data]);
        		}
        	);
        },
        eventResize: function(event) {
        	$.post(
        			$('#calendrier').data('urlUpdate'), { 
        			id: event.id, 
        			start: event.start.format(),
        			end: event.end.format(),
        		});
        },
        eventDrop: function(event) {    
        	$.post(
        			$('#calendrier').data('urlUpdate'), { 
        			id: event.id, 
        			start: event.start.format(),
        			end: event.end.format(),
        		});
        },
    });
});
