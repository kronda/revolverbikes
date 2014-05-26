<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_init' ) ) :
/**
 * Load the customizer files and enqueue scripts
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_init() {
	$path = '/inc/customizer/';

	// Always load
	require_once( get_template_directory() . $path . 'helpers.php' );
	require_once( get_template_directory() . $path . 'helpers-css.php' );
	require_once( get_template_directory() . $path . 'helpers-defaults.php' );
	require_once( get_template_directory() . $path . 'helpers-display.php' );
	require_once( get_template_directory() . $path . 'helpers-fonts.php' );
	require_once( get_template_directory() . $path . 'helpers-logo.php' );

	// Hook up functions
	add_action( 'customize_register', 'ttfmake_customizer_add_sections' );
	add_action( 'customize_register', 'ttfmake_customizer_set_transport' );
	add_action( 'customize_preview_init', 'ttfmake_customizer_preview_script' );
	add_action( 'customize_preview_init', 'ttfmake_add_customizations' );
	add_action( 'customize_controls_enqueue_scripts', 'ttfmake_customizer_sections_script' );
	add_action( 'customize_controls_print_styles', 'ttfmake_customizer_admin_styles' );
}
endif;

add_action( 'after_setup_theme', 'ttfmake_customizer_init' );

if ( ! function_exists( 'ttfmake_customizer_add_sections' ) ) :
/**
 * Add sections and controls to the customizer.
 *
 * Hooked to 'customize_register' via ttfmake_customizer_init().
 *
 * @since  1.0.0.
 *
 * @param  WP_Customize_Manager    $wp_customize    Theme Customizer object.
 * @return void
 */
function ttfmake_customizer_add_sections( $wp_customize ) {
	$path         = '/inc/customizer/';
	$section_path = $path . 'sections/';

	// Get the custom controls
	require_once( get_template_directory() . $path . 'controls.php' );

	// Modifications for existing sections
	require_once( get_template_directory() . $section_path . 'background.php' );
	require_once( get_template_directory() . $section_path . 'navigation.php' );
	require_once( get_template_directory() . $section_path . 'site-title-tagline.php' );

	// List of new sections to add
	$sections = array(
		'general'        => __( 'General', 'make' ),
		'font'           => __( 'Fonts', 'make' ),
		'color'          => __( 'Colors', 'make' ),
		'header'         => __( 'Header', 'make' ),
		'logo'           => __( 'Logo', 'make' ),
		'main'           => __( 'Main', 'make' ),
		'layout-blog'    => __( 'Layout: Blog (Posts Page)', 'make' ),
		'layout-archive' => __( 'Layout: Archives', 'make' ),
		'layout-search'  => __( 'Layout: Search Results', 'make' ),
		'layout-post'    => __( 'Layout: Posts', 'make' ),
		'layout-page'    => __( 'Layout: Pages', 'make' ),
		'footer'         => __( 'Footer', 'make' ),
		'social'         => __( 'Social Profiles &amp; RSS', 'make' )
	);
	$sections = apply_filters( 'ttfmake_customizer_sections', $sections );

	// Priority for first section
	$priority = new TTFMAKE_Prioritizer( 0, 10 );

	// Add and populate each section, if it exists
	foreach ( $sections as $section => $title ) {
		// First load the file
		if ( '' !== locate_template( $section_path . $section . '.php', true ) ) {
			// Custom priorities for built-in sections
			if ( 'font' === $section ) {
				$wp_customize->get_section( 'background_image' )->priority = $priority->add();
			}
			if ( 'logo' === $section ) {
				$wp_customize->get_section( 'title_tagline' )->priority = $priority->add();
			}
			if ( 'main' === $section ) {
				$wp_customize->get_section( 'nav' )->priority = $priority->add();
			}
			if ( 'layout-blog' === $section ) {
				$wp_customize->get_section( 'static_front_page' )->priority = $priority->add();
			}

			// Then add the section
			$section_callback = 'ttfmake_customizer_';
			$section_callback .= ( strpos( $section, '-' ) ) ? str_replace( '-', '_', $section ) : $section;
			if ( function_exists( $section_callback ) ) {
				$section_id = 'ttfmake_' . esc_attr( $section );
				if ( ! $title ) {
					$title = ucfirst( esc_attr( $section ) );
				}

				// Add section
				$wp_customize->add_section(
					$section_id,
					array(
						'title'    => $title,
						'priority' => $priority->add(),
					)
				);

				// Callback to populate the section
				call_user_func_array(
					$section_callback,
					array(
						$wp_customize,
						$section_id
					)
				);
			}
		}
	}
}
endif;

