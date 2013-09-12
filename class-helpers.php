<?php

class UBP_Helpers {

	/**
	 * Only load required files on the 404_template hook
	 */
	static public function init_404_template( $template ) {
		global $UBP_404_Template;
		require_once dirname( __FILE__ ).'/class-404-template.php';
		$UBP_404_Template = new UBP_404_Template();
		return $template;
	}

	static public function requirements_check() {
		add_action( 'admin_init', 'UBP_Helpers::require_no_multisite', 11 );
		add_action( 'admin_notices', 'UBP_Helpers::request_uploads_writable' );
		add_action( 'admin_footer', 'UBP_Helpers::request_permalinks_enabled' );
	}

	/**
	 * Require single-site install before activating.
	 */
	static public function require_no_multisite() {
		if ( function_exists( 'is_multisite' ) && !is_multisite() ) { return true; }

		if ( is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX) ) {
			require_once ABSPATH.'/wp-admin/includes/plugin.php';
			deactivate_plugins( UBP_PLUGIN_FILE );
			wp_die( __( 'Uploads by Proxy is not yet compatible with network installs. The plugin has now disabled itself. Please activate on single-site installs only.', 'uploads-by-proxy') );
		}

		return false;
	}

	/**
	 * Display an error message when permalinks are disabled
	 * Runs on admin_footer becuase admin_notices hook is too early to catch recent changes in permalinks
	 */
	static public function request_permalinks_enabled() {
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
	static public function request_uploads_writable() {
		$upload_dir = wp_upload_dir();
		if ( is_writable( $upload_dir['basedir'] ) ) { return true; }

		echo '<div id="ubp_uploads_message" class="error"><p>'
			 . __( 'The uploads directory must be enabled for Uploads by Proxy to work. ', 'uploads-by-proxy' )
			 . sprintf( __( '%sRead about changing file permissions%s, or try running:', 'uploads-by-proxy' ), '<a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">', '</a>' )
			 . sprintf( "<br/><code>chmod 755 '%s';</code>", $upload_dir['basedir'] )
			 . '</p></div>';

		return false;
	}

}