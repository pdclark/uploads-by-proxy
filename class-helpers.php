<?php

class UBP_Helpers {

	/**
	 * Used to load required files on the 404_template hook, instead of immediately.
	 * Method from Yoast's WordPress SEO
	 */
	static public function frontend_init( $template ) {
		global $UBP_Frontend;
		require_once dirname( __FILE__ ).'/class-frontend.php';
		$UBP_Frontend = new UBP_Frontend();
		return $template;
	}

	static public function requirements_check() {
		self::permalinks_enabled();
		self::uploads_writable();
	}

	/**
	 * Display an error message when permalinks are disabled
	 */
	static public function permalinks_enabled() {
		if ( '' != get_option('permalink_structure') ) { return true; }

		echo '<div id="ubp_permalinks_message" class="error"><p>'
			 . __( 'Pretty Permalinks must be enabled for Uploads by Proxy to work. ', 'uploads-by-proxy' )
			 . sprintf( __( '%1$sRead about using Permalinks%3$s, then %2$sgo to your Permalinks settings%3$s.', 'uploads-by-proxy' ), '<a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">', '<a href="options-permalink.php">', '</a>' )
			 . '</p></div>';

		return false;
	}

	/**
	 * Display an error message when uploads folder is not writable
	 */
	static public function uploads_writable() {
		$upload_dir = wp_upload_dir();
		if ( is_writable( $upload_dir['basedir'] ) ) { return true; }

		echo '<div id="ubp_uploads_message" class="error"><p>'
			 . __( 'The uploads directory must be enabled for Uploads by Proxy to work. ', 'uploads-by-proxy' )
			 . sprintf( __( '%sRead about changing file permissions%s, or run:<br/><code>chmod 755 "%s";', 'uploads-by-proxy' ), '<a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">', '</a>', $upload_dir['basedir'] )
			 . '</p></div>';

		return false;
	}

	function warn_if_network() {
		if ( function_exists( 'is_multisite' ) && is_multisite() )
			return;
	}
}