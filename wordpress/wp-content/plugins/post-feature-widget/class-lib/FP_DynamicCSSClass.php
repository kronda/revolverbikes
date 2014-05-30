<?php

/**
 *
 * Class FP Dynamic CSS
 *
 * Extending A5 Dynamic Files
 *
 * Presses the dynamical CSS of the Featured Post Widget into a virtual style sheet
 *
 */

class FP_DynamicCSS extends A5_DynamicFiles {
	
	private static $options;
	
	function __construct() {
		
		self::$options =  get_option('pf_options');
		
		if (!isset(self::$options['inline'])) self::$options['inline'] = false;
		
		if (!isset(self::$options['compress'])) self::$options['compress'] = false;
		
		parent::A5_DynamicFiles('wp', 'css', false, self::$options['inline']);
		
		$eol = (self::$options['compress']) ? '' : "\r\n";
		$tab = (self::$options['compress']) ? '' : "\t";
		
		$css_selector = '.widget_featured_post_widget[id^="featured_post_widget"]';
		
		parent::$wp_styles .= (!self::$options['compress']) ? $eol.'/* CSS portion of the Featured Post Widget */'.$eol.$eol : '';
		
		$style = '-moz-hyphens: auto;'.$eol.$tab.'-o-hyphens: auto;'.$eol.$tab.'-webkit-hyphens: auto;'.$eol.$tab.'-ms-hyphens: auto;'.$eol.$tab.'hyphens: auto;';
		
		if (!empty(self::$options['css'])) $style.=$eol.$tab.str_replace('; ', ';'.$eol.$tab, str_replace(array("\r\n", "\n", "\r"), ' ', self::$options['css']));
		
		parent::$wp_styles.='div'.$css_selector.','.$eol.'li'.$css_selector.','.$eol.'aside'.$css_selector.' {'.$eol.$tab.$style.$eol.'}'.$eol;
		
		parent::$wp_styles.='div'.$css_selector.' img,'.$eol.'li'.$css_selector.' img,'.$eol.'aside'.$css_selector.' img {'.$eol.$tab.'height: auto;'.$eol.$tab.'max-width: 100%;'.$eol.'}'.$eol;

	}
	
} // FP_Dynamic CSS

?>