<?php

/**
 * Handle redirection through WordPress 404 Template
 */
class UBP_404_Template {

	var $siteurl;
	var $domain;
	var $remote_path;
	var $local_path;
	var $response;

	function __construct() {
		// Only run for whitelisted paths
		if ( !$this->allow_path() ) { return; }

		$this->stream();
	}

	/**
	 * Stream files from publicly registered IP address through PHP
	 */
	public function stream() {
		require dirname(__FILE__).'/class-get-public-ip.php';

		$ip = new UBP_Get_Public_IP( $this->get_domain() );

		// Send domain name in request headers so vhosts resolve
		$args = array( 'headers' => array( 'Host' => $this->get_domain() ) );
		// Route around local DNS by requesting by IP directly
		$url = 'http://' . $ip . $this->get_remote_path();

		$this->response = wp_remote_get( $url, $args);

		if ( !is_wp_error($this->response) && 200 == $this->response['response']['code'] ) {
			$this->download();
		}
	}

	public function download() {
		if ( !function_exists('WP_Filesystem')) { require ABSPATH.'wp-admin/includes/file.php'; }
		global $wp_filesystem; WP_Filesystem();

		$u = wp_upload_dir();
		$basedir = $u['basedir'];

		$remove = str_replace( get_option( 'siteurl' ), '', $u['baseurl'] );
		$basedir = str_replace( $remove, '', $basedir );
		$abspath = $basedir . $this->get_local_path();
		$dir = dirname( $abspath );

		if ( !is_dir( $dir ) && !wp_mkdir_p( $dir ) ) { 
			$this->display_and_exit( "Please check permissions. Could not create directory $dir" );
		}

		$saved_image = $wp_filesystem->put_contents( $abspath, $this->response['body'], FS_CHMOD_FILE ); // predefined mode settings for WP files

		if ( $saved_image ) {
			wp_redirect( get_site_url( get_current_blog_id(), $this->get_local_path() ) );
			exit;
		}else {
			$this->display_and_exit( "Please check permissions. Could not write image $dir" );
		}

	}

	public function display_and_exit( $message=false ) {
		global $wp_query;
		status_header( 200 );
		$wp_query->is_404 = false;

		// Send debug message in response headers.
		if ( $message ) { header('*Uploads-by-Proxy: ' . $message ); }

		foreach( $this->response['headers'] as $name => $value ){
			header( "$name: $value" );
		}

		echo $this->response['body'];
		exit;
	}

	/**
	 * Only redirect for whitelisted paths
	 */
	public function allow_path() {
		$path = $this->get_remote_path();
		if ( empty( $path ) ) { return false; }

		$allowed_paths = array(
			$this->uploads_basedir(),
		);

		$allowed_paths = apply_filters( 'ubp_allowed_paths', $allowed_paths );

		foreach ( $allowed_paths as $value ){
			if ( false !== @strpos( $path, $value) ) { return true; }
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

	public function get_siteurl() {
		if ( isset( $this->siteurl ) ) {
			return $this->siteurl;
		}

		if ( defined( 'UBP_LIVE_DOMAIN' ) && false !== UBP_LIVE_DOMAIN ) {

			// Legacy support
			// Strip schema, slashes, and whitespace
			$url = str_replace( array( 'http://', 'https://' ), '', UBP_LIVE_DOMAIN );
			$url = 'http://' . $url;

		}else if ( defined( 'UBP_SITEURL' ) && false !== UBP_SITEURL ) {
		
			$url = parse_url( UBP_SITEURL );
			$url = 'http://' . $url['host'] . @$url['path'];

		}else if ( !is_multisite() ) {
			// Nothing set... Get original siteurl from database

			remove_filter( 'option_siteurl', '_config_wp_siteurl' );
			$url = get_option( 'siteurl' );
			add_filter( 'option_siteurl', '_config_wp_siteurl' );

		}

		$this->siteurl = untrailingslashit( $url );

		return $this->siteurl;

	}

	public function get_domain() {
		if( !isset( $this->domain ) ) {
			$this->domain = parse_url( $this->get_siteurl(), PHP_URL_HOST );
		}
		return $this->domain;
	}

	public function get_local_path() {
		if ( isset( $this->local_path ) ) {
			return $this->local_path;
		}

		// If local install is in a subdirectory, modify path to request from WordPress root
		$local_wordpress_path = parse_url( get_site_url(), PHP_URL_PATH );
		$requested_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		if (substr($requested_path, 0, strlen($local_wordpress_path)) == $local_wordpress_path) {
		    $requested_path = substr($requested_path, strlen($local_wordpress_path), strlen($requested_path));
		} 

		$this->local_path = $requested_path;

		return $this->local_path;
	}

	public function get_remote_path() {
		if ( isset( $this->remote_path ) ) {
			return $this->remote_path;
		}

		// If remote install is in a subdirectory, prepend the remote path
		$remote_path = parse_url( $this->get_siteurl(), PHP_URL_PATH );
		if ( !empty( $remote_path ) ) {
			$this->remote_path = $remote_path . $this->get_local_path();
		}else {
			$this->remote_path = $this->get_local_path();
		}

		return $this->remote_path;
	}

}