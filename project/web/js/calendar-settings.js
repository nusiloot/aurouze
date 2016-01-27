$(function () {
    var date = new Date();
    var d = date.getDate();
    var m = date.getMonth();
    var y = date.getFullYear();

    $('#calendrier').fullCalendar({
        header: {
            left: 'prev, title, next',
            right: 'month, agendaWeek'
        },
        lang: 'fr',
		timeFormat: 'H:mm',
		allDaySlot: false,
		scrollTime: "08:00:00",
		editable: true,
		droppable: true,
		hiddenDays: [0],
		defaultView: "agendaWeek",
        eventSources: [
            {
                url: Routing.generate('calendarPopulate', {"identifiantEtablissement": $('#calendrier').attr('data-identifiant-etablissement')}),
                type: 'POST',
                error: function() {
                	console.log("populate erreur");
                }
            }
        ],
        eventResize: function(event) {
        	console.log("=== resize ===");      
        	console.log(event.id);     
        	console.log(event.start.format());   
        	console.log(event.end.format());
        },
        eventDrop: function(event) {  
        	console.log("=== drag ===");       
        	console.log(event.id);  
        	console.log(event.start.format());   
        	console.log(event.end.format());
        },
        eventReceive: function(event) {  
        	$.post(
        		Routing.generate('calendarUpdate', {"identifiantEtablissement": $('#calendrier').attr('data-identifiant-etablissement')}), { 
        			id: event.id, 
        			start: event.start.format(),
        			end: event.end.format(),
        		}, function(data) {
                	if (data.error) {
                		console.log("erreur");
                	} else {
                		console.log("ok");
                		event.id = data.id;
                	}
        	});
        },
    });
    $('#fc-events .event').each(function() {
    	$(this).data('event', {
    		title: $(this).attr('data-title'),
    		stick: true
    	});
    	$(this).draggable({
    		zIndex: 999,
    		revert: true,
    		revertDuration: 0
    	});

    });
});
