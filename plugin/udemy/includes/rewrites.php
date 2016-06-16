<?php
/**
 * Rewrites
 *
 * @package     Udemy\Rewrites
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Add custom rewrite rules
 */
function udemy_add_rewrite_rules() {

    global $wp_rewrite;

    $slug = udemy_get_rewrite_slug();

    $new_non_wp_rules = array(
        $slug . '/(.*)'    => 'wp-content/plugins/udemy/public/redirect.php?course=$1',
    );

    $wp_rewrite->non_wp_rules += $new_non_wp_rules;
}
add_action('generate_rewrite_rules', 'udemy_add_rewrite_rules');

/*
 * Return rewrite slug
 */
function udemy_get_rewrite_slug() {

    $default_slug = 'udemy';

    $slug = apply_filters( 'udemy_rewrite_slug', $default_slug );

    return str_replace('/', '', $slug); // Remove slashes if accidentally added
}