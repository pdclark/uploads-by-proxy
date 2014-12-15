<?php

/**
 * Work around local DNS by asking public web sites to look up the IP of a domain
 */
class UBP_Get_Public_IP {

	var $domain;
	var $transient;
	var $expire = 86400;
	var $ip;
	var $ip_pattern = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/';

	function __construct( $domain ) {
		$this->domain = $domain;
		$this->transient = 'dbp_'.$domain;

		$ip = get_transient( $this->transient );

		if ( empty($ip) ) {
			// Loading and serving from localhost
			$custom_url = apply_filters('ubp_ip_url', false, $this->domain );
			$custom_args = apply_filters('ubp_ip_args', array() );

			if ( $custom_url ) $ip = $this->get_ip( $custom_url, $custom_args );
			if ( !$ip ) $ip = $this->get_ip( "http://baremetal.com/cgi-bin/dnsip?target=$domain" );
			if ( !$ip ) $ip = $this->get_ip( 'http://hostnametoip.com/', array( 'index'=>1, 'method' => 'POST', 'referer'=>'http://hostnametoip.com/', 'body' => 'conversion=1&addr='.$domain ) );
			if ( !$ip ) $ip = $this->get_ip( "http://aruljohn.com/cgi-bin/hostname2ip.pl?host=$domain", array( 'referer'=>'http://aruljohn.com/hostname2ip.html' ) );

			if ( $ip ) {
				set_transient( $this->transient, $ip, $this->expire );
				$this->ip = $ip;
			}
		}

		if ( empty($ip) ){ $ip = $domain; }

		$this->ip = $ip;
	}

	function __toString() {
		return $this->ip;
	}

	public function get_ip( $url, $args=array() ) {
		$defaults = array( 
			'method' => 'GET',
			'referer'=> $domain,
			'body' => '',
			'index' => 0,
		);
		$args = wp_parse_args($args, $defaults);
		extract( $args );

		$query_args = array(
			'method' => $method,
			'headers' => array('Referer'=>$referer),
			'body' => $body,
		);

		$response = wp_remote_get( $url, $query_args );
		
		if ( ! is_wp_error( $response ) ) {
			$body = strip_tags($response['body']);

			preg_match_all( $this->ip_pattern, $body, $matches );

			return !empty( $matches[0][$index] )
				? $matches[0][$index]
				: false;
		}

		return false;
	}

}