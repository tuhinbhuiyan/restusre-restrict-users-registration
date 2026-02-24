<?php
/*
Plugin Name: Restrict Users Registration by EmailVerifierPro.app
Description: Easily control who can register. Block bad emails/domains, prevent duplicate IPs, and real-time email validation during signup.
Version: 1.0.1
Author: Tuhin Bhuiyan
Author URI: https://tuhin.dev
Text Domain: restusre-restrict-users-registration
Domain Path: /languages
License: GPL2+
*/

// Exit if accessed directly for security
if (!defined('ABSPATH'))
    exit;

// Plugin constants
define('RESTUSRE_PLUGIN_VERSION', '1.0.1');
define('RESTUSRE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESTUSRE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Require all class files BEFORE using them in hooks!
require_once RESTUSRE_PLUGIN_DIR . 'includes/class-db.php';
require_once RESTUSRE_PLUGIN_DIR . 'includes/class-ip-signup.php';
require_once RESTUSRE_PLUGIN_DIR . 'includes/class-email-verifier.php';
require_once RESTUSRE_PLUGIN_DIR . 'includes/class-blacklist.php';
require_once RESTUSRE_PLUGIN_DIR . 'includes/class-hooks.php';
require_once RESTUSRE_PLUGIN_DIR . 'admin/class-admin.php';

// Activation hook.
register_activation_hook(__FILE__, function () {
    RESTUSRE_DB::install();
    RESTUSRE_Blacklist::install();
    RESTUSRE_Admin::activate();
});

// Deactivation hook (deletes data if requested in plugin settings).
register_deactivation_hook(__FILE__, function () {
    $general = RESTUSRE_DB::get_option('restusre_general', array());
    if (isset($general['delete_on_deactivate']) && $general['delete_on_deactivate']) {
        RESTUSRE_DB::delete_all_plugin_data();
    }
    RESTUSRE_Admin::deactivate();
});

// Initialize the plugin.
add_action('plugins_loaded', function () {
    RESTUSRE_Admin::init();
    RESTUSRE_Hooks::init();
});