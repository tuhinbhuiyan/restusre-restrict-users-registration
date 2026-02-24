<?php
/**
 * Database logic for Restrict Users Registration by EmailVerifierPro.app
 *
 * Handles plugin custom tables and option storage.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class RESTUSRE_DB {
    private static function ensure_options_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'restusre_options';
        // Check if table exists
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) != $table ) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table (
                option_key varchar(100) NOT NULL,
                option_value longtext NOT NULL,
                PRIMARY KEY  (option_key)
            ) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }
    }
    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $options_table = $wpdb->prefix . 'restusre_options';
        $ips_table = $wpdb->prefix . 'restusre_ip_signups';
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $sql1 = "CREATE TABLE $options_table (
            option_key varchar(100) NOT NULL,
            option_value longtext NOT NULL,
            PRIMARY KEY  (option_key)
        ) $charset_collate;";
        dbDelta($sql1);
        $sql2 = "CREATE TABLE $ips_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email_used varchar(255) NOT NULL,
            ip_address varchar(100) NOT NULL,
            signup_time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ip_address (ip_address)
        ) $charset_collate;";
        dbDelta($sql2);
    }
    /**
     * Get a plugin option from the custom table.
     * Always returns $default if not found.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get_option($key, $default = null) {
        self::ensure_options_table();
        global $wpdb;
        $table = $wpdb->prefix . 'restusre_options';
        $val = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $table WHERE option_key=%s", $key ) );
        if ( $val === null ) return $default;
        return maybe_unserialize( $val );
    }
    /**
     * Update a plugin option in the custom table.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function update_option($key, $value) {
        self::ensure_options_table();
        global $wpdb;
        $table = $wpdb->prefix . 'restusre_options';
        $value = maybe_serialize($value);
        if ( self::get_option($key, null) === null ) {
            $wpdb->insert( $table, array( 'option_key' => $key, 'option_value' => $value ) );
        } else {
            $wpdb->update( $table, array( 'option_value' => $value ), array( 'option_key' => $key ) );
        }
    }
    /**
     * Delete all plugin data (tables and options).
     * Used if user requests full removal on deactivation.
     */
    public static function delete_all_plugin_data() {
        global $wpdb;
        $options_table = $wpdb->prefix . 'restusre_options';
        $ips_table = $wpdb->prefix . 'restusre_ip_signups';
        $wpdb->query( "DROP TABLE IF EXISTS $options_table" );
        $wpdb->query( "DROP TABLE IF EXISTS $ips_table" );
        delete_option('restusre_general');
        delete_option('restusre_api');
        delete_option('restusre_domain_blacklist');
        delete_option('restusre_email_blacklist');
    }
}