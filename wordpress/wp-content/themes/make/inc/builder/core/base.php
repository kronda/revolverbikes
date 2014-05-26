<?php
/**
 * @package Make
 */

if ( ! function_exists( 'TTFMAKE_Builder_Base' ) ) :
/**
 * Defines the functionality for the HTML Builder.
 *
 * @since 1.0.0.
 */
class TTFMAKE_Builder_Base {
	/**
	 * The one instance of TTFMAKE_Builder_Base.
	 *
	 * @since 1.0.0.
	 *
	 * @var   TTFMAKE_Builder_Base
	 */
	private static $instance;

	/**
	 * Instantiate or return the one TTFMAKE_Builder_Base instance.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMAKE_Builder_Base
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initiate actions.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMAKE_Builder_Base
	 */
	public function __construct() {
		// Include the API
		require get_template_directory() . '/inc/builder/core/api.php';

		// Add the core sections
		require get_template_directory() . '/inc/builder/sections/section-definitions.php';

		// Include the save routines
		require get_template_directory() . '/inc/builder/core/save.php';

		// Include the front-end helpers
		require get_template_directory() . '/inc/builder/sections/section-front-end-helpers.php';

		// Set up actions
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 1 ); // Bias toward top of stack
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 11 );
		add_action( 'admin_print_styles-post.php', array( $this, 'admin_print_styles' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'admin_print_styles' ) );
		add_action( 'admin_footer', array( $this, 'print_templates' ) );
		add_action( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 15, 2 );
		add_action( 'after_wp_tiny_mce', array( $this, 'after_wp_tiny_mce' ) );
	}

	/**
	 * Add the meta box.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'ttfmake-builder',
			__( 'Page Builder', 'make' ),
			array( $this, 'display_builder' ),
			'page',
			'normal',
			'high'
		);
	}

	/**
	 * Display the meta box.
	 *
	 * @since  1.0.0.
	 *
	 * @param  WP_Post    $post_local    The current post object.
	 * @return void
	 */
	public function display_builder( $post_local ) {
		wp_nonce_field( 'save', 'ttfmake-builder-nonce' );

		// Get the current sections
		global $ttfmake_sections;
		$ttfmake_sections = get_post_meta( $post_local->ID, '_ttfmake-sections', true );
		$ttfmake_sections = ( is_array( $ttfmake_sections ) ) ? $ttfmake_sections : array();

		// Load the boilerplate templates
		get_template_part( 'inc/builder/core/templates/menu' );
		get_template_part( 'inc/builder/core/templates/stage', 'header' );

		$section_data        = $this->get_section_data( $post_local->ID );
		$registered_sections = ttfmake_get_sections();

		// Print the current sections
		foreach ( $section_data as $section ) {
			if ( isset( $registered_sections[ $section['section-type'] ]['display_template'] ) ) {
				// Print the saved section
				$this->load_section( $registered_sections[ $section['section-type'] ], $section );
			}
		}

		get_template_part( 'inc/builder/core/templates/stage', 'footer' );

		// Add the sort input
		$section_order = get_post_meta( $post_local->ID, '_ttfmake-section-ids', true );
		$section_order = ( ! empty( $section_order ) ) ? implode( ',', $section_order ) : '';
		echo '<input type="hidden" value="' . esc_attr( $section_order ) . '" name="ttfmake-section-order" id="ttfmake-section-order" />';
	}

	/**
	 * Enqueue the JS and CSS for the admin.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $hook_suffix    The suffix for the screen.
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		// Only load resources if they are needed on the current page
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) || 'page' !== get_post_type() ) {
			return;
		}

		// Enqueue the CSS
		wp_enqueue_style(
			'ttfmake-builder',
			get_template_directory_uri() . '/inc/builder/core/css/builder.css',
			array(),
			TTFMAKE_VERSION
		);

		wp_enqueue_style( 'wp-color-picker' );

		// Dependencies regardless of min/full scripts
		$dependencies = array(
			'wplink',
			'utils',
			'wp-color-picker',
			'jquery-effects-core',
			'jquery-ui-sortable',
			'backbone',
		);

		// Only load full scripts for WordPress.com and those with SCRIPT_DEBUG set to true
		wp_register_script(
			'ttfmake-builder/js/tinymce.js',
			get_template_directory_uri() . '/inc/builder/core/js/tinymce.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-builder/js/models/section.js',
			get_template_directory_uri() . '/inc/builder/core/js/models/section.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-builder/js/collections/sections.js',
			get_template_directory_uri() . '/inc/builder/core/js/collections/sections.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-builder/js/views/menu.js',
			get_template_directory_uri() . '/inc/builder/core/js/views/menu.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_register_script(
			'ttfmake-builder/js/views/section.js',
			get_template_directory_uri() . '/inc/builder/core/js/views/section.js',
			array(),
			TTFMAKE_VERSION,
			true
		);

		wp_enqueue_script(
			'ttfmake-builder',
			get_template_directory_uri() . '/inc/builder/core/js/app.js',
			apply_filters(
				'ttfmake_builder_js_dependencies',
				array_merge(
					$dependencies,
					array(
						'ttfmake-builder/js/tinymce.js',
						'ttfmake-builder/js/models/section.js',
						'ttfmake-builder/js/collections/sections.js',
						'ttfmake-builder/js/views/menu.js',
						'ttfmake-builder/js/views/section.js',
					)
				)
			),
			TTFMAKE_VERSION,
			true
		);

		// Add data needed for the JS
		$data = array(
			'pageID' => get_the_ID(),
		);

		wp_localize_script(
			'ttfmake-builder',
			'ttfmakeBuilderData',
			$data
		);
	}

	/**
	 * Print additional, dynamic CSS for the builder interface.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function admin_print_styles() {
		global $pagenow;

		// Do not complete the function if the product template is in use (i.e., the builder needs to be shown)
		if ( 'page' !== get_post_type() ) {
			return;
		}
	?>
		<style type="text/css">
			<?php if ( 'post-new.php' === $pagenow || ( 'post.php' === $pagenow && 'template-builder.php' === get_page_template_slug() ) ) : ?>
			#postdivrich {
				display: none;
			}
			<?php else : ?>
			#ttfmake-builder {
				display: none;
			}
			<?php endif; ?>

			<?php foreach ( ttfmake_get_sections() as $key => $section ) : ?>
			#ttfmake-menu-list-item-link-<?php echo esc_attr( $section['id'] ); ?> .ttfmake-menu-list-item-link-icon-wrapper {
				background-image: url(<?php echo addcslashes( esc_url_raw( $section['icon'] ), '"' ); ?>);
			}
			<?php endforeach; ?>
		</style>
	<?php
	}

	/**
	 * Reusable component for adding an image uploader.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $section_name    Name of the current section.
	 * @param  int       $image_id        ID of the current image.
	 * @param  array     $messages        Message to show.
	 * @return void
	 */
	public function add_uploader( $section_name, $image_id = 0, $messages = array() ) {
		$image        = wp_get_attachment_image( $image_id, 'large' );
		$add_state    = ( '' === $image ) ? 'ttfmake-show' : 'ttfmake-hide';
		$remove_state = ( '' === $image ) ? 'ttfmake-hide' : 'ttfmake-show';

		// Set default messages. Note that the theme textdomain is not used in some cases
		// because the strings are core i18ns
		$messages['add']    = ( empty( $messages['add'] ) )    ? __( 'Set featured image' )               : $messages['add'];
		$messages['remove'] = ( empty( $messages['remove'] ) ) ? __( 'Remove featured image' )            : $messages['remove'];
		$messages['title']  = ( empty( $messages['title'] ) )  ? __( 'Featured Image', 'make' )        : $messages['title'];
		$messages['button'] = ( empty( $messages['button'] ) ) ? __( 'Use as Featured Image', 'make' ) : $messages['button'];
		?>
		<div class="ttfmake-uploader">
			<div class="ttfmake-media-uploader-placeholder ttfmake-media-uploader-add">
				<?php if ( '' !== $image ) : ?>
					<?php echo $image; ?>
				<?php endif; ?>
			</div>
			<div class="ttfmake-media-link-wrap">
				<a href="#" class="ttfmake-media-uploader-add ttfmake-media-uploader-set-link <?php echo $add_state; ?>" data-title="<?php echo esc_attr( $messages['title'] ); ?>" data-button-text="<?php echo esc_attr( $messages['button'] ); ?>">
					<?php echo $messages['add']; ?>
				</a>
				<a href="#" class="ttfmake-media-uploader-remove <?php echo $remove_state; ?>">
					<?php echo $messages['remove']; ?>
				</a>
			</div>
			<input type="hidden" name="<?php echo esc_attr( $section_name ); ?>[image-id]" value="<?php echo absint( $image_id ); ?>" class="ttfmake-media-uploader-value" />
		</div>
	<?php
	}

	/**
	 * Load a section template with an available data payload for use in the template.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $section     The section data.
	 * @param  array     $data        The data payload to inject into the section.
	 * @return void
	 */
	public function load_section( $section, $data = array() ) {
		if ( ! isset( $section['id'] ) ) {
			return;
		}

		// Globalize the data to provide access within the template
		global $ttfmake_section_data;
		$ttfmake_section_data = array(
			'data'    => $data,
			'section' => $section,
		);

		// Include the template
		get_template_part( $section['builder_template'] );

		// Destroy the variable as a good citizen does
		unset( $GLOBALS['ttfmake_section_data'] );
	}

	/**
	 * Print out the JS section templates
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function print_templates() {
		global $hook_suffix, $typenow, $ttfmake_is_js_template;
		$ttfmake_is_js_template = true;

		// Only show when adding/editing pages
		if ( 'page' !== $typenow || ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) )) {
			return;
		}

		// Print the templates
		foreach ( ttfmake_get_sections() as $key => $section ) : ?>
			<script type="text/html" id="tmpl-ttfmake-<?php echo esc_attr( $section['id'] ); ?>">
			<?php
			ob_start();
			$this->load_section( $section, array() );
			$html = ob_get_clean();

			$html = str_replace(
				array(
					'temp',
				),
				array(
					'{{{ id }}}',
				),
				$html
			);

			echo $html;
			?>
		</script>
		<?php endforeach;

		unset( $GLOBALS['ttfmake_is_js_template'] );
	}

	/**
	 * Wrapper function to produce a WP Editor with special defaults.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $content     The content to display in the editor.
	 * @param  string    $name        Name of the editor.
	 * @param  array     $settings    Setting to send to the editor.
	 * @return void
	 */
	public function wp_editor( $content, $name, $settings = array() ) {
		$settings = wp_parse_args( $settings, array(
			'tinymce'   => array(
				'toolbar1' => 'bold,italic,link,unlink',
				'toolbar2' => '',
				'toolbar3' => '',
				'toolbar4' => '',
			),
			'quicktags' => array(
				'buttons' => 'strong,em,link',
			),
			'editor_height' => 150,
		) );

		// Remove the default media buttons action and replace it with the custom one
		remove_action( 'media_buttons', 'media_buttons' );
		add_action( 'media_buttons', array( $this, 'media_buttons' ) );

		// Render the editor
		wp_editor( $content, $name, $settings );

		// Reinstate the original media buttons function
		remove_action( 'media_buttons', array( $this, 'media_buttons' ) );
		add_action( 'media_buttons', 'media_buttons' );
	}

	/**
	 * Add the media buttons to the text editor.
	 *
	 * This is a copy and modification of the core "media_buttons" function. In order to make the media editor work
	 * better for smaller width screens, we need to wrap the button text in a span tag. By doing so, we can hide the
	 * text in some situations.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $editor_id    The value of the current editor ID.
	 * @return void
	 */
	public function media_buttons( $editor_id = 'content' ) {
		$post = get_post();
		if ( ! $post && ! empty( $GLOBALS['post_ID'] ) ) {
			$post = $GLOBALS['post_ID'];
		}

		wp_enqueue_media( array(
			'post' => $post
		) );

		$img = '<span class="wp-media-buttons-icon"></span>';

		// Note that the theme textdomain is not used for Add Media in order to use the core l10n
		echo '<a href="#" id="insert-media-button" class="button insert-media add_media" data-editor="' . esc_attr( $editor_id ) . '" title="' . esc_attr__( 'Add Media' ) . '">' . $img . ' <span class="ttfmake-media-button-text">' . __( 'Add Media' ) . '</span></a>';
	}

	/**
	 * Append the editor styles to the section editors.
	 *
	 * Unfortunately, the `wp_editor()` function does not support a "content_css" argument. As a result, the stylesheet
	 * for the "content_css" parameter needs to be added via a filter.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array     $mce_init     The array of tinyMCE settings.
	 * @param  string    $editor_id    The ID for the current editor.
	 * @return array                   The modified settings.
	 */
	function tiny_mce_before_init( $mce_init, $editor_id ) {
		// Only add stylesheet to a section editor
		if ( false === strpos( $editor_id, 'make' ) ) {
			return $mce_init;
		}

		// Editor styles
		$editor_styles = array();
		if ( '' !== $google_request = ttfmake_get_google_font_uri() ) {
			$editor_styles[] = $google_request;
		}

		$editor_styles[] = get_template_directory_uri() . '/css/font-awesome.css';
		$editor_styles[] = get_template_directory_uri() . '/css/editor-style.css';

		// Append in the customizer styles if available
		if ( function_exists( 'ttfmake_get_css' ) && ttfmake_get_css()->build() ) {
			$editor_styles[] = add_query_arg( 'action', 'ttfmake-css', admin_url( 'admin-ajax.php' ) );
		}

		// Create string of CSS files
		$content_css = implode( ',', $editor_styles );

		// If there is already a stylesheet being added, append and do not override
		if ( isset( $mce_init['content_css'] ) ) {
			$mce_init['content_css'] .= ',' . $content_css;
		} else {
			$mce_init['content_css'] = $content_css;
		}

		return $mce_init;
	}

	/**
	 * Denote the default editor for the user.
	 *
	 * Note that it would usually be ideal to expose this via a JS variable using wp_localize_script; however, it is
	 * being printed here in order to guarantee that nothing changes this value before it would otherwise be printed.
	 * The "after_wp_tiny_mce" action appears to be the most reliable place to print this variable.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $settings   TinyMCE settings.
	 * @return void
	 */
	public function after_wp_tiny_mce( $settings ) {
		?>
		<script type="text/javascript">
			var ttfmakeMCE = '<?php echo esc_js( wp_default_editor() ); ?>';
		</script>
	<?php
	}

	/**
	 * Retrieve all of the data for the sections.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $post_id    The post to retrieve the data from.
	 * @return array                 The combined data.
	 */
	public function get_section_data( $post_id ) {
		$ordered_data = array();
		$ids          = get_post_meta( $post_id, '_ttfmake-section-ids', true );
		$post_meta    = get_post_meta( $post_id );

		// Temp array of hashed keys
		$temp_data = array();

		// Any meta containing the old keys should be deleted
		if ( is_array( $post_meta ) ) {
			foreach ( $post_meta as $key => $value ) {
				// Only consider builder values
				if ( 0 === strpos( $key, '_ttfmake:' ) ) {
					// Get the individual pieces
					$temp_data[ str_replace( '_ttfmake:', '', $key ) ] = $value[0];
				}
			}
		}

		// Create multidimensional array from postmeta
		$data = $this->create_array_from_meta_keys( $temp_data );

		// Reorder the data in the order specified by the section IDs
		if ( is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				if ( isset( $data[ $id ] ) ) {
					$ordered_data[ $id ] = $data[ $id ];
				}
			}
		}

		return $ordered_data;
	}

	/**
	 * Convert an array with array keys that map to a multidimensional array to the array.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $arr    The array to convert.
	 * @return array            The converted array.
	 */
	function create_array_from_meta_keys( $arr ) {
		// The new multidimensional array we will return
		$result = array();

		// Process each item of the input array
		foreach ( $arr as $key => $value ) {
			// Store a reference to the root of the array
			$current = & $result;

			// Split up the current item's key into its pieces
			$pieces = explode( ':', $key );

			/**
			 * For all but the last piece of the key, create a new sub-array (if necessary), and update the $current
			 * variable to a reference of that sub-array.
			 */
			for ( $i = 0; $i < count( $pieces ) - 1; $i++ ) {
				$step = $pieces[ $i ];
				if ( ! isset( $current[ $step ] ) ) {
					$current[ $step ] = array();
				}
				$current = & $current[ $step ];
			}

			// Add the current value into the final nested sub-array
			$current[ $pieces[ $i ] ] = $value;
		}

		// Return the result array
		return $result;
	}
}
endif;

