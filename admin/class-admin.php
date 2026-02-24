<?php
/**
 * Admin logic for Restrict Users Registration by EmailVerifierPro.app
 *
 * Handles admin menu, settings, AJAX, and admin UI logic.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly for security
}

class RESTUSRE_Admin {
    /**
     * Initialize admin menu, assets, and AJAX handlers.
     *
     * @return void
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_ajax_restusre_save_settings', array( __CLASS__, 'ajax_save_settings' ) );
        add_action( 'wp_ajax_restusre_blacklist_action', array( __CLASS__, 'ajax_blacklist_action' ) );
        add_action( 'wp_ajax_restusre_domain_blacklist', array( __CLASS__, 'ajax_domain_blacklist' ) );
        // Email blacklist AJAX
        add_action( 'wp_ajax_restusre_email_blacklist_list', array( __CLASS__, 'ajax_email_blacklist_list' ) );
        add_action( 'wp_ajax_restusre_email_blacklist_add', array( __CLASS__, 'ajax_email_blacklist_add' ) );
        add_action( 'wp_ajax_restusre_email_blacklist_remove', array( __CLASS__, 'ajax_email_blacklist_remove' ) );
        // Domain blacklist AJAX
        add_action( 'wp_ajax_restusre_domain_blacklist_list', array( __CLASS__, 'ajax_domain_blacklist_list' ) );
        add_action( 'wp_ajax_restusre_domain_blacklist_add', array( __CLASS__, 'ajax_domain_blacklist_add' ) );
        add_action( 'wp_ajax_restusre_domain_blacklist_remove', array( __CLASS__, 'ajax_domain_blacklist_remove' ) );
        add_action( 'wp_ajax_restusre_signup_activity_list', array( __CLASS__, 'ajax_signup_activity_list' ) );
        add_action( 'wp_ajax_restusre_signup_activity_remove', array( __CLASS__, 'ajax_signup_activity_remove' ) );
    }
    /**
     * Register plugin admin menu and submenus.
     *
     * @return void
     */
    public static function add_menu() {
        add_menu_page(
            __( 'Restrict Users Registration by EmailVerifierPro.app', 'restusre-restrict-users-registration' ),
            __( 'Restrict Users Registration', 'restusre-restrict-users-registration' ),
            'manage_options',
            'restusre-restrict-users-registration',
            array( __CLASS__, 'settings_page' ),
            'dashicons-shield-alt'
        );
        add_submenu_page(
            'restusre-restrict-users-registration',
            __( 'Email Blacklist', 'restusre-restrict-users-registration' ),
            __( 'Email Blacklist', 'restusre-restrict-users-registration' ),
            'manage_options',
            'restusre-email-blacklist',
            array( __CLASS__, 'email_blacklist_page' )
        );
        add_submenu_page(
            'restusre-restrict-users-registration',
            __( 'Domain Blacklist', 'restusre-restrict-users-registration' ),
            __( 'Domain Blacklist', 'restusre-restrict-users-registration' ),
            'manage_options',
            'restusre-domain-blacklist',
            array( __CLASS__, 'domain_blacklist_page' )
        );
        add_submenu_page(
            'restusre-restrict-users-registration',
            __( "User Signup Logs", 'restusre-restrict-users-registration' ),
            __( "User Signup Logs", 'restusre-restrict-users-registration' ),
            'manage_options',
            'restusre-signup-activity',
            array( __CLASS__, 'signup_activity_page' )
        );
    }
    /**
     * Enqueue admin CSS/JS assets for plugin pages.
     *
     * @param string $hook The current admin page hook.
     * @return void
     */
    public static function enqueue_assets( $hook ) {
        // Debug: Log the hook value for troubleshooting
        error_log('RESTUSRE DEBUG: enqueue_assets called for hook: ' . $hook);
        // Allow for both main and submenu page hooks
        $valid_hooks = array(
            'toplevel_page_restusre-restrict-users-registration',
            'restrict-users-registration_page_restusre-email-blacklist',
            'restrict-users-registration_page_restusre-domain-blacklist', 
            'restrict-users-registration_page_restusre-signup-activity',
        );
        
        // Also check if the hook contains plugin slug for more flexible matching
        $is_plugin_page = in_array( strtolower($hook), array_map('strtolower', $valid_hooks) ) 
                         || strpos($hook, 'restusre') !== false;
        
        if ( !$is_plugin_page ) return;
        wp_enqueue_script( 'restusre-admin', RESTUSRE_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), RESTUSRE_PLUGIN_VERSION, true );
        // Pass debug flag to JS in a WordPress-compliant way
        $debug = !empty(RESTUSRE_DB::get_option('restusre_general', array())['debug_logging']);
        wp_add_inline_script('restusre-admin', 'window.RESTUSRE_DOMAIN_DEBUG = ' . ($debug ? 'true' : 'false') . ';');
        wp_enqueue_style( 'restusre-admin', RESTUSRE_PLUGIN_URL . 'admin/css/admin.css', array(), RESTUSRE_PLUGIN_VERSION );
        wp_enqueue_style( 'restusre-bootstrap', RESTUSRE_PLUGIN_URL . 'admin/css/bootstrap.min.css', array(), RESTUSRE_PLUGIN_VERSION );
        wp_localize_script( 'restusre-admin', 'RESTUSRE_AJAX', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'restusre_admin_nonce' ),
        ) );
    }
    /**
     * Render the main settings page.
     *
     * @return void
     */
    public static function settings_page() {
        $general = RESTUSRE_DB::get_option('restusre_general', array( 'enabled' => 0, 'prevent_duplicate_ip' => 0, 'delete_on_deactivate' => 0 ) );
        $is_enabled = !empty( $general['enabled'] );
        ?>
        <div class="wrap evp-settings-page">
            <h1><?php esc_html_e( 'Restrict Users Registration by EmailVerifierPro.app', 'restusre-restrict-users-registration' ); ?></h1>
            <?php if ( $is_enabled ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Email validation is currently ACTIVE and will be enforced on all new registrations.', 'restusre-restrict-users-registration' ); ?></p>
                </div>
            <?php else : ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php esc_html_e( 'Email validation is currently INACTIVE. Registrations are not being checked.', 'restusre-restrict-users-registration' ); ?></p>
                </div>
            <?php endif; ?>
            <div id="evp-settings-root"></div>
        </div>
        <?php
        include RESTUSRE_PLUGIN_DIR . 'admin/settings-template.php';
    }
    /**
     * Render the email blacklist admin page.
     *
     * @return void
     */
    public static function email_blacklist_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Email Blacklist', 'restusre-restrict-users-registration' ) . '</h1>';
        include RESTUSRE_PLUGIN_DIR . 'admin/email-blacklist-template.php';
        echo '</div>';
    }
    /**
     * Render the domain blacklist admin page.
     *
     * @return void
     */
    public static function domain_blacklist_page() {
        echo '<div class="wrap"><h1>' . esc_html__( 'Domain Blacklist', 'restusre-restrict-users-registration' ) . '</h1>';
        include RESTUSRE_PLUGIN_DIR . 'admin/domain-blacklist-template.php';
        echo '</div>';
    }
    /**
     * Render the signup activity admin page.
     *
     * @return void
     */
    public static function signup_activity_page() {
        echo '<div class="wrap"><h1>' . esc_html__( "User Signup Logs", 'restusre-restrict-users-registration' ) . '</h1>';
        include RESTUSRE_PLUGIN_DIR . 'admin/signup-activity-template.php';
        echo '</div>';
    }
    /**
     * Sanitize general settings input.
     *
     * @param array $input
     * @return array
     */
    public static function sanitize_general( $input ) {
        // Always ensure all keys exist and default to 0
        return array(
            'enabled' => !empty($input['enabled']) ? 1 : 0,
            'prevent_duplicate_ip' => !empty($input['prevent_duplicate_ip']) ? 1 : 0,
            'delete_on_deactivate' => !empty($input['delete_on_deactivate']) ? 1 : 0,
            'invalid_retry_limit' => array_key_exists('invalid_retry_limit', $input) ? intval($input['invalid_retry_limit']) : 3,
            'debug_logging' => !empty($input['debug_logging']) ? 1 : 0,
        );
    }
    /**
     * Sanitize API settings input.
     *
     * @param array $input
     * @return array
     */
    public static function sanitize_api( $input ) {
        return array(
            'api_domain' => isset($input['api_domain']) ? sanitize_text_field($input['api_domain']) : '',
            'username'   => isset($input['username']) ? sanitize_text_field($input['username']) : '',
            'api_key'    => isset($input['api_key']) ? sanitize_text_field($input['api_key']) : '',
        );
    }
    /**
     * Sanitize domain blacklist input.
     *
     * @param array $input
     * @return array
     */
    public static function sanitize_domain_blacklist( $input ) {
        $list = array();
        if ( ! empty( $input['domains'] ) && is_array($input['domains']) ) {
            foreach ( $input['domains'] as $domain ) {
                $domain = trim( sanitize_text_field( $domain ) );
                if ( ! empty( $domain ) && filter_var( "test@$domain", FILTER_VALIDATE_EMAIL ) ) {
                    $list[] = $domain;
                }
            }
        }
        return array_unique( $list );
    }
    /**
     * AJAX: Save plugin settings (with nonce and capability check).
     *
     * @return void
     */
    public static function ajax_save_settings() {
        // Security: Nonce and capability check FIRST
        check_ajax_referer( 'restusre_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'restusre-restrict-users-registration' ) );
        }
        // Only log debug info if debug_logging is enabled in POST (raw, before sanitize)
        $debug_enabled = false;
        if (isset($_POST['general']['debug_logging']) && $_POST['general']['debug_logging']) {
            $debug_enabled = true;
        }
        // Log only sanitized keys for debug (not the whole POST)
        if ($debug_enabled) {
            $log = array();
            if (isset($_POST['general']) && is_array($_POST['general'])) {
                $log['general'] = self::sanitize_general($_POST['general']);
            }
            if (isset($_POST['api']) && is_array($_POST['api'])) {
                $log['api'] = self::sanitize_api($_POST['api']);
            }
            if (isset($_POST['domains']) && is_array($_POST['domains'])) {
                $log['domains'] = self::sanitize_domain_blacklist(array('domains' => $_POST['domains']));
            }
            error_log('RESTUSRE DEBUG: sanitized POST keys: ' . print_r($log, true));
        }
        $general = isset( $_POST['general'] ) && is_array($_POST['general']) ? self::sanitize_general( $_POST['general'] ) : array();
        if ($debug_enabled) error_log('RESTUSRE DEBUG: $general after sanitize: ' . print_r($general, true));
        $api     = isset( $_POST['api'] ) && is_array($_POST['api']) ? self::sanitize_api( $_POST['api'] )         : array();
        $domains = isset( $_POST['domains'] ) && is_array($_POST['domains']) ? self::sanitize_domain_blacklist( array( 'domains' => $_POST['domains'] ) ) : array();
        // Debug: Log what is being saved
        if ($debug_enabled) error_log('RESTUSRE DEBUG: Saving general: ' . print_r($general, true));
        if ($debug_enabled) error_log('RESTUSRE DEBUG: Saving api: ' . print_r($api, true));
        if ($debug_enabled) error_log('RESTUSRE DEBUG: Saving domains: ' . print_r($domains, true));
        RESTUSRE_DB::update_option('restusre_general', $general);
        // Debug: Log what is loaded after save
        $saved_general = RESTUSRE_DB::get_option('restusre_general', array());
        if ($debug_enabled) error_log('RESTUSRE DEBUG: Loaded general after save: ' . print_r($saved_general, true));
        RESTUSRE_DB::update_option('restusre_api', $api);
        RESTUSRE_DB::update_option('restusre_domain_blacklist', $domains);
        wp_send_json_success( __( 'Settings saved.', 'restusre-restrict-users-registration' ) );
    }
    /**
     * Recursively sanitize $_POST for debug logging, masking sensitive fields.
     *
     * @param array $input
     * @return array
     */
    private static function sanitize_debug_post($input) {
        $sanitized = array();
        $sensitive_keys = array('api_key', 'password', 'pass', 'secret');
        foreach ($input as $key => $value) {
            if (in_array(strtolower($key), $sensitive_keys)) {
                $sanitized[$key] = '[MASKED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitize_debug_post($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = sanitize_text_field($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
    /**
     * AJAX: Process email blacklist action (block/approve).
     *
     * @return void
     */
    public static function ajax_blacklist_action() {
        check_ajax_referer( 'restusre_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'restusre-restrict-users-registration' ) );
        }
        $email = sanitize_email( $_POST['email'] );
        $action = sanitize_text_field( $_POST['action_type'] );
        if ( empty( $email ) ) {
            wp_send_json_error( __( 'Invalid email.', 'restusre-restrict-users-registration' ) );
        }
        $result = RESTUSRE_Blacklist::process_action( $email, $action );
        if ( $result ) {
            wp_send_json_success( __( 'Action processed.', 'restusre-restrict-users-registration' ) );
        } else {
            wp_send_json_error( __( 'Failed to process.', 'restusre-restrict-users-registration' ) );
        }
    }
    /**
     * Generic handler for blacklist AJAX actions (add, remove, list) for email/domain.
     *
     * @param string $type 'email' or 'domain'
     * @param string $action 'add', 'remove', 'list'
     * @return void
     */
    private static function handle_blacklist_ajax($type, $action) {
        $is_email = ($type === 'email');
        $option_key = $is_email ? 'restusre_email_blacklist' : 'restusre_domain_blacklist';
        $get_func = $is_email ? ['RESTUSRE_Blacklist', 'get_emails'] : ['RESTUSRE_DB', 'get_option'];
        $add_func = $is_email ? ['RESTUSRE_Blacklist', 'add_email'] : null;
        $remove_func = $is_email ? ['RESTUSRE_Blacklist', 'remove_email'] : null;
        $field = $is_email ? 'email' : 'domain';
        $sanitize = $is_email ? 'sanitize_email' : 'sanitize_text_field';
        if ($action === 'list') {
            check_ajax_referer( 'restusre_admin_nonce', 'nonce' );
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( __( 'Unauthorized.', 'restusre-restrict-users-registration' ) );
            }
            if ($is_email) {
                $items = call_user_func($get_func);
                wp_send_json_success($items);
            } else {
                $items = call_user_func($get_func, $option_key, array());
                $result = array();
                foreach ($items as $item) {
                    $result[] = array(
                        'domain' => $item,
                        'status' => 'active',
                        'added' => '',
                    );
                }
                wp_send_json_success($result);
            }
        } elseif ($action === 'add') {
            check_ajax_referer( 'restusre_admin_nonce', 'nonce' );
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( __( 'Unauthorized.', 'restusre-restrict-users-registration' ) );
            }
            $value = isset($_POST[$field]) && is_string($_POST[$field]) ? call_user_func($sanitize, $_POST[$field]) : '';
            if (empty($value)) {
                wp_send_json_error(
                    sprintf(
                        /* translators: %s: field name */
                        __('Invalid %s.', 'restusre-restrict-users-registration'),
                        $field
                    )
                );
            }
            if ($is_email) {
                $result = call_user_func($add_func, $value);
                if ($result) {
                    wp_send_json_success( __( 'Email added to blacklist.', 'restusre-restrict-users-registration' ) );
                } else {
                    wp_send_json_error( __( 'Failed to add email.', 'restusre-restrict-users-registration' ) );
                }
            } else {
                if (!$value || !filter_var("test@$value", FILTER_VALIDATE_EMAIL)) {
                    wp_send_json_error(__('Invalid domain.', 'restusre-restrict-users-registration'));
                }
                $domains = call_user_func($get_func, $option_key, array());
                if (in_array($value, $domains)) {
                    wp_send_json_error(__('Domain already blacklisted.', 'restusre-restrict-users-registration'));
                }
                $domains[] = $value;
                RESTUSRE_DB::update_option($option_key, $domains);
                wp_send_json_success(__('Domain added.', 'restusre-restrict-users-registration'));
            }
        } elseif ($action === 'remove') {
            check_ajax_referer( 'restusre_admin_nonce', 'nonce' );
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( __( 'Unauthorized.', 'restusre-restrict-users-registration' ) );
            }
            $value = isset($_POST[$field]) && is_string($_POST[$field]) ? call_user_func($sanitize, $_POST[$field]) : '';
            if (empty($value)) {
                wp_send_json_error(
                    sprintf(
                        /* translators: %s: field name */
                        __('Invalid %s.', 'restusre-restrict-users-registration'),
                        $field
                    )
                );
            }
            if ($is_email) {
                $result = call_user_func($remove_func, $value);
                if ($result) {
                    wp_send_json_success( __( 'Email removed from blacklist.', 'restusre-restrict-users-registration' ) );
                } else {
                    wp_send_json_error( __( 'Failed to remove email.', 'restusre-restrict-users-registration' ) );
                }
            } else {
                $domains = call_user_func($get_func, $option_key, array());
                $domains = array_diff($domains, array($value));
                RESTUSRE_DB::update_option($option_key, array_values($domains));
                wp_send_json_success(__('Domain removed.', 'restusre-restrict-users-registration'));
            }
        }
    }
    /**
     * AJAX: List all blacklisted emails.
     *
     * @return void
     */
    public static function ajax_email_blacklist_list() { self::handle_blacklist_ajax('email', 'list'); }
    /**
     * AJAX: Add an email to the blacklist.
     *
     * @return void
     */
    public static function ajax_email_blacklist_add() { self::handle_blacklist_ajax('email', 'add'); }
    /**
     * AJAX: Remove an email from the blacklist.
     *
     * @return void
     */
    public static function ajax_email_blacklist_remove() { self::handle_blacklist_ajax('email', 'remove'); }
    /**
     * AJAX: List all blacklisted domains.
     *
     * @return void
     */
    public static function ajax_domain_blacklist_list() { self::handle_blacklist_ajax('domain', 'list'); }
    /**
     * AJAX: Add a domain to the blacklist.
     *
     * @return void
     */
    public static function ajax_domain_blacklist_add() { self::handle_blacklist_ajax('domain', 'add'); }
    /**
     * AJAX: Remove a domain from the blacklist.
     *
     * @return void
     */
    public static function ajax_domain_blacklist_remove() { self::handle_blacklist_ajax('domain', 'remove'); }
    /**
     * Activate plugin: set default options.
     *
     * @return void
     */
    public static function activate() {
        RESTUSRE_DB::update_option( 'restusre_general', array( 'enabled' => 1, 'prevent_duplicate_ip' => 0, 'delete_on_deactivate' => 0 ) );
        RESTUSRE_DB::update_option( 'restusre_api', array( 'api_domain' => '', 'username' => '', 'api_key' => '' ) );
        RESTUSRE_DB::update_option( 'restusre_domain_blacklist', array() );
        RESTUSRE_Blacklist::install();
    }
    /**
     * Deactivate plugin: no-op (handled in main file).
     *
     * @return void
     */
    public static function deactivate() {
        // No-op (deletion handled in main plugin file)
    }
    /**
     * AJAX: List all signup activity records.
     *
     * @return void
     */
    public static function ajax_signup_activity_list() {
        global $wpdb;
        check_ajax_referer( 'restusre_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'restusre-restrict-users-registration' ) );
        }
        $table = $wpdb->prefix . 'restusre_ip_signups';
        $rows = $wpdb->get_results( "SELECT id, email_used, ip_address, signup_time FROM $table ORDER BY id DESC", ARRAY_A );
        wp_send_json_success($rows);
    }
    /**
     * AJAX: Remove a signup activity record.
     *
     * @return void
     */
    public static function ajax_signup_activity_remove() {
        global $wpdb;
        check_ajax_referer( 'restusre_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'restusre-restrict-users-registration' ) );
        }
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error(__('Invalid ID.', 'restusre-restrict-users-registration'));
        }
        $table = $wpdb->prefix . 'restusre_ip_signups';
        $wpdb->delete($table, array('id' => $id));
        wp_send_json_success(__('Signup record removed.', 'restusre-restrict-users-registration'));
    }
}
// End of class