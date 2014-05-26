<?php

if ( ! class_exists( 'TTFMP_PerPage_Options' ) ) :
/**
 * Post meta-related functionality.
 *
 * @since 1.0.0.
 */
class TTFMP_PerPage_Options {
	/**
	 * The one instance of TTFMP_PerPage_Options.
	 *
	 * @since 1.0.0.
	 *
	 * @var   TTFMP_PerPage_Options
	 */
	private static $instance;

	/**
	 * Instantiate or return the one TTFMP_PerPage_Options instance.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMP_PerPage_Options
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
	 * @return TTFMP_PerPage_Options
	 */
	public function __construct() {}

	/**
	 * Get the relevant setting keys for the specified view
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $view    The view.
	 * @return array              The keys.
	 */
	public function get_keys( $view ) {
		$common = array(
			'hide-header',
			'hide-footer',
			'sidebar-left',
			'sidebar-right',
			'post-author',
		);

		$post = array(
			'featured-images',
			'post-date',
			'show-categories',
			'show-tags',
		);

		$page = array(
			'featured-images',
			'post-date',
			'hide-title',
		);

		$product = array(
			'shop-sidebar',
		);

		if ( 'product' === $view || 'shop' === $view ) {
			$keys = array_merge( $common, $product );
		} else if ( 'page' === $view ) {
			$keys = array_merge( $common, $page );
		} else {
			$keys = array_merge( $common, $post );
		}

		return apply_filters( 'ttfmp_perpage_keys', $keys );
	}

	/**
	 * Get the global layout settings for the given post type.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $view    The view.
	 * @return array              The layout settings.
	 */
	public function get_global_settings( $view ) {
		// Get setting keys for specified view
		$keys = $this->get_keys( $view );

		$settings = array();

		foreach ( $keys as $key ) {
			$id = 'layout-' . $view . '-' . $key;
			$settings[$key] = get_theme_mod( $id, ttfmake_get_default( $id ) );
		}

		return $settings;
	}

	/**
	 * Get the "settings" post meta and fill in gaps with default values.
	 *
	 * @since  1.0.0.
	 *
	 * @param  object    $post    The current post.
	 * @param  string    $view    The current view type.
	 * @return array              The settings.
	 */
	public function get_post_settings( $post, $view = '' ) {
		// Get the current view if one isn't specified
		if ( ! $view ) {
			$view = ttfmp_get_perpage()->get_view( $post );
		}

		// Get defaults
		$defaults = $this->get_global_settings( $view );

		// Get post meta
		$meta_key = ttfmp_get_perpage()->prefix . 'settings';
		$settings = get_post_meta( $post->ID, $meta_key, true );

		// Parse and return
		if ( empty( $settings ) ) {
			return $defaults;
		} else {
			return wp_parse_args( (array) $settings, $defaults );
		}
	}

	/**
	 * Get the "overrides" post meta and fill in gaps with default values.
	 *
	 * @since  1.0.0.
	 *
	 * @param  object    $post    The current post.
	 * @param  string    $view    The current view type.
	 * @return array              The overrides.
	 */
	public function get_post_overrides( $post, $view = '' ) {
		// Get the current view if one isn't specified
		if ( ! $view ) {
			$view = ttfmp_get_perpage()->get_view( $post );
		}

		// Get defaults
		$keys = $this->get_keys( $view );
		$defaults = array_fill_keys( $keys, 0 );

		// Get post meta
		$meta_key = ttfmp_get_perpage()->prefix . 'overrides';
		$overrides = get_post_meta( $post->ID, $meta_key, true );

		// Parse and return
		if ( empty( $overrides ) ) {
			return $defaults;
		} else {
			return wp_parse_args( (array) $overrides, $defaults );
		}
	}

	/**
	 * Sanitize a value for storage in post meta.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $key      The array key
	 * @param  mixed     $value    The value to sanitize
	 * @param  string    $view     The view type
	 * @return mixed               The sanitized value
	 */
	public function sanitize_post_meta( $key, $value, $view = '' ) {
		$functions = array(
			'hide-header' => 'absint',
			'hide-footer' => 'absint',
			'sidebar-left' => 'absint',
			'sidebar-right' => 'absint',
			'post-author' => 'ttfmake_sanitize_choice',
			'featured-images' => 'ttfmake_sanitize_choice',
			'post-date' => 'ttfmake_sanitize_choice',
			'show-categories' => 'absint',
			'show-tags' => 'absint',
			'hide-title' => 'absint',
			'shop-sidebar' => 'ttfmake_sanitize_choice',
		);

		if ( ! function_exists( $functions[$key] ) ) {
			return false;
		} else {
			$args = array( $value );
			if ( 'ttfmake_sanitize_choice' === $functions[$key] ) {
				$args[] = 'layout-' . $view . '-' . $key;
			}
			return call_user_func_array( $functions[$key], $args );
		}
	}
}
endif;

if ( ! function_exists( 'ttfmp_get_perpage_options' ) ) :
/**
 * Instantiate or return the one TTFMP_PerPage_Options instance.
 *
 * @since  1.0.0.
 *
 * @return TTFMP_PerPage_Options
 */
function ttfmp_get_perpage_options() {
	return TTFMP_PerPage_Options::instance();
}
endif;
