<?php
/**
 * Plugin Name: WP AJAX Audit
 * Description: A nice and easy remote audit for your WP site via AJAX. Returns information about the core, plugins, and themes. (For expert users only!)
 * Version: 1.0.0
 * Author: David Kryzaniak (@dave_kz on Twitter)
 * Author URI: http://dave.kz/
 * License: GNU2
 */

add_action('wp_ajax_wp_ajax_audit', 'wp_ajax_audit_run');
add_action('wp_ajax_nopriv_wp_ajax_audit', 'wp_ajax_audit_run');

//non-admins get nothing.
if (is_admin()) {add_action('admin_menu', 'wp_ajax_audit_add_page');}

register_activation_hook(__FILE__, 'wp_ajax_audit_activate');
register_deactivation_hook(__FILE__, 'wp_ajax_audit_deactivate');


/**
 * called on activation
 */
function wp_ajax_audit_activate()
{
    // Activation
    // Create a new access key
    add_option('wp_ajax_audit_access_key', md5(microtime() . rand()));
}

/**
 * Deactivates the plugin
 */
function wp_ajax_audit_deactivate()
{
    // Deactivation
    // remote the access key
    delete_option('wp_ajax_audit_access_key');
}

/**
 * Checks for the access key. If the key matches, wp_ajax_audit_site_profile() is sent.
 */
function wp_ajax_audit_run()
{
    if (!isset($_POST['wp_ajax_audit_access_key']) || get_option('wp_ajax_audit_access_key') != $_POST['wp_ajax_audit_access_key']) {
        echo "Your Access Key is not correct!";
        exit;
    } else {
        // force an update check
        wp_ajax_audit_check_for_updates();

        header("Content-Type: application/json");

        echo json_encode(wp_ajax_audit_site_profile());

        exit; //stop loading stuff
    }
}

/**
 * Forces a rebuild of the Update stats
 */
function wp_ajax_audit_check_for_updates()
{
    //wipe the transient data
    delete_site_transient( 'update_plugins' );
    delete_site_transient( 'update_themes' );
    delete_site_transient( 'update_core' );

    //rebuild what we just deleted
    //From wp-admin/includes/update.php
    wp_version_check();
    wp_update_plugins();
    wp_update_themes();
}

if(!function_exists('wp_ajax_audit_wp_profile')) :

    /**
     * This functions builds the array for information you want to send in the ajax request.
     * Need to send custom parameters? Copy this function and place it in you functions.php
     *
     * @return array    An array of the data you want to send back.
     */
    function wp_ajax_audit_site_profile()
    {
        $data = maybe_unserialize(wp_load_alloptions());

        get_site_transient( 'update_core' );

        //http://www.youtube.com/watch?v=HKDk0FFzT-Q
        unset($data['logged_in_salt']);
        unset($data['auth_salt']);
        unset($data['nonce_salt']);
        unset($data['secure_auth_salt']);

        $profile = array(
            'siteurl' => $data['siteurl'],
            'blogname' => $data['blogname'],
            'blogdescription' => $data['blogdescription'],

            'update_themes' => maybe_unserialize(get_site_transient( 'update_themes' )),
            'update_plugins' => maybe_unserialize(get_site_transient( 'update_plugins' )),

            'active_plugins' => maybe_unserialize($data['active_plugins']),
            'active_theme' => $data['current_theme'],

            'all_plugins' => get_plugins(),
            'all_themes' => wp_ajax_audit_get_themes_info(),

            'core_status' => get_preferred_from_update_core(),
        );

        return $profile;
    }
endif;

/**
 * @return array Themes Info
 */
function wp_ajax_audit_get_themes_info()
{
    $themes = array();
    foreach(wp_get_themes() as $singleTheme=>$value){
        $theme = wp_get_theme($singleTheme);
        $themes[$singleTheme] = array(
            'Name' => $theme->get('Name'),
            'Version' => $theme->get('Version'),
        );
    }
    return $themes;
}

/**
 * Add the page to the backend of WordPress
 */
function wp_ajax_audit_add_page()
{
    //Add a new submenu for wp ajax audit under Tools
    add_management_page(
        __('WP Ajax Audit', 'menu-wp-ajax-audit'),
        __('WP AJAX Audit', 'menu-wp-ajax-audit'),
        'manage_options',
        'WPAjaxAudit',
        'wp_ajax_audit_page'
    );
}

/**
 * Adds the content to the admin page
 *
 * @return string Content of the page
 */
function wp_ajax_audit_page()
{
    //admins only
    if (!is_admin()) {
        return "Sorry, you need to be an admin to view this page.";
    }

    //displays the page content for the Test Tools submenu
    //@to-do add langauge support!

    ?>
    <h2 class="wp-ajax-audit-title" style="text-align:center">Your Access Key is:</h2>
    <div class="wp-ajax-audit-key-box" style="text-align:center;margin:50px">
			<span class="wp-ajax-audit-key"
                  style="padding:9px 14px;margin-bottom:14px;background-color:#F7F7F9;border:1px solid #E1E1E8;border-radius:4px;font-size:2em">
				<?php echo get_option('wp_ajax_audit_access_key'); ?>
			</span>

        <p>Need a new key? Deactivate and Reactivate this plugin!</p>
    </div>
    <div class="examples">
        <h5>Using PHP with cURL (This should be done on a private remote machine)</h5>
			<pre lang="php">
&lt;?php

	//open connection
	$ch = curl_init();

	//find the admin-ajax.php url
	$wp_admin_ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
	
	//assemble the POST parameters
	$fields_string = 'action=wp_ajax_audit&' .
		'wp_ajax_audit_access_key=<?php echo get_option('wp_ajax_audit_access_key'); ?>';
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, 2);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

	//execute post
	$result = curl_exec($ch);

	//close connection
	curl_close($ch);
	
?&gt;
			</pre>
    </div>
<?php
}

?>
