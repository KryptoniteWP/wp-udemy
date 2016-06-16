<?php
/*
 * Load WordPress
 */
require_once( explode( "wp-content" , __FILE__ )[0] . "wp-load.php" );

// Default redirect
$redirect = 'https://udemy.com';

// Check if course was given
if ( isset ( $_GET['course'] ) ) {

    $course = trailingslashit( esc_attr( $_GET['course'] ) );

    $redirect .= '/' . trailingslashit ( $course );
}

// Build affiliate url
if ( function_exists( 'udemy_get_redirect_affiliate_url' ) ) {
    $redirect = udemy_get_redirect_affiliate_url( $redirect, $encode = true );
}

var_dump($redirect);

// Redirect
//wp_redirect( $redirect, 301 );
exit;
