var observerCallbacks = new Array();

function addObserverCallback(callback, prepend) {
	if (observerCallbacks.indexOf(callback) < 0) {
		if (prepend)
			observerCallbacks.unshift(callback);
		else
			observerCallbacks.push(callback);
	}
	
	callback();
}

function runObserverCallbacks() {
	for (var i = 0; i < observerCallbacks.length; i++)
		observerCallbacks[i]();
}

$(function() {
	addObserverCallback(function() { $('.mfx-hide-parent-on-click').click(hideParentOnClick); });
	addObserverCallback(setupButtons);
	addObserverCallback(function() { $('.mfx-invisible-at-start').removeClass('mfx-invisible-at-start'); });
	addObserverCallback(mfxCenterElements);

	try {
		var observerExtend = { attributes: true, childList: true, characterData: true, subtree:true };
		var observer = new MutationObserver(function(mutations) {
			observer.disconnect();
			runObserverCallbacks();
			observer.observe(document, observerExtend);
		});
		observer.observe(document, observerExtend);
	}
	catch (e) { }
});