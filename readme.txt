=== WP-AJAX-Audit ===
Contributors: david.kryzaniak
Tags: audit, security, ajax, ajax audit
Requires at least: 3.5.0
Tested Up to: 3.6.1
Stable tag: 1.0.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A quick remote audit of your WordPress site via AJAX. Returns information about the core, plugins, and themes.

== Description ==

Ever want to check the WordPress settings of your site without logging in?

WP-AJAX-Audit is a simple plugin that allows you to send an AJAX request (from a remote server) to retrieve information about your site. Quickly find things like core settings, plugins/themes installed, and much more.

= Sending The Request =
After installing, browse to Tools >  WP AJAX Audit to find your access key, and to generate an example script.

The request must be made with Post parameters (An "action" which is set to "wp_ajax_audit" and the "wp_ajax_audit_access_key")

Here is an example:
`<?php

	//open connection
	$ch = curl_init();

	//find the admin-ajax.php url
	$wp_admin_ajax_url = 'http://www.example.com/wp-admin/admin-ajax.php';
	
	//assemble the POST parameters
	$fields_string = 'action=wp_ajax_audit' .
		'&wp_ajax_audit_access_key=abcdefg123456';
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, 2);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

	//execute post
	$result = curl_exec($ch);

	//close connection
	curl_close($ch);
	
?>`

If successful, $result is a json encoded version of wp_load_alloptions().

Note, you'll still need to 'unserialize(json_decode($result))' to get the array of data.

== Installation ==

1. Upload the files to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. To get the your sites access key, go to: Tools > WP AJAX Audit 

== Frequently Asked Questions ==

Coming Soon!

== Screenshots ==

Screenshots are coming soon.

== Changelog ==

= 1.0.0 =
Bugs worked out. Current stable version.

= 0.1.0 =
Initial commit

== Upgrade Notice ==
No known upgrade issues