<?php
/*
Plugin Name: Uploads by Proxy
Plugin URI: http://brainstormmedia.com
Description: Load images from live site if missing in development or staging environment. Meant to be used in a local development environment only. Override this with <code>define('UBP_IS_LOCAL', true);</code> in wp-config.php.
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
 *	Example filter: Add custom hook for getting public IP.
 *  In this case, ip.php would just run <?php echo gethostbyname( $_GET['domain'] );
 *
 *    add_filter('ubp_ip_url', 'ubp_ip_url', 10, 2);
 *    function ubp_ip_url( $url, $domain ) {
 *    	return 'http://website.com/ip.php?domain='.$domain;
 *    }
 */

/**
 * Load live images from a domain differing from the current site's
 * For example, we're on domain.dev or stage.domain.com but want to load from domain.com
 *
 * Override by adding this to wp-config.php, then disabling and re-enabling the plugin.
 *     define('UBP_LIVE_DOMAIN', 'domain.com');
 */
if ( !defined('UBP_LIVE_DOMAIN') ) define('UBP_LIVE_DOMAIN', $_SERVER['HTTP_HOST'] ); // e.g., domain.com

/**
 * Handle redirection with Apache rewrite rules
 * Requires UBP_PROXY point to a domain that had mod_rewrite and mod_proxy enabled.
 * Put the contents of htaccess-remote-proxy.txt in the remote root directory as .htaccess
 * 
 * Override by adding this to wp-config.php, then disabling and re-enabling the plugin.
 *     define('UBP_PROXY', 'proxy.domain.com' );
 * 
 * Remote Proxy:   On
 * Local Rewrites: On
 * Speed:          Very Fast
 */
if ( !defined('UBP_PROXY') ) define('UBP_PROXY', false); // e.g., proxy.domain.com

/**
 * Disable .htaccess rewrite rules
 *
 * Override by adding this to wp-config.php, then disabling and re-enabling the plugin.
 *     define('UBP_MOD_REWRITE', true);
 */
if ( !defined('UBP_MOD_REWRITE') ) define('UBP_MOD_REWRITE', false);

if ( !defined('UBP_IS_LOCAL') ) define('UBP_IS_LOCAL', ( '127.0.0.1' == $_SERVER['SERVER_ADDR'] && '127.0.0.1' == $_SERVER['REMOTE_ADDR'] ) );

if ( version_compare(PHP_VERSION, '5.2', '<') ) {
	if ( is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX) ) {
		require_once ABSPATH.'/wp-admin/includes/plugin.php';
		deactivate_plugins( __FILE__ );
		wp_die( sprintf( __('Uploads by Proxy requires PHP 5.2 or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself. For information on upgrading, %s$1see this article%s$2.', 'uploads-by-proxy'), '<a href="http://codex.wordpress.org/Switching_to_PHP5">', '</a>') );
	} else {
		return;
	}
}else {

	require_once dirname( __FILE__ ).'/class-uploads-by-proxy.php';
	$storm_uploads_by_proxy = new Storm_Uploads_by_Proxy();

}