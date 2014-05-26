<?php

if ( ! class_exists( 'TTFMP_WooCommerce_Shop_Sidebar' ) ) :
/**
 * Adds a shop sidebar option.
 *
 * @since 1.0.0.
 */
class TTFMP_WooCommerce_Shop_Sidebar {
	/**
	 * The one instance of TTFMP_WooCommerce_Shop_Sidebar.
	 *
	 * @since 1.0.0.
	 *
	 * @var   TTFMP_WooCommerce_Shop_Sidebar
	 */
	private static $instance;

	/**
	 * Instantiate or return the one TTFMP_WooCommerce_Shop_Sidebar instance.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMP_WooCommerce_Shop_Sidebar
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 *
	 */
	public function __construct() {
		//
		add_action( 'widgets_init', array( $this, 'register_shop_sidebar' ) );

		//
		add_filter( 'ttfmake_sidebar_left', array( $this, 'display_shop_sidebar' ) );
		add_filter( 'ttfmake_sidebar_right', array( $this, 'display_shop_sidebar' ) );
	}

	/**
	 *
	 */
	public function register_shop_sidebar() {
		register_sidebar( array(
			'id'            => 'sidebar-shop-woocommerce',
			'name'          => __( 'Shop Sidebar', 'make-plus' ),
			'description'   => $this->sidebar_description( 'sidebar-shop-woocommerce' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		) );
	}

	/**
	 * @param  string    $sidebar_id    The id of the sidebar.
	 * @return string
	 */
	private function sidebar_description( $sidebar_id ) {
		$description = '';

		$shop_mod = ttfmake_sanitize_choice( get_theme_mod( 'layout-shop-shop-sidebar', ttfmake_get_default( 'layout-shop-shop-sidebar' ) ), 'layout-shop-shop-sidebar' );
		$shop_choices = ttfmake_get_choices( 'layout-shop-shop-sidebar' );
		$product_mod = ttfmake_sanitize_choice( get_theme_mod( 'layout-product-shop-sidebar', ttfmake_get_default( 'layout-product-shop-sidebar' ) ), 'layout-product-shop-sidebar' );
		$product_choices = ttfmake_get_choices( 'layout-product-shop-sidebar' );

		$locations = '';

		// Shop view
		if ( 'none' !== $shop_mod ) {
			$locations .= sprintf(
				__( 'the %s in the Shop view', 'make-plus' ),
				$shop_choices[ $shop_mod ]
			);
		}

		// Product view
		if ( 'none' !== $product_mod ) {
			if ( '' !== $locations ) {
				$locations .= ' ' . __( 'and', 'make-plus' ) . ' ';
			}

			$locations .= sprintf(
				__( 'the %s in the Product view', 'make-plus' ),
				$product_choices[ $product_mod ]
			);
		}

		// Build the description
		if ( '' === $locations ) {
			$description = __( 'This widget area is currently disabled. Enable it in the "Layout" section of the Theme Customizer.', 'make-plus' );
		} else {
			$description = sprintf(
				__( 'This widget area is currently used in place of %s. Change this in the "Layout" section of the Theme Customizer.', 'make-plus' ),
				esc_html( $locations )
			);
		}

		return esc_html( $description );
	}

	/**
	 * @param  string    $sidebar_id    The ID of the current sidebar.
	 * @return string
	 */
	public function display_shop_sidebar( $sidebar_id ) {
		if ( false === strpos( $sidebar_id, 'sidebar-' ) ) {
			return $sidebar_id;
		}

		$view = ttfmake_get_view();
		if ( ! in_array( $view, array( 'shop', 'product' ) ) ) {
			return $sidebar_id;
		}

		$mod = get_theme_mod( 'layout-' . $view . '-shop-sidebar', ttfmake_get_default( 'layout-' . $view . '-shop-sidebar' ) );
		$location = str_replace( 'sidebar-', '', $sidebar_id );

		if ( 'none' !== $mod && $location === $mod ) {
			$sidebar_id = 'sidebar-shop-woocommerce';
		}

		return $sidebar_id;
	}
}
endif;

if ( ! function_exists( 'ttfmp_get_woocommerce_shop_sidebar' ) ) :
/**
 * Instantiate or return the one TTFMP_WooCommerce_Shop_Sidebar instance.
 *
 * @since  1.0.0.
 *
 * @return TTFMP_WooCommerce_Shop_Sidebar
 */
function ttfmp_get_woocommerce_shop_sidebar() {
	return TTFMP_WooCommerce_Shop_Sidebar::instance();
}
endif;

ttfmp_get_woocommerce_shop_sidebar();