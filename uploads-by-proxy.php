<?php
/*
Plugin Name: Uploads by Proxy
Plugin URI: http://brainstormmedia.com
Description: Load images from live site if missing in development or staging environment. Only runs in a local development environment by default. Force the plugin to run with <code>define('UBP_IS_LOCAL', true);</code> in wp-config.php. If live domain is different than development domain, set the live domain with <code>define('UBP_LIVE_DOMAIN', 'live-domain.com');</code> in wp-config.php.
Version: 1.0
Author: Brainstorm Media
Author URI: http://brainstormmedia.com
*/

/**
 * Copyright (c) 2012 Brainstorm Media. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/**
 * Load live images from a domain differing from the current site's
 * For example, we're on domain.dev or stage.domain.com but want to load from domain.com
 *
 * Override by adding this to wp-config.php, then disabling and re-enabling the plugin.
 *     define('UBP_LIVE_DOMAIN', 'domain.com');
 */
if ( !defined('UBP_LIVE_DOMAIN') ) define('UBP_LIVE_DOMAIN', $_SERVER['HTTP_HOST'] ); // e.g., domain.com

if ( !defined('UBP_IS_LOCAL') ) define('UBP_IS_LOCAL', ( '127.0.0.1' == $_SERVER['SERVER_ADDR'] && '127.0.0.1' == $_SERVER['REMOTE_ADDR'] ) );

/**
 * Check for PHP 5.2 or higher before activating.
 */
if ( version_compare(PHP_VERSION, '5.2', '<') ) {
	if ( is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX) ) {
		require_once ABSPATH.'/wp-admin/includes/plugin.php';
		deactivate_plugins( __FILE__ );
		wp_die( sprintf( __( '%s requires PHP 5.2 or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself. For information on upgrading, %ssee this article%s.', 'uploads-by-proxy'), ubp_plugin_name(), '<a href="http://codex.wordpress.org/Switching_to_PHP5" target="_blank">', '</a>') );
	} else {
		return;
	}
}else {

	// Only initialize if we're on a development server and have a 404
	if ( UBP_IS_LOCAL ) {
		add_filter( '404_template', 'ubp_frontend_init' );
	}

}

/**
 * Used to load required files on the 404_template hook, instead of immediately.
 * Method from Yoast's WordPress SEO
 */
function ubp_frontend_init( $template ) {
	global $UBP_Frontend;
	require_once dirname( __FILE__ ).'/class-frontend.php';
	$UBP_Frontend = new UBP_Frontend();
	return $template;
}

/**
 * Return the plugin name from the plugin header.
 */
function ubp_plugin_name() {
	$plugin_data = get_plugin_data( __FILE__, false );
	return $plugin_data['Name'];
}