<?php

if ( ! class_exists( 'TTFMP_WooCommerce' ) ) :
/**
 * Bootstrap the enhanced WooCommerce functionality.
 *
 * @since 1.0.0.
 */
class TTFMP_WooCommerce {
	/**
	 * Name of the component.
	 *
	 * @since 1.0.0.
	 *
	 * @var   string    The name of the component.
	 */
	var $component_slug = 'woocommerce';

	/**
	 * Path to the component directory (e.g., /var/www/mysite/wp-content/plugins/make-plus/components/my-component).
	 *
	 * @since 1.0.0.
	 *
	 * @var   string    Path to the component directory
	 */
	var $component_root = '';

	/**
	 * File path to the plugin main file (e.g., /var/www/mysite/wp-content/plugins/make-plus/components/my-component/my-component.php).
	 *
	 * @since 1.0.0.
	 *
	 * @var   string    Path to the plugin's main file.
	 */
	var $file_path = '';

	/**
	 * The URI base for the plugin (e.g., http://domain.com/wp-content/plugins/make-plus/my-component).
	 *
	 * @since 1.0.0.
	 *
	 * @var   string    The URI base for the plugin.
	 */
	var $url_base = '';

	/**
	 * The one instance of TTFMP_WooCommerce.
	 *
	 * @since 1.0.0.
	 *
	 * @var   TTFMP_WooCommerce
	 */
	private static $instance;

