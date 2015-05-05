<?php
/*
Plugin Name: Uploads by Proxy
Plugin URI: http://github.com/pdclark/uploads-by-proxy
Author: Paul Clark
Author URI: http://pdclark.com
Description: Load images from production site if missing in development environment. Activate by using either <code>define('WP_SITEURL', 'http://development-domain.com');</code> or <code>define('UBP_SITEURL', 'http://live-domain.com/wordpress');</code> in wp-config.php.
Version: 1.1.2
*/

/**
 * Check that we're on a development server.
 * This tests if we're serving from and to localhost (127.0.0.1),
 * which should catch most common dev environments like MAMP, WAMP, XAMPP, etc.
 *
 * If you're hosting from a staging environment, or some weird situation where
 * this test doesn't return true, redefine it in wp-config.php:
 *     define('UBP_IS_LOCAL', true);
 *
 * 	   WARNING!!
 *     Do not set this to "true" on a live site!
 *     Doing so will cause 404 pages for wp-content/uploads to go into
 *     an infinite loop until Apache kills the PHP process.
 */
if ( !defined('UBP_IS_LOCAL') ) {
	define('UBP_IS_LOCAL', (
		( '127.0.0.1' == $_SERVER['SERVER_ADDR'] && '127.0.0.1' == $_SERVER['REMOTE_ADDR'] ) // IPv4
		|| ( '::1' == $_SERVER['SERVER_ADDR'] && '::1' == $_SERVER['REMOTE_ADDR'] ) // IPv6
	) );
}

/**
 * Used for deactivating the plugin here or in class-helpers.php if requirements aren't met.
 */
define( 'UBP_PLUGIN_FILE', __FILE__ );

/**
 * Check for PHP 5.2 or higher before activating.
 */
if ( version_compare(PHP_VERSION, '5.2', '<') ) {
	if ( is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX) ) {
		require_once ABSPATH.'/wp-admin/includes/plugin.php';
		deactivate_plugins( UBP_PLUGIN_FILE );
		wp_die( sprintf( __( 'Uploads by Proxy requires PHP 5.2 or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself. For information on upgrading, %ssee this article%s.', 'uploads-by-proxy'), '<a href="http://codex.wordpress.org/Switching_to_PHP5" target="_blank">', '</a>') );
	} else {
		return;
	}
}

require_once dirname( __FILE__ ).'/class-helpers.php';

// Only initialize if we're on a development server
if ( UBP_IS_LOCAL ) {
	add_action( 'admin_init', 'UBP_Helpers::requirements_check' );
	add_filter( '404_template', 'UBP_Helpers::init_404_template' );
}
