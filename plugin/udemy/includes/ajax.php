<?php
/**
 * Ajax
 *
 * @package     Udemy\Ajax
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Redeem code ajax callback
 */
function edd_envato_customers_ajax_redeem_code() {

    // Defaults
    $actions = array();

    // Sanitizing form data
    $purchase_code = ( isset ( $_POST['purchase_code'] ) ) ? sanitize_text_field( $_POST['purchase_code'] ) : null;

    if ( isset ( $_POST['form_actions'] ) && is_array( $_POST['form_actions'] ) ) {
        foreach ( $_POST['form_actions'] as $key => $val ) {
            $actions[ $key ] = sanitize_text_field( $val );
        }
    }

    // AJAX Call
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $response = false;

        // ACTION

        /*
         * Prepare feedback
         */
        if ( ! empty ( $feedback_class ) && ! empty ( $feedback_text ) )
            $response = '<span class="' . $feedback_class . '">' . $feedback_text . '</span>';

        // response output
        //header( "Content-Type: application/json" );
        echo $response;
    }

    // IMPORTANT: don't forget to "exit"
    exit;
}
add_action( 'wp_ajax_nopriv_post_edd_envato_customers_redeem_code', 'edd_envato_customers_ajax_redeem_code' );
add_action( 'wp_ajax_post_edd_envato_customers_redeem_code', 'edd_envato_customers_ajax_redeem_code' );