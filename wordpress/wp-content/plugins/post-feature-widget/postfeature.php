<?php
/*
Plugin Name: Featured Post Widget
Plugin URI: http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/featured-post-widget
Description: Featured Post Widget is yet another plugin to make your blog a bit more newspaper-like. Just by choosing a post from a dropdown, you can put it in the 'featured' area and display thumbnail, headline, excerpt or all three of them (if available) in the fully customizable widget.
Version: 3.9.2
Author: Waldemar Stoffel
Author URI: http://www.waldemarstoffel.com
License: GPL3
Text Domain: postfeature
*/

/*  Copyright 2010 - 2014  Waldemar Stoffel  (email : stoffel@atelier-fuenf.de)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


/* Stop direct call */

defined('ABSPATH') OR exit;

if (!defined('PF_PATH')) define( 'PF_PATH', plugin_dir_path(__FILE__) );
if (!defined('PF_BASE')) define( 'PF_BASE', plugin_basename(__FILE__) );

# loading the framework
if (!class_exists('A5_Image')) require_once PF_PATH.'class-lib/A5_ImageClass.php';
if (!class_exists('A5_Excerpt')) require_once PF_PATH.'class-lib/A5_ExcerptClass.php';
if (!class_exists('A5_FormField')) require_once PF_PATH.'class-lib/A5_FormFieldClass.php';
if (!class_exists('A5_OptionPage')) require_once PF_PATH.'class-lib/A5_OptionPageClass.php';
if (!class_exists('A5_DynamicFiles')) require_once PF_PATH.'class-lib/A5_DynamicFileClass.php';

#loading plugin specific classes
if (!class_exists('FP_Admin')) require_once PF_PATH.'class-lib/FP_AdminClass.php';
if (!class_exists('FP_DynamicCSS')) require_once PF_PATH.'class-lib/FP_DynamicCSSClass.php';
if (!class_exists('Featured_Post_Widget')) require_once PF_PATH.'class-lib/FP_WidgetClass.php';

class PostFeaturePlugin {
	
	const language_file = 'postfeature';

	static $options;
	
	function __construct() {
		
		load_plugin_textdomain(self::language_file, false , basename(dirname(__FILE__)).'/languages');
		
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		
		add_filter('plugin_row_meta', array(&$this, 'register_links'), 10, 2);	
		add_filter( 'plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );
				
		register_activation_hook(  __FILE__, array(&$this, '_install') );
		register_deactivation_hook(  __FILE__, array(&$this, '_uninstall') );
		
		self::$options = get_option('postfeature_cache');
		
		if (false != self::$options) : 
		
			delete_option('postfeature_cache');
			
			$this->_install();
			
		endif;
		
		self::$options = get_option('pf_options');
		
		$FP_DynamicCSS = new FP_DynamicCSS;
		$FP_Admin = new FP_Admin;
		
	}
	
	/* attach JavaScript file for textarea reszing */
	
	function enqueue_scripts($hook) {
		
		if ($hook != 'settings_page_featured-post-settings' && $hook != 'widgets.php' && $hook != 'post.php') return;
		
		wp_register_script('ta-expander-script', plugins_url('ta-expander.js', __FILE__), array('jquery'), '2.0', true);
		wp_enqueue_script('ta-expander-script');
	
	
	}
	
	/* Additional links on the plugin page */
	
	function register_links($links, $file) {
		
		if ($file == PF_BASE) :
		
			$links[] = '<a href="http://wordpress.org/extend/plugins/post-feature-widget/faq/" target="_blank">'.__('FAQ', self::language_file).'</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=D8AVGNDYYUNA2" target="_blank">'.__('Donate', self::language_file).'</a>';
		
		endif;
		
		return $links;
	
	}
	
	function plugin_action_links( $links, $file ) {
		
		if ($file == PF_BASE) array_unshift($links, '<a href="'.admin_url( 'options-general.php?page=featured-post-settings' ).'">'.__('Settings', self::language_file).'</a>');
	
		return $links;
	
	}
	
	// Creating default options on activation
	
	function _install() {
		
		$default = array(
			'cache' => array(),
			'inline' => false
		);
	
		add_option('pf_options', $default);
	
	}
	
	// Cleaning on deactivation
	
	function _uninstall() {
	
		delete_option('pf_options');
	
	}

} // class

$PostFeaturePlugin = new PostFeaturePlugin;

?>