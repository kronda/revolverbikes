(function($) {
	var ttfmpTypekit = {
		cache: {},

		init: function() {
			// Cache elements
			ttfmpTypekit.cache.$textInput = $('input', '#customize-control-ttfmp-typekit-id');
			ttfmpTypekit.cache.$wrapper = $('#accordion-section-ttfmake_font');
			ttfmpTypekit.cache.$descriptionText = $('p', '#customize-control-ttfmp-typekit-load-fonts');
			ttfmpTypekit.cache.$reset = $('a:nth-child(1)', ttfmpTypekit.cache.$descriptionText);
			ttfmpTypekit.cache.$load = $('a:nth-child(2)', ttfmpTypekit.cache.$descriptionText);
			ttfmpTypekit.cache.$titleFontSelect = $('select', '#customize-control-ttfmake_font-site-title');
			ttfmpTypekit.cache.$headerFontSelect = $('select', '#customize-control-ttfmake_font-header');
			ttfmpTypekit.cache.$bodyFontSelect = $('select', '#customize-control-ttfmake_font-body');

			// Denote which items are Typekit fonts
			ttfmpTypekit.markTypekitChoices();

			// Add classes to elements
			ttfmpTypekit.cache.$reset.addClass('button reset-fonts');
			ttfmpTypekit.cache.$load.addClass('button load-fonts');

			ttfmpTypekit.cache.$wrapper.on('click', '.load-fonts', function(evt) {
				evt.preventDefault();

				// Add the loading status
				ttfmpTypekit.showSpinner();

				// Remove errors
				ttfmpTypekit.removeErrors();

				if ('' !== ttfmpTypekit.cache.$textInput.val()) {
					ttfmpTypekit.makeRequest(ttfmpTypekit.cache.$textInput.val(), ttfmpTypekitData.nonce);
				} else {
					ttfmpTypekit.addError(ttfmpTypekitData.noInputError);
					ttfmpTypekit.hideSpinner();
				}
			});

			ttfmpTypekit.cache.$wrapper.on('click', '.reset-fonts', function(evt) {
				evt.preventDefault();
				ttfmpTypekit.reset();
				ttfmpTypekit.removeErrors();
				ttfmpTypekit.hideSpinner();
			});
		},

		markTypekitChoices: function() {
			_.each(ttfmpTypekitData.typekitChoices, function(value) {
				$('option[value="' + value +'"]', ttfmpTypekit.cache.$titleFontSelect).addClass('ttfmp-typekit-choice');
				$('option[value="' + value +'"]', ttfmpTypekit.cache.$headerFontSelect).addClass('ttfmp-typekit-choice');
				$('option[value="' + value +'"]', ttfmpTypekit.cache.$bodyFontSelect).addClass('ttfmp-typekit-choice');
			});

			// Mark the header as a choice
			if (ttfmpTypekitData.typekitChoices.length > 0) {
				$('.ttfmp-typekit-choice', ttfmpTypekit.cache.$titleFontSelect).first().prev().addClass('ttfmp-typekit-choice');
				$('.ttfmp-typekit-choice', ttfmpTypekit.cache.$headerFontSelect).first().prev().addClass('ttfmp-typekit-choice');
				$('.ttfmp-typekit-choice', ttfmpTypekit.cache.$bodyFontSelect).first().prev().addClass('ttfmp-typekit-choice');
			}
		},

		makeRequest: function(id, nonce) {
			wp.ajax.send(
				'ttfmp_get_typekit_fonts', {
					success: ttfmpTypekit.handleSuccess,
					error: ttfmpTypekit.handleError,
					data: {
						nonce: nonce,
						id: id
					}
				}
			)
		},

		handleSuccess: function(data) {
			var optionsHTML = ttfmpTypekit.buildOption(0, '--- ' + ttfmpTypekitData.headerLabel + ' ---', true),
				titleVal = ttfmpTypekit.cache.$titleFontSelect.val(),
				headerVal = ttfmpTypekit.cache.$headerFontSelect.val(),
				bodyVal = ttfmpTypekit.cache.$bodyFontSelect.val();

			$.each(data, function(index, value) {
				optionsHTML += ttfmpTypekit.buildOption(index, value, false);
			});

			// Remove the previous fonts
			ttfmpTypekit.removeFonts();

			// Prepend the new options
			ttfmpTypekit.prependFonts(optionsHTML);

			// Set the correct current vals
			ttfmpTypekit.cache.$titleFontSelect.val(titleVal);
			ttfmpTypekit.cache.$headerFontSelect.val(headerVal);
			ttfmpTypekit.cache.$bodyFontSelect.val(bodyVal);

			// Remove the loading indicator
			ttfmpTypekit.hideSpinner();
		},

		buildOption: function(index, value, disabled) {
			disabled = (true === disabled) ? ' disabled="disabled"' : '';
			return '<option value="' + index + '" class="ttfmp-typekit-choice"' + disabled + '>' + value + '</option>';
		},

		prependFonts: function(optionsHTML) {
			ttfmpTypekit.cache.$titleFontSelect.prepend(optionsHTML);
			ttfmpTypekit.cache.$headerFontSelect.prepend(optionsHTML);
			ttfmpTypekit.cache.$bodyFontSelect.prepend(optionsHTML);
		},

		removeFonts: function() {
			$('.ttfmp-typekit-choice', ttfmpTypekit.cache.$titleFontSelect).remove();
			$('.ttfmp-typekit-choice', ttfmpTypekit.cache.$headerFontSelect).remove();
			$('.ttfmp-typekit-choice', ttfmpTypekit.cache.$bodyFontSelect).remove();
		},

		handleError: function(data) {
			ttfmpTypekit.addError(ttfmpTypekitData.ajaxError);
			ttfmpTypekit.hideSpinner();
		},

		showSpinner: function() {
			ttfmpTypekit.cache.$descriptionText.append('<span class="spinner"></span>');
		},

		hideSpinner: function() {
			$('.spinner', ttfmpTypekit.cache.$descriptionText).remove();
		},

		addError: function(message) {
			ttfmpTypekit.removeErrors();
			ttfmpTypekit.cache.$descriptionText.prepend('<span class="error">' + message + '<br /></span>');
		},

		removeErrors: function() {
			$('.error', ttfmpTypekit.cache.$descriptionText).remove();
		},

		reset: function() {
			// Remove the text input
			ttfmpTypekit.cache.$textInput.attr('value', '').change();

			// Remove the Typekit fonts
			ttfmpTypekit.removeFonts();

			// Set the default fonts
			ttfmpTypekit.cache.$titleFontSelect.val('sans-serif').change();
			ttfmpTypekit.cache.$headerFontSelect.val('sans-serif').change();
			ttfmpTypekit.cache.$bodyFontSelect.val('Open Sans').change();
		}
	};

	ttfmpTypekit.init();
})(jQuery);