	/**
	 * Instantiate or return the one TTFMP_WooCommerce instance.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMP_WooCommerce
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Bootstrap the module
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMP_WooCommerce
	 */
	public function __construct() {
		// Set the main paths for the component
		$this->component_root = ttfmp_get_app()->component_base . '/' . $this->component_slug;
		$this->file_path      = $this->component_root . '/' . basename( __FILE__ );
		$this->url_base       = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Initialize the components of the module
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function init() {
		// Include needed files
		require_once $this->component_root . '/class-shop-sidebar.php';
		require_once $this->component_root . '/class-section-definitions.php';
		require_once $this->component_root . '/class-shortcode.php';
		require_once $this->component_root . '/color.php';

		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Modify the WooCommerce General Settings page
		add_action( 'woocommerce_settings_general', array( $this, 'modify_wc_settings' ) );

		// Filter the frontend color settings
		add_filter( 'pre_option_woocommerce_frontend_css_colors', array( $this, 'frontend_css_colors' ) );

		// Add new settings and controls
		add_action( 'customize_register', array( $this, 'color_highlight' ), 20 );

		// Use a preview version of the WooCommerce stylesheet while in the Theme Customizer
		add_action( 'wp', array( $this, 'compile_preview_styles' ) );

		// Re-compile the WooCommerce CSS file when settings are saved
		add_action( 'customize_save_after', array( $this, 'save_frontend_styles' ) );

		// Customizer filters
		add_filter( 'ttfmake_customizer_sections', array( $this, 'customizer_sections' ) );
		add_filter( 'ttfmake_setting_defaults', array( $this, 'setting_defaults' ) );
		add_filter( 'ttfmake_setting_choices', array( $this, 'setting_choices' ), 10, 2 );
		add_filter( 'ttfmake_get_view', array( $this, 'get_view' ), 10, 2 );
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Styles
		wp_enqueue_style(
			'ttfmp-woocommerce',
			trailingslashit( $this->url_base ) . 'css/woocommerce.css',
			array( 'woocommerce-general', 'woocommerce-smallscreen', 'woocommerce-layout' ),
			ttfmp_get_app()->version
		);
	}

	/**
	 * Replace the color pickers in the Frontend styles section of the UI with a note
	 * directing users to the Customizer.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function modify_wc_settings() {
		// Determine the callback to remove
		$callback = $this->has_method_filter( 'woocommerce_admin_field_frontend_styles', 'WC_Settings_General', 'frontend_styles_setting' );

		if ( false !== $callback ) {
			// Replace the Frontend styles options in WooCommerce settings with
			// blurb about settings in the Customizer
			remove_action( 'woocommerce_admin_field_frontend_styles', $callback );
			add_action( 'woocommerce_admin_field_frontend_styles', array( $this, 'frontend_styles_setting' ) );
		}
	}

	/**
	 * Add Frontend styles message.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function frontend_styles_setting() {
		?>
		<tr valign="top" class="woocommerce_frontend_css_colors">
			<th scope="row" class="titledesc">
				<?php _e( 'Frontend Styles', 'woocommerce' ); ?>
			</th>
			<td class="forminp">
				<span class="description">
			<?php // File writability check
			$base_file = WC()->plugin_path() . '/assets/css/woocommerce-base.less';
			$css_file  = WC()->plugin_path() . '/assets/css/woocommerce.css';
			if ( is_writable( $base_file ) && is_writable( $css_file ) ) {
				// Get the URL
				$url = admin_url( 'customize.php' );
				$shop = get_option( 'woocommerce_shop_page_id' );
				if ( $shop ) {
					$url = add_query_arg( 'url', urlencode( get_permalink( $shop ) ), $url );
				}
				// Add the message
				printf(
					__( 'These styles can be customized in the Colors section of the %s.', 'make-plus' ),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( $url ),
						__( 'Theme Customizer', 'make-plus' )
					)
				);
			} else {
				echo __( 'To edit colours <code>woocommerce/assets/css/woocommerce-base.less</code> and <code>woocommerce.css</code> need to be writable. See <a href="http://codex.wordpress.org/Changing_File_Permissions">the Codex</a> for more information.', 'woocommerce' );
			}
			?>
				</span>
			</td>
		</tr>
	<?php
	}

	/**
	 * Override the WooCommerce frontend color options with the Make color settings
	 *
	 * @since  1.0.0.
	 *
	 * @param  bool     $colors    Unused
	 * @return array               The Make color settings array
	 */
	public function frontend_css_colors( $colors ) {
		$colors = array(
			'primary' => get_theme_mod( 'color-primary', ttfmake_get_default( 'color-primary' ) ),
			'secondary' => get_theme_mod( 'color-secondary', ttfmake_get_default( 'color-secondary' ) ),
			'highlight' => get_theme_mod( 'color-woocommerce-highlight', ttfmake_get_default( 'color-woocommerce-highlight' ) ),
			'content_bg' => get_theme_mod( 'main-background-color', ttfmake_get_default( 'main-background-color' ) ),
			'subtext' => get_theme_mod( 'color-detail', ttfmake_get_default( 'color-detail' ) ),
		);

		return $colors;
	}

	/**
	 * Check if the currently loading instance is in the Preview pane
	 *
	 * @since  1.0.0.
	 *
	 * @return bool    True if it's in the Preview pane
	 */
	public function is_preview() {
		global $wp_customize;
		return ( isset( $wp_customize ) && $wp_customize->is_preview() );
	}

	/**
	 * Swap the normal woocommerce CSS file with the preview file in the style queue
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function preview_frontend_styles() {
		if ( ! $this->is_preview() ) {
			return;
		}

		wp_dequeue_style( 'woocommerce-general' );
		wp_deregister_style( 'woocommerce-general' );
		wp_enqueue_style(
			'woocommerce-general',
			WC()->plugin_url() . '/assets/css/ttfmp-woocommerce-preview.css',
			array(),
			time()
		);
	}

	/**
	 * Build a preview version of the woocommerce CSS file
	 *
	 * Based on woocommerce_compile_less_styles() in version 2.1.9 of WooCommerce
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function compile_preview_styles() {
		if ( ! $this->is_preview() ) {
			return;
		}

		$colors    = array_map( 'esc_attr', (array) get_option( 'woocommerce_frontend_css_colors' ) );
		$base_file = WC()->plugin_path() . '/assets/css/woocommerce-base.less';
		$less_file = WC()->plugin_path() . '/assets/css/woocommerce.less';
		$css_file  = WC()->plugin_path() . '/assets/css/ttfmp-woocommerce-preview.css';

		if ( ! file_exists( $css_file ) ) {
			$new_file = file_put_contents( $css_file, '' );
			if ( false === $new_file ) {
				return;
			}
		}

		if ( is_writable( $base_file ) && is_writable( $css_file ) ) {
			if ( ! class_exists( 'lessc' ) ) {
				include_once( WC()->plugin_path() . '/includes/libraries/class-lessc.php' );
			}
			if ( ! class_exists( 'cssmin' ) ) {
				include_once( WC()->plugin_path() . '/includes/libraries/class-cssmin.php' );
			}

			try {
				// Write new color to base file
				$color_rules = "
@primary:       " . $colors['primary'] . ";
@primarytext:   " . wc_light_or_dark( $colors['primary'], 'desaturate(darken(@primary,50%),18%)', 'desaturate(lighten(@primary,50%),18%)' ) . ";

@secondary:     " . $colors['secondary'] . ";
@secondarytext: " . wc_light_or_dark( $colors['secondary'], 'desaturate(darken(@secondary,60%),18%)', 'desaturate(lighten(@secondary,60%),18%)' ) . ";

@highlight:     " . $colors['highlight'] . ";
@highlightext:  " . wc_light_or_dark( $colors['highlight'], 'desaturate(darken(@highlight,60%),18%)', 'desaturate(lighten(@highlight,60%),18%)' ) . ";

@contentbg:     " . $colors['content_bg'] . ";

@subtext:       " . $colors['subtext'] . ";
            ";

				// Save the original base for later
				$original_base = file_get_contents( $base_file, null, null, null, 1024 );

				if ( trim( $color_rules ) != trim( $original_base ) ) {
					file_put_contents( $base_file, $color_rules );

					$less         = new lessc;
					$compiled_css = $less->compileFile( $less_file );
					$compiled_css = CssMin::minify( $compiled_css );

					if ( $compiled_css ) {
						file_put_contents( $css_file, $compiled_css );
					}
				}

				// Swap the woocommerce.css file with the new preview file in the style queue
				add_action( 'wp_enqueue_scripts', array( $this, 'preview_frontend_styles' ), 20 );

				// Reset the base
				file_put_contents( $base_file, $original_base );
			} catch ( exception $ex ) {
				wp_die( __( 'Could not compile woocommerce.less:', 'woocommerce' ) . ' ' . $ex->getMessage() );
			}
		}
	}

	/**
	 * Re-compile the WooCommerce stylesheet when color changes are saved
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function save_frontend_styles() {
		// Load the LESS compile function
		if ( class_exists( 'WC' ) && ! function_exists( 'woocommerce_compile_less_styles' ) ) {
			// Include the file with the compile function
			$file = WC()->plugin_path() . '/includes/admin/wc-admin-functions.php';
			if ( file_exists( $file ) ) {
				include_once( $file );
			}
		}

		// If the function was successfully loaded, run it
		if ( function_exists( 'woocommerce_compile_less_styles' ) ) {
			woocommerce_compile_less_styles();
		}
	}

	/**
	 * Filter to add new Customizer sections
	 *
	 * This function takes the main array of Customizer sections and attempts to insert
	 * new ones right after the layout-page section.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $sections    The array of sections to add to the Customizer.
	 * @return array                 The modified array of sections.
	 */
	public function customizer_sections( $sections ) {
		$new_sections = array(
			'layout-shop'    => array( 'title' => __( 'Layout: Shop', 'make-plus' ), 'path' => $this->component_root ),
			'layout-product' => array( 'title' => __( 'Layout: Product', 'make-plus' ), 'path' => $this->component_root ),
		);

		// Get the position of the layout-page section in the array
		$keys = array_keys( $sections );
		$positions = array_flip( $keys );
		$layout_page = absint( $positions[ 'layout-page' ] );

		// Slice the array
		$front = array_slice( $sections, 0, $layout_page + 1 );
		$back  = array_slice( $sections, $layout_page + 1 );

		// Combine and return
		return array_merge( $front, $new_sections, $back );
	}

	/**
	 * Filter to add new Customizer setting defaults
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $defaults    The array of Customizer option defaults.
	 * @return array
	 */
	public function setting_defaults( $defaults ) {
		$new_defaults = array(
			// Colors
			'color-woocommerce-highlight'  => '#289a00',

			// Layout - Shop
			'layout-shop-hide-header'      => 0,
			'layout-shop-hide-footer'      => 0,
			'layout-shop-sidebar-left'     => 0,
			'layout-shop-sidebar-right'    => 1,
			'layout-shop-shop-sidebar'     => 'right',
			'layout-shop-post-author'      => 'none',

			// Layout - Product
			'layout-product-hide-header'   => 0,
			'layout-product-hide-footer'   => 0,
			'layout-product-sidebar-left'  => 0,
			'layout-product-sidebar-right' => 1,
			'layout-product-shop-sidebar'  => 'right',
			'layout-product-post-author'   => 'none',
		);

		return array_merge( $defaults, $new_defaults );
	}

	/**
	 * Filter to add new Customizer setting choices
	 *
	 * @since  1.0.0.
	 *
	 * @param  array     $choices    The current choices.
	 * @param  string    $setting    The current setting.
	 * @return array
	 */
	public function setting_choices( $choices, $setting ) {
		if ( count( $choices ) > 1 ) {
			return $choices;
		}

		switch ( $setting ) {
			case 'layout-shop-shop-sidebar' :
			case 'layout-product-shop-sidebar' :
				$choices = array(
					'left'  => __( 'Left Sidebar', 'make-plus' ),
					'right' => __( 'Right Sidebar', 'make-plus' ),
					'none'  => __( 'Neither', 'make-plus' ),
				);
				break;
		}

		return $choices;
	}

	/**
	 * Add a Highlight Color control to the Colors section of the Customizer
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function color_highlight() {
		global $wp_customize;

		$color_detail = $wp_customize->get_control( 'ttfmake_color-detail' );

		$priority       = new TTFMAKE_Prioritizer( $color_detail->priority + 1, 1 );
		$section        = 'ttfmake_color';
		$control_prefix = 'ttfmp_';
		$setting_prefix = 'color';

		// Highlight Color
		$setting_id = $setting_prefix . '-woocommerce-highlight';
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => ttfmake_get_default( $setting_id ),
				'type'              => 'theme_mod',
				'sanitize_callback' => 'maybe_hash_hex_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$control_prefix . $setting_id,
				array(
					'settings' => $setting_id,
					'section'  => $section,
					'label'    => __( 'Highlight Color', 'make-plus' ),
					'priority' => $priority->add()
				)
			)
		);
	}

	/**
	 * Utility function to determine if an action/filter hook has a particular class method added to it.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string         $tag       The action/filter hook tag.
	 * @param  string         $class     The class.
	 * @param  string         $method    The class method.
	 * @return bool|string               The encoded class/method id attached to the hook.
	 */
	public function has_method_filter( $tag, $class, $method ) {
		global $wp_filter;
		$callback = false;

		if ( isset( $wp_filter[$tag] ) ) {
			foreach ( $wp_filter[$tag] as $priority ) {
				foreach ( $priority as $cb => $action ) {
					if ( is_array( $action['function'] ) && $class === get_class( $action['function'][0] ) && $method === $action['function'][1] ) {
						$callback = $cb;
						break;
					}
				}
				if ( false !== $callback ) {
					break;
				}
			}
		}

		return $callback;
	}

	/**
	 * Filter to identify views related to WooCommerce
	 *
	 * This assumes two WooCommerce views: a "Shop" view that includes product archives
	 * and other shop utility pages such as Checkout, and a "Product" view which is just
	 * individual products.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $view                The current view.
	 * @param  string    $parent_post_type    The post type of the parent of the current post.
	 * @return string
	 */
	public function get_view( $view, $parent_post_type ) {
		// Product attachments
		if ( is_attachment() && 'product' === $parent_post_type ) {
			$view = 'product';
		}
		// Shop pages
		else if ( is_shop() || is_product_category() || is_product_tag() || is_cart() || is_checkout() ) {
			$view = 'shop';
		}
		// Single products
		else if ( is_product() ) {
			$view = 'product';
		}

		return $view;
	}
}
endif;

if ( ! function_exists( 'ttfmp_get_woocommerce' ) ) :
/**
 * Instantiate or return the one TTFMP_WooCommerce instance.
 *
 * @since  1.0.0.
 *
 * @return TTFMP_WooCommerce
 */
function ttfmp_get_woocommerce() {
	return TTFMP_WooCommerce::instance();
}
endif;

ttfmp_get_woocommerce()->init();
