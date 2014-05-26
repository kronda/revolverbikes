<?php

if ( ! function_exists( 'ttfmake_customizer_layout_product' ) ) :
/**
 * Configure settings and controls for the Layout: Product section.
 *
 * @since  1.0.0.
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_layout_product( $wp_customize, $section ) {
	$priority       = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Section info
	$setting_id = $setting_prefix . '-section-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => __( 'The Product view includes single products and items attached to those products, such as images.', 'make-plus' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Header, Footer, Sidebars heading
	$setting_id = $setting_prefix . '-heading-sidebars';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'  => $section,
				'type'     => 'heading',
				'label'    => __( 'Header, Footer, Sidebars', 'make-plus' ),
				'priority' => $priority->add()
			)
		)
	);

	// Hide site header
	$setting_id = $setting_prefix . '-hide-header';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Hide site header', 'make-plus' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Hide site footer
	$setting_id = $setting_prefix . '-hide-footer';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Hide site footer', 'make-plus' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Left sidebar
	$setting_id = $setting_prefix . '-sidebar-left';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Show left sidebar', 'make-plus' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Right sidebar
	$setting_id = $setting_prefix . '-sidebar-right';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Show right sidebar', 'make-plus' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Shop sidebar
	$setting_id = $setting_prefix . '-shop-sidebar';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'ttfmake_sanitize_choice',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Shop Sidebar Location', 'make-plus' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);
}
endif;