/**
 * @package Make
 */

( function( $ ) {
	var api = wp.customize;

	/**
	 * Visibility toggling for some controls
	 */
	$.each({
		'general-layout': {
			controls: [ 'ttfmake_background-info' ],
			callback: function( to ) { return 'full-width' === to; }
		},
		'background_image': {
			controls: [ 'ttfmake_background_size' ],
			callback: function( to ) { return !! to; }
		},
		'header-background-image': {
			controls: [ 'ttfmake_header-background-repeat', 'ttfmake_header-background-position', 'ttfmake_header-background-size' ],
			callback: function( to ) { return !! to; }
		},
		'header-layout': {
			controls: [ 'ttfmake_header-branding-position' ],
			callback: function( to ) { return ( '1' == to || '3' == to ); }
		},
		'main-background-image': {
			controls: [ 'ttfmake_main-background-repeat', 'ttfmake_main-background-position', 'ttfmake_main-background-size' ],
			callback: function( to ) { return !! to; }
		},
		'footer-background-image': {
			controls: [ 'ttfmake_footer-background-repeat', 'ttfmake_footer-background-position', 'ttfmake_footer-background-size' ],
			callback: function( to ) { return !! to; }
		}
	}, function( settingId, o ) {
		api( settingId, function( setting ) {
			$.each( o.controls, function( i, controlId ) {
				api.control( controlId, function( control ) {
					var visibility = function( to ) {
						control.container.toggle( o.callback( to ) );
					};

					visibility( setting.get() );
					setting.bind( visibility );
				});
			});
		});
	});

	// Set header items as disabled
	$('#customize-control-ttfmake_font-site-title option, #customize-control-ttfmake_font-header option, #customize-control-ttfmake_font-body option')
		.filter(function(index) {
			var val = $(this).val();
			return !isNaN(parseFloat(+val)) && isFinite(val);
		}).attr('disabled', 'disabled');
} )( jQuery );
