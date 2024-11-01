<?php
/**
 * @wordpress-plugin
 * Plugin Name: VIA CRM Forms
 * Description: Use your Wordpress site to capture leads from Gravity Forms and import them to SalesForce as leads. Works with any theme.
 * Version:     1.0.5
 * Author:      VIA Studio
 * Author URI:  https://viastudio.com
 * Requires PHP:      7.1
 */
if (!defined('WPINC')) {
    die;
}

//Load the current WP core version
include( ABSPATH . WPINC . '/version.php' );
if (version_compare($wp_version, '5.3') < 0) {
    //If WP core is < 5.3, do a PHP version check ourselves
    $php_version = phpversion();

    if (version_compare($php_version, '7.1') < 0) {
        die('This plugin requires PHP 7.1 or higher');
    }
}

require_once __DIR__ . '/vendor/autoload.php';

//Version should be incremented any time there's a database change
if (!defined('VIAGF_VERSION_KEY')) {
    define('VIAGF_VERSION_KEY', 'viagf_version');
}
if (!defined('VIAGF_VERSION')) {
    define('VIAGF_VERSION', '1.0.0');
}

//Add plugin JS & CSS
add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'page_gf') !== false) {
        wp_enqueue_script('viagf_admin_js', plugin_dir_url(__FILE__) . 'build/via-gravityforms.js', ['jquery']);
    }
});

add_filter('gform_noconflict_scripts', 'register_script');
function register_script($scripts) {

    //registering scripts with Gravity Forms so that it gets enqueued when running on no-conflict mode
    $scripts[] = 'viagf_admin_js';
    $scripts[] = 'viagf_admin_css';
    return $scripts;
}

//Bootstrap and load our GF add-on
class GF_Bootstrap {
    public static function load() {
        if (!method_exists('GFForms', 'include_addon_framework')) {
            return;
        }

        require_once(__DIR__ . '/src/AddOn/class-gfsalesforceaddon.php');

        \GFAddOn::register('\ViaGF\GFSalesforceAddOn');
    }
}
add_action('gform_loaded', array('GF_Bootstrap', 'load'), 5);

if (!function_exists('gf_salesforce_addon')) {
    function gf_salesforce_addon() {
        return \ViaGF\GFSalesforceAddOn::get_instance();
    }
}

if (!function_exists('sanitize_number')) {
    function sanitize_number($val) {
        return preg_replace("/[^0-9]/", "", $val);
    }
}

//Define functions for handling our custom meta data from the database
if (!function_exists('viagf_get_form_meta')) {
    /**
     * Retrieve a meta value from the database.
     */
    function viagf_get_form_meta($form_id, $key) {
        $wpdb = $GLOBALS['wpdb'];
        $tableName = "{$wpdb->prefix}gf_viagf_meta";

        $q = $wpdb->prepare("SELECT meta_value FROM $tableName WHERE form_id = %d AND meta_key = %s", $form_id, $key);
        return $wpdb->get_var($q);
    }
}

if (!function_exists('viagf_update_form_meta')) {
    /**
     * Add a new meta value to the database. Delete the existing value, if any, first
     */
    function viagf_update_form_meta($form_id, $key, $value) {
        $wpdb = $GLOBALS['wpdb'];
        $tableName = "{$wpdb->prefix}gf_viagf_meta";

        if (viagf_has_form_meta($form_id, $key)) {
            viagf_delete_form_meta($form_id, $key);
        }

        return $wpdb->insert($tableName, [
            'form_id' => $form_id,
            'meta_key' => $key,
            'meta_value' => $value,
        ], [
            '%d',
            '%s',
            '%s',
        ]);
    }
}

if (!function_exists('viagf_delete_form_meta')) {
    /**
     * Delete a meta value from the database
     */
    function viagf_delete_form_meta($form_id, $key) {
        $wpdb = $GLOBALS['wpdb'];
        $tableName = "{$wpdb->prefix}gf_viagf_meta";

        return $wpdb->delete($tableName, [
            'form_id' => $form_id,
            'meta_key' => $key
        ], [
            '%d',
            '%s',
        ]);
    }
}

if (!function_exists('viagf_clear_form_meta')) {
    /**
     * Delete all meta values for a form
     */
    function viagf_clear_form_meta($form_id) {
        $wpdb = $GLOBALS['wpdb'];
        $tableName = "{$wpdb->prefix}gf_viagf_meta";

        return $wpdb->delete($tableName, [
            'form_id' => $form_id,
        ], [
            '%d',
        ]);
    }
}

if (!function_exists('viagf_has_form_meta')) {
    /**
     * Check to see if a meta value exists
     */
    function viagf_has_form_meta($form_id, $key) {
        return (bool) viagf_get_form_meta($form_id, $key);
    }
}

//Load the rest of our plugin functionality
$viagf_loader = new \ViaGF\Loader(__DIR__, plugin_dir_url(__FILE__));
$viagf_loader->init();

//Handle activating/deactivating the plugin
register_activation_hook(__FILE__, [\ViaGF\Loader::class, 'activatePlugin']);
register_deactivation_hook(__FILE__, [\ViaGF\Loader::class, 'deactivatePlugin']);
