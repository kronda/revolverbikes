/* global jQuery */
var oneApp = oneApp || {}, $oneApp = $oneApp || jQuery(oneApp);

(function($, $oneApp) {
	'use strict';

	var layoutTemplates = {
		init: function() {
			var $messageBox = $('.ttfmp-import-message'),
				$select = $('#ttfmp-import-content', $messageBox),
				$link = $('#ttfmp-import-link', $messageBox);

			$select.on('change', function(){
				var val = $select.val(),
					url = ttfmpLayoutTemplates.base;

				// Construct the URL
				url += '?ttfmp_template_nonce=' + ttfmpLayoutTemplates.nonce + '&ttfmp_template=' + val + '&ttfmp_post_id=' + ttfmpLayoutTemplates.postID;

				// Replace the URL
				$link.attr('href', url);
			});

			$oneApp.on('afterSectionViewAdded', function() {
				$messageBox.addClass('ttfmp-import-message-hide');
			});

			$oneApp.on('afterSectionViewRemoved', function() {
				if ($('.ttfmake-section').length < 1) {
					$messageBox.removeClass('ttfmp-import-message-hide');
				}
			});
		}
	};

	layoutTemplates.init();
})(jQuery, $oneApp);