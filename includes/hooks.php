<?php
/**
 * Hooks
 *
 * @package     UFWP\Hooks
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Extend body classes
 */
function ufwp_add_body_classes( $classes ) {

    $classes[] = 'ufwp';

    return $classes;
}
//add_filter( 'body_class', 'ufwp_add_body_classes' );

/**
 * Maybe add credits
 */
function ufwp_maybe_add_credits_to_the_content( $content ) {

    if ( ! is_single() && ! is_page() )
        return $content;

    $options = ufwp_get_options();

    $credits = ( isset( $options['credits'] ) && $options['credits'] == '1' ) ? true : false;

    if ( ufwp_has_plugin_content() && $credits ) {

        $credits_url = apply_filters( 'ufwp_credits_url', 'https://wordpress.org/plugins/wp-udemy/' );

        $credits_link = '<a href="' . $credits_url . '" target="_blank" rel="nofollow" title="' . __('Udemy for WordPress', 'wp-udemy') . '">' . __('Udemy for WordPress', 'wp-udemy') . '</a>';

        $content .= '<p><small>' . __('Presentation of the video courses powered by ', 'wp-udemy') . $credits_link . '.</small></p>';
    }

    return $content;
}
add_filter( 'the_content', 'ufwp_maybe_add_credits_to_the_content' );

/**
 * Custom CSS
 */
function ufwp_insert_custom_css() {

    $options = ufwp_get_options();

    $custom_css_activated = ( isset( $options['custom_css_activated'] ) && $options['custom_css_activated'] == '1' ) ? true : false;

    if ( ufwp_has_plugin_content() && $custom_css_activated && ! empty ( $options['custom_css'] ) ) {
        echo '<style type="text/css">' . $options['custom_css'] . '</style>';
    }
}
add_action('wp_head','ufwp_insert_custom_css');