(function($)
{

	$(document).ready(function()
	{
		$.initAjaxPost();
	});

	$.initAjaxPost = function() 
	{

		var notificationError = $('#ajax_form_error_notification');
		var notificationProgress = $('#ajax_form_progress_notification');

		$(document).ajaxError(
			function(event, xhr, settings) {
				if (settings.type === "POST") {
					notificationError.show();
				}
			}
		);

		$(document).ajaxSuccess(
			function(event, xhr, settings) {
				if (settings.type === "POST") {
					notificationError.hide();
				}
			}
		);

		$(document).ajaxSend(
			function(event, xhr, settings) {
				if (settings.type === "POST") {
					notificationError.hide();
					notificationProgress.show();
				}
			}
		);

		$(document).ajaxComplete(
			function(event, xhr, settings) {
				if (settings.type === "POST") {
					notificationProgress.hide();
				}
			}
		);
	};
})(jQuery);