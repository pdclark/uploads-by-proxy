<?php
/*
Plugin Name: Uploads by Proxy
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

$storm_uploads_by_proxy = new Storm_Uploads_by_Proxy();

class Storm_Uploads_by_Proxy {

	/**
	 * Domain running an apache passthrough proxy, using this .htaccess:
	 *     # Proxy requests from /domain/path to http://domain/path
	 *     <IfModule mod_rewrite.c>
	 *     RewriteEngine On
	 *     RewriteRule ^([^/]*)/(.*)$ http://$1/$2 [P,L]
	 *     </IfModule>
	 */
	var $proxy = 'proxy.brainstormmedia.com';

	var $marker = 'Uploads by Proxy';

	function __construct() {
		register_activation_hook(   __FILE__, array($this, 'save_mod_rewrite_rules') ); 
		register_deactivation_hook( __FILE__, array($this, 'remove_rewrite_rules') ); 

		add_action('template_redirect', array($this, 'template_redirect'));
	}

	function template_redirect(){
		// Only run on 404
		if ( !is_404() ) { return; }

		$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

		// Only run for whitelisted paths
		if ( !$this->allow_path($path) ) { return; }

		$this->redirect( $path );
	}

	public function rewrite_rules() {
		$rules_file = plugin_dir_path(__FILE__) . 'htaccess-rewrite-rules.txt';

		if ( file_exists($rules_file) ) {
			return file_get_contents( $rules_file );
		}else {
			return false;
		}
	}

	public function remove_rewrite_rules() {
		$this->save_mod_rewrite_rules(true);
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

	/**
	 * Updates the htaccess file with the rewrite rules if it is writable.
	 *
	 * Always writes to the file if it exists and is writable to ensure that we
	 * blank out old rules.
	 */
	function save_mod_rewrite_rules( $remove=false ) {
		if ( is_multisite() || !apache_mod_loaded('mod_proxy') || !apache_mod_loaded('mod_rewrite') ) {
			return;
		}

		if ( !function_exists('got_mod_rewrite') ){ include ABSPATH.'/wp-admin/includes/misc.php'; }
		if ( !function_exists('get_home_path')   ){ include ABSPATH.'/wp-admin/includes/file.php'; }

		$home_path = get_home_path();
		$htaccess_file = $home_path.'.htaccess';
		$rules = $this->rewrite_rules();

		// If the file doesn't already exist check for write access to the directory and whether we have some rules.
		// else check for write access to the file.
		if ((!file_exists($htaccess_file) && is_writable($home_path)) || is_writable($htaccess_file)) {
			if ( $rules ) {
				$rules = explode( "\n", $rules );

				if ( $remove ){
					return $this->prepend_with_markers( $htaccess_file, $this->marker, array() );
				}else {
					return $this->prepend_with_markers( $htaccess_file, $this->marker, $rules );
				}

			}
		}

		return false;
	}

	/**
	 * Based on WordPress misc.php::insert_with_markers
	 *
	 * Inserts an array of strings into a file (.htaccess ), placing it between
	 * BEGIN and END markers. Replaces existing marked info. Retains surrounding
	 * data. Creates file if none exists.
	 *
	 * @param unknown_type $filename
	 * @param unknown_type $marker
	 * @param unknown_type $insertion
	 * @return bool True on write success, false on failure.
	 */
	function prepend_with_markers( $filename, $marker, $insertion ) {
		if (!file_exists( $filename ) || is_writeable( $filename ) ) {
			if (!file_exists( $filename ) ) {
				$markerdata = '';
			} else {
				$markerdata = explode( "\n", implode( '', file( $filename ) ) );
			}

			if ( !$f = @fopen( $filename, 'w' ) )
				return false;

			$foundit = false;

			// Check if our code already exists
			if ( $markerdata ) {
				$state = true;
				foreach ( $markerdata as $n => $markerline ) {
					if (strpos($markerline, '# BEGIN ' . $marker) !== false)
						$state = false;
					if (strpos($markerline, '# END ' . $marker) !== false) {
						$state = true;
						$foundit = true;
					}
				}
			}

			// Moved this section before if ( $markerdata ) compared to WordPress version
			if (!$foundit) {
				fwrite( $f, "\n# BEGIN {$marker}\n" );
				foreach ( $insertion as $insertline )
					fwrite( $f, "{$insertline}\n" );
				fwrite( $f, "# END {$marker}\n" );
			}

			if ( $markerdata ) {
				$state = true;
				foreach ( $markerdata as $n => $markerline ) {
					if (strpos($markerline, '# BEGIN ' . $marker) !== false)
						$state = false;
					if ( $state ) {
						if ( $n + 1 < count( $markerdata ) )
							fwrite( $f, "{$markerline}\n" );
						else
							fwrite( $f, "{$markerline}" );
					}
					if (strpos($markerline, '# END ' . $marker) !== false) {
						fwrite( $f, "# BEGIN {$marker}\n" );
						if ( is_array( $insertion ))
							foreach ( $insertion as $insertline )
								fwrite( $f, "{$insertline}\n" );
						fwrite( $f, "# END {$marker}\n" );
						$state = true;
						$foundit = true;
					}
				}
			}
			
			fclose( $f );
			return true;
		} else {
			return false;
		}
	}
}