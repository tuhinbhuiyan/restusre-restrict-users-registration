<?php
/**
 * Email blacklist logic for Restrict Users Registration by EmailVerifierPro.app
 *
 * Handles blacklisting, whitelisting, and invalid attempt tracking for emails.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class RESTUSRE_Blacklist {
    const OPTION_KEY = 'restusre_email_blacklist';
    const ATTEMPTS_KEY = 'restusre_email_invalid_attempts';
    /**
     * Install blacklist and attempts options if not present.
     */
    public static function install() {
        if ( RESTUSRE_DB::get_option( self::OPTION_KEY ) === null ) {
            RESTUSRE_DB::update_option( self::OPTION_KEY, array() );
        }
        if ( RESTUSRE_DB::get_option( self::ATTEMPTS_KEY ) === null ) {
            RESTUSRE_DB::update_option( self::ATTEMPTS_KEY, array() );
        }
    }
    /**
     * Add an email to the blacklist.
     *
     * @param string $email
     * @param string $status
     */
    public static function add( $email, $status = 'invalid' ) {
        $emails = RESTUSRE_DB::get_option( self::OPTION_KEY, array() );
        $emails[ sanitize_email( $email ) ] = array(
            'status' => $status,
            'timestamp' => time(),
        );
        RESTUSRE_DB::update_option( self::OPTION_KEY, $emails );
    }
    /**
     * Remove an email from the blacklist.
     *
     * @param string $email
     */
    public static function remove( $email ) {
        $emails = RESTUSRE_DB::get_option( self::OPTION_KEY, array() );
        unset( $emails[ sanitize_email( $email ) ] );
        RESTUSRE_DB::update_option( self::OPTION_KEY, $emails );
    }
    /**
     * Get all blacklisted emails, optionally filtered by status.
     *
     * @param string $status
     * @return array
     */
    public static function get_all( $status = '' ) {
        $emails = RESTUSRE_DB::get_option( self::OPTION_KEY, array() );
        if ( $status ) {
            return array_filter( $emails, function($data) use ($status) {
                return $data['status'] === $status;
            });
        }
        return $emails;
    }
    /**
     * Process a blacklist action (block/approve).
     *
     * @param string $email
     * @param string $action
     * @return bool
     */
    public static function process_action( $email, $action ) {
        if ( $action === 'block' ) {
            self::add( $email, 'invalid' );
            return true;
        } elseif ( $action === 'approve' ) {
            self::remove( $email );
            return true;
        }
        return false;
    }
    /**
     * Check if an email is blacklisted.
     *
     * @param string $email
     * @return bool
     */
    public static function is_blacklisted( $email ) {
        $emails = RESTUSRE_DB::get_option( self::OPTION_KEY, array() );
        return isset( $emails[ sanitize_email( $email ) ] );
    }
    /**
     * Get all blacklisted emails as array for UI.
     *
     * @return array
     */
    public static function get_emails() {
        $emails = self::get_all();
        $result = array();
        foreach ($emails as $email => $data) {
            $result[] = array(
                'email' => $email,
                'status' => isset($data['status']) ? $data['status'] : 'invalid',
                'added' => isset($data['timestamp']) ? gmdate('Y-m-d H:i', $data['timestamp']) : '',
            );
        }
        return $result;
    }
    /**
     * Add an email to the blacklist (UI/AJAX).
     *
     * @param string $email
     * @return bool
     */
    public static function add_email($email) {
        $email = strtolower(trim($email));
        if (!is_email($email)) return false;
        $emails = RESTUSRE_DB::get_option(self::OPTION_KEY, array());
        $key = sanitize_email($email);
        if (isset($emails[$key])) return true; // Already exists, treat as success
        $emails[$key] = array('status' => 'invalid', 'timestamp' => gmdate('Y-m-d H:i:s'));
        RESTUSRE_DB::update_option(self::OPTION_KEY, $emails);
        return true;
    }
    /**
     * Remove an email from the blacklist (UI/AJAX).
     *
     * @param string $email
     * @return bool
     */
    public static function remove_email($email) {
        $emails = RESTUSRE_DB::get_option(self::OPTION_KEY, array());
        $key = strtolower(trim(sanitize_email($email)));
        if (!isset($emails[$key])) return false;
        unset($emails[$key]);
        RESTUSRE_DB::update_option(self::OPTION_KEY, $emails);
        return true;
    }
    /**
     * Record an invalid attempt for an email.
     *
     * @param string $email
     * @return int
     */
    public static function record_invalid_attempt($email) {
        $email = sanitize_email($email);
        $attempts = RESTUSRE_DB::get_option(self::ATTEMPTS_KEY, array());
        if (!isset($attempts[$email])) {
            $attempts[$email] = 1;
        } else {
            $attempts[$email]++;
        }
        RESTUSRE_DB::update_option(self::ATTEMPTS_KEY, $attempts);
        return $attempts[$email];
    }
    /**
     * Reset invalid attempts for an email.
     *
     * @param string $email
     */
    public static function reset_invalid_attempts($email) {
        $email = sanitize_email($email);
        $attempts = RESTUSRE_DB::get_option(self::ATTEMPTS_KEY, array());
        if (isset($attempts[$email])) {
            unset($attempts[$email]);
            RESTUSRE_DB::update_option(self::ATTEMPTS_KEY, $attempts);
        }
    }
    /**
     * Blacklist email if invalid attempts exceed limit.
     *
     * @param string $email
     * @return bool
     */
    public static function maybe_blacklist_on_invalid($email) {
        $general = RESTUSRE_DB::get_option('restusre_general', array('invalid_retry_limit' => 3));
        $limit = isset($general['invalid_retry_limit']) ? intval($general['invalid_retry_limit']) : 3;
        $count = self::record_invalid_attempt($email);
        if ($count >= $limit) {
            self::add($email, 'invalid');
            self::reset_invalid_attempts($email);
            return true;
        }
        return false;
    }
}