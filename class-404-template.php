<?php

/**
 * Handle redirection through WordPress 404 Template
 */
class UBP_404_Template {

	var $domain;
	var $response;

	function __construct() {
		$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

		// Only run for whitelisted paths
		if ( !$this->allow_path($path) ) { return; }

		$this->stream( $path );
	}

	/**
	 * Stream files from publicly registered IP address through PHP
	 */
	public function stream( $path ) {
		require dirname(__FILE__).'/class-get-public-ip.php';

		$ip = new UBP_Get_Public_IP( $this->get_domain() );

		// Send domain name in request headers so vhosts resolve
		$args = array( 'headers' => array( 'Host' => $this->get_domain() ) );
		// Route around local DNS by requesting by IP directly
		$url = 'http://'.$ip.$path;

		$this->response = wp_remote_get( $url, $args);

		if ( !is_wp_error($this->response) && 200 == $this->response['response']['code'] ) {
			$this->download( $path );
		}
	}

	public function download( $path ) {
		if ( !function_exists('WP_Filesystem')) { require ABSPATH.'wp-admin/includes/file.php'; }
		global $wp_filesystem; WP_Filesystem();

		$u = wp_upload_dir();
		$basedir = $u['basedir'];

		$remove = str_replace( get_option( 'siteurl' ), '', $u['baseurl'] );
		$basedir = str_replace( $remove, '', $basedir );
		$abspath = $basedir.$path;
		$dir = dirname( $abspath );

		if ( !is_dir( $dir ) && !wp_mkdir_p( $dir ) ) { 
			$this->display_and_exit( "Please check permissions. Could not create directory $dir" );
		}

		$saved_image = $wp_filesystem->put_contents( $abspath, $this->response['body'], FS_CHMOD_FILE ); // predefined mode settings for WP files

		if ( $saved_image ) {
			wp_redirect( $path );
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
	public function allow_path( $path ) {
		if ( empty($path) ) { return false; }

		$allowed_paths = array(
			$this->uploads_basedir(),
		);
		$allowed_paths = apply_filters( 'ubp_allowed_paths', $allowed_paths );

		foreach ( $allowed_paths as $value ){
			if ( false !== @strpos($path, $value) ) { return true; }
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

	public function get_domain() {
		if( !isset($this->domain) ){
			// Strip schema, slashes, and whitespace
			$this->domain = str_replace( array( 'http://', 'https://', '/', ' ' ), '', UBP_LIVE_DOMAIN );
		}
		return $this->domain;
	}

}