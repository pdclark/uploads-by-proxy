=== Uploads by Proxy ===
Contributors: pdclark
Author URI: http://pdclark.com
Tags: localhost, local, development, staging, uploads, media library, xampp, mamp, wamp, git, svn, subversion
Requires at least: 3.1
Tested up to: 3.6
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

For local development: Automatically load images from the production version of wp-content/uploads if they are missing locally.

== Description ==

This plugin is meant to be used by developers who work on sites in a local development environment before deploying changes to a production (live) server. It allows you skip downloading the contents of `wp-content/uploads` to your local WordPress install. Instead, images missing from the uploads directory are loaded from the production server as needed.

= Setup =

In most cases, you should be able to activate the plugin and go. If the plugin does not work automatically, then you need to set the address of your live WordPress install in `wp-config.php`, like this:

     define('UBP_SITEURL', 'http://example-live.com/wordpress');

If you are on a staging server (not a local development environment) you may need to force the plugin to run with `define( 'UBP_IS_LOCAL', true );` in `wp-config.php`. Do not set this on a live site!


== Installation ==

1. Upload the `uploads-by-proxy` folder to the `/wp-content/plugins/` directory
1. In a local or staging development site, activate the Uploads by Proxy plugin through the 'Plugins' menu in WordPress
1. If your development site address is different than your live site address, or you are not using `WP_SITEURL`, set your live site address in `wp-config.php` like this: `define('UBP_SITEURL', 'http://example-live.com/wordpress');`

== Frequently Asked Questions ==

= Why would I want to use this? =

Maybe you work on a site with gigabytes of images in the uploads directory, or maybe you use a version control system like SVN or Git, and prefer to not store `wp-content/uploads` in your repository. Either way, this plugin allows you to not worry about the uploads folder being up-to-date.

= What is a production environment/site/server? =

"Production" is a term that's used to refer to a version of a site that is running live on the Internet. If you normally edit your site through the WordPress code editor or over FTP, you are making edits on the production server. Editing your site in this way risks crashing the site for your visitors each time you make an edit.

= What is a development environment/site/server? =

"Development" refers to a version of your site that is in a protected area only accessible to you. Programs like [MAMP](http://www.mamp.info), [WAMP](http://www.wampserver.com/), and [XAMPP](http://www.apachefriends.org/en/xampp.html) allow you run a copy of your WordPress site in a way that is only accessible from your computer. This allows you to work on a copy and test changes without effecting the live site until you are ready to deploy your changes.

= An image changed on my live server, but it didn't update locally. =

This plugin only goes into action when an image is missing on your local copy. When it runs, it copies the file into your local wp-content/uploads folder and doesn't run again. If you'd like to update an image with the production copy again, delete your local copy.

= What will happen if I enable this plugin on a live site? =

Nothing. The plugin only takes action if it detects it is on a local development environment.

= How does the plugin detect the difference between a production and development environment? =

The plugin only loads if the server address and browser address are both `127.0.0.1`. This should catch most local environments, such as MAMP, WAMP, and XAMPP.

If you want to run the plugin on a staging server, or have some other situation where you want to force the plugin to run, set `define('UBP_IS_LOCAL', true);` in wp-config.php.

**Warning!** Do not force `UBP_IS_LOCAL` to `true` on a production site! If if have any 404 requests for images in the uploads directory, it will cause PHP to go into an infinite loop until Apache kills the process. This could make your site run very slowly.

= How does the plugin get around local DNS when production and development sites use the same domain? =

It asks one of several remote servers what *it* thinks the IP address of your production domain is, then uses that IP to request the missing image. The correct domain name is sent in headers so that virtualhosts resolve.

= What if I don't want my domain name sent to some third-party site? =

You can define your own server that will return an IP address when given a domain name. In your WordPress install, you would put this in a plugin or functions.php:

`add_filter('ubp_ip_url', 'ubp_ip_url', 10, 2);
function ubp_ip_url( $url, $domain ) {
    return 'http://yoursite.com/ip.php?domain='.$domain;
}`

**yoursite.com** would be replaced with your own site address, and **ip.php** would contain:

`<?php echo gethostbyname( $_GET['domain'] ); ?>`

= Where are those icons in the wordpress.org plugin header from? =

[Font Awesome](http://fortawesome.github.com/Font-Awesome)

== Changelog ==

= 1.1.2 =

* Fix: Resolve a warning output in debug environments. (Static function not declared as static.)

= 1.1.1 =

* Fix: Suppress notice that could keep image from displaying on first load when WordPress installed in root directory. Thanks [@justnorris](http://wordpress.org/support/topic/fixed-some-issues?replies=1).

= 1.1 =

* Auto-detect live URL in cases where `WP_SITEURL` is being set in `wp-config.php`.
* Change `UBP_LIVE_SITE` to `UBP_SITEURL` and match format to `WP_SITEURL`.
* Maintain legacy support for installs using `UBP_LIVE_SITE`.
* Add support for root directory installs mapping to subdirectory installs.
* Add support for subdirectory installs mapping to root directory installs.
* Add IPv6 localhost address when checking for local development environment.

= 1.0 =

* Initial public release.

== Upgrade Notice ==

* Fix: Resolve a warning output in debug environments. (Static function not declared as static.)
* Fix: Suppress notice that could keep image from displaying on first load when WordPress installed in root directory. Thanks [@justnorris](http://wordpress.org/support/topic/fixed-some-issues?replies=1).
