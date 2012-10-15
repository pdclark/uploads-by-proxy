=== Uploads by Proxy ===
Contributors: pdclark, brainstormmedia
Author URI: http://brainstormmedia.com
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FD4GKBGQFUZC8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: localhost, local, development, uploads, media library, xampp, mamp, wamp
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 1.0

Develop locally without downloading your uploads folder.

== Description ==


Example filter: Add custom hook for getting public IP.
In this case, ip.php would just run `<?php echo gethostbyname( $_GET['domain'] );`
`
	add_filter('ubp_ip_url', 'ubp_ip_url', 10, 2);
	function ubp_ip_url( $url, $domain ) {
		return 'http://website.com/ip.php?domain='.$domain;
	}
`


== Installation ==

1. Upload the `uploads-by-proxy` folder to the `/wp-content/plugins/` directory
1. In a local or staging development site, activate the Uploads by Proxy plugin through the 'Plugins' menu in WordPress
1. If your development site address is different than your live site address, set your live site address in wp-config.php with `define('UBP_LIVE_DOMAIN', 'live-site-address.com');`

== Frequently Asked Questions ==

= What will happen if I enable this plugin on a live site? =

Nothing. The plugin only takes action if it detects it is on a local development environment.


== Changelog ==

= 0.1 =

* Initial beta release.