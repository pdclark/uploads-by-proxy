<?php
/*
Plugin Name: Live Uploads
Plugin URI: http://brainstormmedia.com
Description: Load images from live site if missing in development environment.
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

add_action('template_redirect', create_function('', 'global $storm_live_uploads; $storm_live_uploads = new Storm_Live_Uploads();') );

class Storm_Live_Uploads {

	/**
	 * Domain running an apache passthrough proxy, using this .htaccess:
	 *     # Proxy requests from /domain/path to http://domain/path
	 *     <IfModule mod_rewrite.c>
	 *     RewriteEngine On
	 *     RewriteRule ^([^/]*)/(.*)$ http://$1/$2 [P,L]
	 *     </IfModule>
	 */
	var $proxy = 'proxy.brainstormmedia.com';

	function __construct() {
		// Only run on 404
		if ( !is_404() ) { return; }

		$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

		// Only run for whitelisted paths
		if ( !$this->allow_path($path) ) { return; }

		$this->redirect( $path );
	}

	/**
	 * Redirect to live file through proxy
	 */
	public function redirect( $path ) {
		global $wp_query;
		status_header( 200 );
		$wp_query->is_404 = false;

		$domain = $_SERVER['HTTP_HOST'];

		$url = 'http://' . trailingslashit($this->proxy) . $domain . $path;

		header( "Location: $url", 302 );
		exit;
	}

	/**
	 * Only redirect for whitelisted paths
	 */
	public function allow_path( $path ) {
		if ( empty($path) ) { return false; }

		$allowed_paths = array(
			$this->uploads_basedir(),
		);
		$allowed_paths = apply_filters( 'stlu_allowed_paths', $allowed_paths );

		foreach ( $allowed_paths as $value ){
			if ( false !== strpos($path, $value) ) { return true; }
		}

		return false;
	}

	/**
	 * Return path to uploads folder, relative to WordPress root directory
	 * @var string
	 */
	public function uploads_basedir() {
		$uploads = wp_upload_dir();
		return str_replace( ABSPATH, '', $uploads['basedir'] );
	}

}