function setupButtons() {
	if ($.ui)
		$('button').button();
}

function hideParentOnClick(event) {
	event.preventDefault();
	$(this).parent().hide();
}

function handleErrorsAndNotifs(container) {
	if (container.errors)
		handleErrors(container.errors);
	if (container.notifs)
		handleNotifs(container.notifs);
}

function handleErrors(errors) {
	if (!errors || errors.length == 0)
		return;
	for (var i = 0; i < errors.length; i++)
	{
		var error = errors[i];
		var str = '';
		if (error.errfile)
			str = error.errnoconstant + ' - [' + error.errfile + ':' + error.errline + ']<br />' + error.errstr;
		else
			str = error.errstr;
		$('#mfx-errors ul').append('<li>' + str + '</li>');
	}
	$('#mfx-errors').removeClass('invisible');
}

function handleNotifs(notifs) {
	if (!notifs || notifs.length == 0)
		return;
	for (var i = 0; i < notifs.length; i++)
		$('#mfx-notifs ul').append('<li>' + notifs[i] + '</li>');
	$('#mfx-notifs').removeClass('invisible');
}

$(function() {
	if ($.ui) {
		$('#mfx-profiler').removeClass('mfx-hcenter mfx-vcenter');
	}
	
	$('#mfx-profiler-bar .mfx-profiler-toggle').unbind('click').click(function(event) {
		event.preventDefault();
		
		if ($.ui)
			$('#mfx-profiler').dialog({
				width: window.innerWith * 0.75,
				modal: true,
				title: $(this).attr('mfx:dialog-title')
			});
		else {
			$('#mfx-profiler').toggle();
		}
		loadProfilerGraph();
	});
});