if ( ! function_exists( 'ttfmake_get_builder_base' ) ) :
/**
 * Instantiate or return the one TTFMAKE_Builder_Base instance.
 *
 * @since  1.0.0.
 *
 * @return TTFMAKE_Builder_Base
 */
function ttfmake_get_builder_base() {
	return TTFMAKE_Builder_Base::instance();
}
endif;

add_action( 'admin_init', 'ttfmake_get_builder_base', 1 );

if ( ! function_exists( 'ttfmake_load_section_header' ) ) :
/**
 * Load a consistent header for sections.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_load_section_header() {
	get_template_part( '/inc/builder/core/templates/section', 'header' );
}
endif;

if ( ! function_exists( 'ttfmake_load_section_footer' ) ) :
/**
 * Load a consistent footer for sections.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_load_section_footer() {
	get_template_part( '/inc/builder/core/templates/section', 'footer' );
}
endif;

if ( ! function_exists( 'ttfmake_get_wp_editor_id' ) ) :
/**
 * Generate the ID for a WP editor based on an existing or future section number.
 *
 * @since  1.0.0.
 *
 * @param  array     $data              The data for the section.
 * @param  array     $is_js_template    Whether a JS template is being printed or not.
 * @return string                       The editor ID.
 */
function ttfmake_get_wp_editor_id( $data, $is_js_template ) {
	$id_base = 'ttfmakeeditor' . $data['section']['id'];

	if ( $is_js_template ) {
		$id = $id_base . 'temp';
	} else {
		$id = $id_base . $data['data']['id'];
	}

	return $id;
}
endif;

if ( ! function_exists( 'ttfmake_get_section_name' ) ) :
/**
 * Generate the name of a section.
 *
 * @since  1.0.0.
 *
 * @param  array     $data              The data for the section.
 * @param  array     $is_js_template    Whether a JS template is being printed or not.
 * @return string                       The name of the section.
 */
function ttfmake_get_section_name( $data, $is_js_template ) {
	$name = 'ttfmake-section';

	if ( $is_js_template ) {
		$name .= '[{{{ id }}}]';
	} else {
		$name .= '[' . $data['data']['id'] . ']';
	}

	return $name;
}
endif;

if ( ! function_exists( 'ttfmake_sanitize_text' ) ) :
/**
 * Allow only the allowedtags array in a string.
 *
 * @since  1.0.0.
 *
 * @param  string    $string    The unsanitized string.
 * @return string               The sanitized string.
 */
function ttfmake_sanitize_text( $string ) {
	global $allowedtags;
	return wp_kses( $string , $allowedtags );
}
endif;