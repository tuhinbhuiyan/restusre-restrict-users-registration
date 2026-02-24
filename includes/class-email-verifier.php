<?php
/**
 * Email verification API logic for Restrict Users Registration by EmailVerifierPro.app
 *
 * Handles API requests and response parsing for email verification.
 *
 * @author Tuhin Bhuiyan <https://tuhin.dev>
 * @package RestrictUsersRegistration
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class RESTUSRE_Email_Verifier {
    /**
     * Verify an email address using the configured API.
     *
     * @param string $email
     * @return array
     */
    public static function verify_email( $email ) {
        $api = RESTUSRE_DB::get_option( 'restusre_api', array( 'api_domain' => '', 'username' => '', 'api_key' => '' ) );
        if ( empty( $api['api_domain'] ) || empty( $api['username'] ) || empty( $api['api_key'] ) ) {
            error_log('RESTUSRE DEBUG: API not configured. api=' . print_r($api, true));
            return array( 'error' => __( 'API not configured.', 'restusre-restrict-users-registration' ) );
        }
        $url = trailingslashit( $api['api_domain'] ) . 'api/v?user=' . urlencode( $api['username'] ) . '&api_token=' . urlencode( $api['api_key'] ) . '&verify=' . urlencode( $email );
        $response = wp_remote_get( $url, array('timeout' => 10) );
        error_log('RESTUSRE DEBUG: API request url: ' . $url);
        error_log('RESTUSRE DEBUG: API response: ' . print_r($response, true));
        if ( is_wp_error( $response ) ) {
            return array( 'error' => $response->get_error_message() );
        }
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        // If the response is a non-empty array, use the first element
        if ( is_array($data) && isset($data[0]) && is_array($data[0]) ) {
            $data = $data[0];
        }
        if ( empty( $data ) || ! isset( $data['code'] ) ) {
            return array( 'error' => __( 'Invalid API response.', 'restusre-restrict-users-registration' ) );
        }
        return $data;
    }
}