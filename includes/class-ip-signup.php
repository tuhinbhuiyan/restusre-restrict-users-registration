<?php
/**
 * IP signup logging for Restrict Users Registration by EmailVerifierPro.app
 *
 * Handles logging and checking of IP addresses for user signups.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class RESTUSRE_IP_Signup {
    /**
     * Log a signup event for an email and IP address.
     *
     * @param string $email
     * @param string $ip
     */
    public static function log_signup($email, $ip) {
        global $wpdb;
        $table = $wpdb->prefix . 'restusre_ip_signups';
        $wpdb->insert( $table, array(
            'email_used' => sanitize_email($email),
            'ip_address' => sanitize_text_field($ip)
        ) );
    }

    /**
     * Check if an IP address already exists in the log.
     *
     * @param string $ip
     * @return bool
     */
    public static function ip_exists($ip) {
        global $wpdb;
        $table = $wpdb->prefix . 'restusre_ip_signups';
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE ip_address=%s", $ip ) );
        return $exists > 0;
    }

    /**
     * Get the current user's IP address.
     *
     * @return string
     */
    public static function get_ip() {
        foreach ( array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key ) {
            if ( isset($_SERVER[$key]) && !empty($_SERVER[$key]) ) {
                $ip = sanitize_text_field($_SERVER[$key]);
                if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
                // Sanitize and validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return 'unknown';
    }
}