<?php
/**
 * Ask for a plugin review in the WP Admin footer, if we're on our plugin area pages
 *
 * @param $text
 *
 * @return string
 */
function ufwp_admin_footer_text( $text ) {

    if ( ufwp_is_plugin_admin_area() ) {
        $text = sprintf( wp_kses( __( 'If you enjoy using <strong>Online Learning Courses</strong>, please <a href="%s" target="_blank">leave us a ★★★★★ rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance, this helps a lot!', 'wp-udemy' ), array(  'a' => array( 'href' => array() ), 'strong' => array() ) ), esc_url( 'https://wordpress.org/support/view/plugin-reviews/wp-udemy?rate=5#postform' ) );
    };

    return $text;
}
add_filter( 'admin_footer_text', 'ufwp_admin_footer_text' );