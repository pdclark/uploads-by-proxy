<?php

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