if ( ! function_exists( 'ttfmake_customizer_set_transport' ) ) :
/**
 * Add postMessage support for certain built-in settings in the Theme Customizer.
 *
 * Allows these settings to update asynchronously in the Preview pane.
 *
 * @since  1.0.0.
 *
 * @param  WP_Customize_Manager    $wp_customize    Theme Customizer object.
 * @return void
 */
function ttfmake_customizer_set_transport( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
}
endif;

if ( ! function_exists( 'ttfmake_customizer_preview_script' ) ) :
/**
 * Enqueue customizer preview script
 *
 * Hooked to 'customize_preview_init' via ttfmake_customizer_init()
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_preview_script() {
	$path = '/inc/customizer/js/';

	wp_enqueue_script(
		'ttfmake-customizer-preview',
		get_template_directory_uri() . $path . 'customizer-preview' . TTFMAKE_SUFFIX . '.js',
		array( 'customize-preview' ),
		TTFMAKE_VERSION,
		true
	);
}
endif;

if ( ! function_exists( 'ttfmake_customizer_sections_script' ) ) :
/**
 * Enqueue customizer sections script
 *
 * Hooked to 'customize_controls_enqueue_scripts' via ttfmake_customizer_init()
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_sections_script() {
	$path = '/inc/customizer/js/';

	wp_enqueue_script(
		'ttfmake-customizer-sections',
		get_template_directory_uri() . $path . 'customizer-sections' . TTFMAKE_SUFFIX . '.js',
		array( 'customize-controls' ),
		TTFMAKE_VERSION,
		true
	);
}
endif;

if ( ! function_exists( 'ttfmake_customizer_admin_styles' ) ) :
/**
 * Styles for our Customizer sections and controls. Prints in the <head>
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_admin_styles() { ?>
	<style type="text/css">
		.customize-control.customize-control-heading {
			margin-bottom: -2px;
		}
	</style>
<?php }
endif;

if ( ! function_exists( 'ttfmake_add_customizations' ) ) :
/**
 * Make sure the 'ttfmake_css' action only runs once.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_add_customizations() {
	do_action( 'ttfmake_css' );
}
endif;

add_action( 'admin_init', 'ttfmake_add_customizations' );

if ( ! function_exists( 'ttfmake_display_customizations' ) ) :
/**
 * Generates the style tag and CSS needed for the theme options.
 *
 * By using the "ttfmake_css" filter, different components can print CSS in the header. It is organized this way to
 * ensure that there is only one "style" tag and not a proliferation of them.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_display_customizations() {
	do_action( 'ttfmake_css' );

	// Echo the rules
	$css = ttfmake_get_css()->build();
	if ( ! empty( $css ) ) {
		echo "\n<!-- Begin One Custom CSS -->\n<style type=\"text/css\" id=\"tff-one-custom-css\">\n";
		echo $css;
		echo "\n</style>\n<!-- End One Custom CSS -->\n";
	}
}
endif;

add_action( 'wp_head', 'ttfmake_display_customizations', 11 );

if ( ! function_exists( 'ttfmake_ajax_display_customizations' ) ) :
/**
 * Generates the theme option CSS as an Ajax response
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_ajax_display_customizations() {
	// Make sure this is an Ajax request
	if ( ! defined( 'DOING_AJAX' ) || true !== DOING_AJAX ) {
		return;
	}

	// Set the content type
	header( "Content-Type: text/css" );

	// Echo the rules
	echo ttfmake_get_css()->build();

	// End the Ajax response
	die();
}
endif;

add_action( 'wp_ajax_ttfmake-css', 'ttfmake_ajax_display_customizations' );

if ( ! function_exists( 'ttfmake_mce_css' ) ) :
/**
 * Make sure theme option CSS is added to TinyMCE last, to override other styles.
 *
 * @since  1.0.0.
 *
 * @param  string    $stylesheets    List of stylesheets added to TinyMCE.
 * @return string                    Modified list of stylesheets.
 */
function ttfmake_mce_css( $stylesheets ) {
	if ( ttfmake_get_css()->build() ) {
		$stylesheets .= ',' . add_query_arg( 'action', 'ttfmake-css', admin_url( 'admin-ajax.php' ) );
	}

	return $stylesheets;
}
endif;

add_filter( 'mce_css', 'ttfmake_mce_css', 99 );