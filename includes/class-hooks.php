<?php
/**
 * Registration hooks and validation for Restrict Users Registration by EmailVerifierPro.app
 *
 * Handles registration validation, WooCommerce hooks, and IP logging.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */


class RESTUSRE_Hooks {
    /**
     * Initialize registration hooks for WordPress and WooCommerce.
     */
    public static function init() {
        add_filter( 'registration_errors', array( __CLASS__, 'validate_email_on_register' ), 10, 3 );
        // Only add WooCommerce hooks if WooCommerce is active
        if ( class_exists( 'WooCommerce' ) ) {
            add_filter( 'woocommerce_registration_errors', array( __CLASS__, 'woocommerce_registration_errors' ), 10, 3 );
            add_action( 'woocommerce_created_customer', array( __CLASS__, 'log_ip_after_woo_register' ), 10, 1 );
        }
        add_action( 'user_register', array( __CLASS__, 'log_ip_after_user_register' ), 10, 1 );
    }
    /**
     * Check if an IP is a duplicate signup.
     *
     * @param string $ip
     * @return string|false
     */
    private static function check_duplicate_ip($ip) {
        if ( RESTUSRE_IP_Signup::ip_exists( $ip ) ) {
            return __( 'Registration from this IP address is not allowed. Only one sign-up per IP.', 'restusre-restrict-users-registration' );
        }
        return false;
    }
    /**
     * Check if an email's domain is blacklisted.
     *
     * @param string $email
     * @return string|false
     */
    private static function check_domain_blacklist($email) {
        $domain = substr( strrchr( $email, "@" ), 1 );
        $domains = RESTUSRE_DB::get_option( 'restusre_domain_blacklist', array() );
        if ( in_array( $domain, $domains ) ) {
            return __( 'Email domain is not allowed to register.', 'restusre-restrict-users-registration' );
        }
        return false;
    }
    /**
     * Check if an email is blacklisted.
     *
     * @param string $email
     * @return string|false
     */
    private static function check_email_blacklisted($email) {
        if ( RESTUSRE_Blacklist::is_blacklisted( $email ) ) {
            return __( 'This email address is blocked from registering.', 'restusre-restrict-users-registration' );
        }
        return false;
    }
    /**
     * Get the reason for an invalid email from API result.
     *
     * @param array $result
     * @return string
     */
    private static function get_email_invalid_reason($result) {
        return isset( $result['error'] ) ? $result['error'] : ( isset( $result['message'] ) ? $result['message'] : __( 'Your email address could not be verified.', 'restusre-restrict-users-registration' ) );
    }
    /**
     * Get the status from API result.
     *
     * @param array $result
     * @return string|null
     */
    private static function get_status_from_result($result) {
        if (isset($result['status'])) return $result['status'];
        if (isset($result['response']['status'])) return $result['response']['status'];
        return null;
    }
    /**
     * Validate email on WordPress registration.
     *
     * @param WP_Error $errors
     * @param string $sanitized_user_login
     * @param string $user_email
     * @return WP_Error
     */
    public static function validate_email_on_register( $errors, $sanitized_user_login, $user_email ) {
        $general = RESTUSRE_DB::get_option( 'restusre_general', array( 'enabled' => 1, 'prevent_duplicate_ip' => 0 ) );
        if ( empty( $general['enabled'] ) ) return $errors;
        if ( ! empty( $general['prevent_duplicate_ip'] ) ) {
            $ip = RESTUSRE_IP_Signup::get_ip();
            $ip_error = self::check_duplicate_ip($ip);
            if ( $ip_error ) {
                $errors->add( 'duplicate_ip', $ip_error );
                return $errors;
            }
        }
        $email_blacklisted_msg = self::check_email_blacklisted($user_email);
        if ( $email_blacklisted_msg ) {
            $errors->add( 'email_blacklisted', $email_blacklisted_msg );
            return $errors;
        }
        $domain_error = self::check_domain_blacklist($user_email);
        if ( $domain_error ) {
            $errors->add( 'domain_blacklisted', $domain_error );
            return $errors;
        }
        $result = RESTUSRE_Email_Verifier::verify_email( $user_email );
        $invalid_statuses = array('invalid', 'catch all', 'catchall', 'unknown', 'skipped');
        $status = self::get_status_from_result($result);
        if ( isset( $result['code'] ) && $result['code'] == 200 && $status && !in_array( strtolower($status), $invalid_statuses ) ) {
            // Do not log IP here; handled after user creation
            return $errors;
        }
        $reason = self::get_email_invalid_reason($result);
        // Track invalid attempts and auto-blacklist if needed
        RESTUSRE_Blacklist::maybe_blacklist_on_invalid($user_email);
        $errors->add( 'email_invalid', $reason );
        return $errors;
    }
    /**
     * Validate email on WooCommerce registration.
     *
     * @param WP_Error $validation_errors
     * @param string $username
     * @param string $email
     * @return WP_Error
     */
    public static function woocommerce_registration_errors( $validation_errors, $username, $email ) {
        $general = RESTUSRE_DB::get_option( 'restusre_general', array( 'enabled' => 1, 'prevent_duplicate_ip' => 0 ) );
        if ( empty( $general['enabled'] ) ) return $validation_errors;
        if ( ! empty( $general['prevent_duplicate_ip'] ) ) {
            $ip = RESTUSRE_IP_Signup::get_ip();
            $ip_error = self::check_duplicate_ip($ip);
            if ( $ip_error ) {
                $validation_errors->add( 'duplicate_ip', $ip_error );
                return $validation_errors;
            }
        }
        $email_blacklisted_msg = self::check_email_blacklisted($email);
        if ( $email_blacklisted_msg ) {
            $validation_errors->add( 'email_blacklisted', $email_blacklisted_msg );
            return $validation_errors;
        }
        $domain_error = self::check_domain_blacklist($email);
        if ( $domain_error ) {
            $validation_errors->add( 'domain_blacklisted', $domain_error );
            return $validation_errors;
        }
        $result = RESTUSRE_Email_Verifier::verify_email( $email );
        $invalid_statuses = array('invalid', 'catch all', 'catchall', 'unknown', 'skipped');
        $status = self::get_status_from_result($result);
        if ( isset( $result['code'] ) && $result['code'] == 200 && $status && !in_array( strtolower($status), $invalid_statuses ) ) {
            // Do not log IP here; handled after user creation
            return $validation_errors;
        }
        $reason = self::get_email_invalid_reason($result);
        // Track invalid attempts and auto-blacklist if needed
        RESTUSRE_Blacklist::maybe_blacklist_on_invalid($email);
        $validation_errors->add( 'email_invalid', $reason );
        return $validation_errors;
    }
    /**
     * Log IP after user_register (WordPress core).
     *
     * @param int $user_id
     */
    public static function log_ip_after_user_register( $user_id ) {
        $general = RESTUSRE_DB::get_option( 'restusre_general', array( 'enabled' => 1, 'prevent_duplicate_ip' => 0 ) );
        if ( empty( $general['enabled'] ) || empty( $general['prevent_duplicate_ip'] ) ) return;
        // Only log for non-WooCommerce registrations
        if ( defined('WC_VERSION') && did_action('woocommerce_created_customer') ) return;
        $user = get_userdata( $user_id );
        if ( $user ) {
            RESTUSRE_IP_Signup::log_signup( $user->user_email, RESTUSRE_IP_Signup::get_ip() );
        }
    }
    /**
     * Log IP after WooCommerce registration.
     *
     * @param int $customer_id
     */
    public static function log_ip_after_woo_register( $customer_id ) {
        $general = RESTUSRE_DB::get_option( 'restusre_general', array( 'enabled' => 1, 'prevent_duplicate_ip' => 0 ) );
        if ( empty( $general['enabled'] ) || empty( $general['prevent_duplicate_ip'] ) ) return;
        // Prevent duplicate log if already logged in user_register
        if ( did_action('user_register') ) return;
        $user = get_userdata( $customer_id );
        if ( $user ) {
            RESTUSRE_IP_Signup::log_signup( $user->user_email, RESTUSRE_IP_Signup::get_ip() );
        }
    }
}