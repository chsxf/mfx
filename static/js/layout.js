function mfxCenterElements() {
	$('.mfx-hcenter').each(function() {
		var el = $(this);
		var parent = el.parent();
		el.css('left', (parent.innerWidth() - el.outerWidth()) / 2); 
	});
	
	$('.mfx-vcenter').each(function() {
		var el = $(this);
		var parent = el.parent();
		el.css('top', (parent.innerHeight() - el.outerHeight()) / 2);
	});
}

window.addEventListener('resize', mfxCenterElements, false);
$(function() {
	mfxCenterElements();
	setInterval(mfxCenterElements, 100);
});