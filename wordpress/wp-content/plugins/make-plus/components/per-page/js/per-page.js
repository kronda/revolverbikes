
(function($){
	$('.ttfmp_per-page_override').on('change', function() {
		var checked = $(this).prop('checked'),
			setting = $(this).parent().parent().find('.ttfmp_per-page_setting');

		if (checked) {
			setting.prop('disabled', '');
		} else {
			setting.prop('disabled', 'disabled');
		}
	});
}(jQuery));