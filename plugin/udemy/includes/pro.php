<?php
/*
 * Custom templating
 */
function udemy_pro_get_template_file( $template_file, $template, $type ) {

    // Check theme folder
    if ( $custom_template_file = locate_template( array( 'udemy/' . $template . '.php' ) ) ) {
        return $custom_template_file;
    }

    return $template_file;
}
add_filter( 'udemy_template_file', 'udemy_pro_get_template_file', 10, 3 );

/*
 * Settings: Quickstart additions
 */
function udemy_pro_settings_quickstart_additions() {

    ?>

    <p>
        <strong><?php _e( 'Affiliate Links', 'udemy-wp' ); ?></strong><br />
        <?php _e( 'Using affiliate links can be done in two ways: Automatically by entering your publisher ID and selecting the affiliate link type or passing the affiliate link to each shortcode individually. Of course the second method does not work with generated lists.', 'udemy-wp' ); ?>
    </p>
    <p>
        <code>[udemy id="480986" link="https://domain.com/my-affiliate-link/"]</code>
    </p>

    <?php
}
add_action( 'udemy_settings_quickstart_render', 'udemy_pro_settings_quickstart_additions' );

/*
 * Add affiliate links settings field
 */
function udemy_pro_add_affiliate_links_settings_field() {
    
    add_settings_field(
        'udemy_affiliate_links',
        __('Affiliate', 'udemy-pro'),
        'udemy_affiliate_links_settings_field_render',
        'udemy',
        'udemy_general'
    );
}
add_action( 'udemy_settings_general_register', 'udemy_pro_add_affiliate_links_settings_field', 10 );

/*
 * Render affiliate links settings field
 */
function udemy_affiliate_links_settings_field_render() {

    $options = udemy_get_options();

    $link_types = array(
        'disabled' => __('Disabled', 'udemy-pro'),
        'standard' => __('Standard', 'udemy-pro'),
        'masked' => __('Masked by plugin', 'udemy-pro')
    );

    $links = ( isset ( $options['affiliate_links'] ) ) ? $options['affiliate_links'] : 'disabled';
    $publisher_id = ( !empty($options['affiliate_publisher_id'] ) ) ? esc_attr( trim( $options['affiliate_publisher_id'] ) ) : '';

    ?>

    <p>
        <?php printf( wp_kses( __( 'Information about how to join the affiliate program can be found <a href="%s">here</a>.', 'udemy-pro' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( 'https://coder.flowdee.de/docs/article/udemy-for-wordpress/#affiliate-program' ) ); ?>
    </p>

    <br />

    <h4 style="margin: 5px 0"><?php _e('Affiliate Links', 'udemy-pro'); ?></h4>
    <p>
        <select id="udemy_affiliate_links" name="udemy[affiliate_links]">
            <?php foreach ( $link_types as $key => $label ) { ?>
                <option value="<?php echo $key; ?>" <?php selected( $links, $key ); ?>><?php echo $label; ?></option>
            <?php } ?>
        </select>
    </p>

    <p>
        <small>
            <?php _e('After switching over to masked links you might have to update your permalinks.', 'udemy-pro'); ?>
        </small>
    </p>

    <br />

    <h4 style="margin: 5px 0"><?php _e('Publisher ID', 'udemy-pro'); ?> <span class="req">*</span></h4>
    <p>
        <input type='text' name='udemy[affiliate_publisher_id]' id="udemy_affiliate_publisher_id"
               value='<?php echo esc_attr( trim( $publisher_id ) ); ?>' style="width: 125px;">
        <span><?php _e('e.g.', 'udemy-pro'); ?> <code>rAHrr6IQKiQ</code></span>
    </p>

    <h4><?php _e('Comparison of the different affiliate links', 'udemy-pro'); ?></h4>
    <table class="widefat udemy-settings-table udemy-settings-table--slim">
        <thead>
        <tr>
            <th><?php _e('Type', 'udemy-pro'); ?></th>
            <th><?php _e('Example', 'udemy-pro'); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php _e('Standard', 'udemy-pro'); ?></td>
            <td style="word-break: break-all;"><a href="http://click.linksynergy.com/deeplink?id=rAHrr6IQKiQ&type=10&mid=39197&murl=https%3A%2F%2Fwww.udemy.com%2Funofficial-udemy-launch-become-a-bestselling-instructor%2F" target="_blank" rel="nofollow">http://click.linksynergy.com/deeplink?id=rAHrr6IQKiQ&type=10&mid=39197&murl=https%3A%2F%2Fwww.udemy.com%2Funofficial-udemy-launch-become-a-bestselling-instructor%2F</a></td>
        </tr>
        <tr class="alternate">
            <?php $rewrite_slug = udemy_get_rewrite_slug(); ?>
            <td><?php _e('Masked by plugin', 'udemy-pro'); ?></td>
            <td style="word-break: break-all;"><a href="<?php echo get_bloginfo('url'); ?>/<?php echo $rewrite_slug; ?>/unofficial-udemy-launch-become-a-bestselling-instructor/" target="_blank" rel="nofollow"><?php echo get_bloginfo('url'); ?>/<?php echo $rewrite_slug; ?>/unofficial-udemy-launch-become-a-bestselling-instructor/</a></td>
        </tr>
        <tr>
            <td><?php _e('Masked via bit.ly', 'udemy-pro'); ?><br /><small style="font-weight: bold; color: cornflowerblue;"><?php _e('Coming soon!', 'udemy-pro'); ?></small></td>
            <td style="word-break: break-all;"><a href="http://bit.ly/1tyo0Oi" target="_blank" rel="nofollow">http://bit.ly/1tyo0Oi</a></td>
        </tr>
        </tbody>
    </table>

    <p>
        <small>
            <?php printf( wp_kses( __( 'More information about all types of affiliate links can be found <a href="%s">here</a>.', 'udemy-pro' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( 'https://coder.flowdee.de/docs/article/udemy-for-wordpress/#affiliate-links' ) ); ?>
        </small>
    </p>

    <?php
}

/*
 * Settings: Validate input
 */
function udemy_pro_settings_validate_input( $input) {

    // Affiliate Links
    if ( empty ( $input['affiliate_publisher_id'] ) && isset ( $input['affiliate_links'] ) && $input['affiliate_links'] != 'disabled' ) {
        $input['affiliate_links'] = 'disabled';
    }

    return $input;
}
add_filter( 'udemy_settings_validate_input', 'udemy_pro_settings_validate_input' );

/*
 * Maybe use affiliate url for courses
 */
function udemy_pro_affiliate_course_url( $url, $basic_url ) {

    $options = udemy_get_options();

    if ( ( isset ( $options['affiliate_links'] ) && $options['affiliate_links'] != 'disabled' ) ) {

        if ( 'standard' === $options['affiliate_links'] ) {
            $url = udemy_get_course_affiliate_url( $url );

        } elseif ( 'masked' === $options['affiliate_links'] ) {

            $rewrite_slug = udemy_get_rewrite_slug();

            if ( ! empty ( $rewrite_slug ) )
                $url = get_bloginfo( 'url' ) . '/' . $rewrite_slug . $basic_url;
        }

        // TODO: Bit.ly
    }

    return $url;
}
add_filter( 'udemy_course_url', 'udemy_pro_affiliate_course_url', 20, 2 );

/*
 * Shortcode output args
 */
function udemy_pro_shortcode_output_args( $output_args, $atts ) {

    // Link
    if ( isset( $output_args['type'] ) && 'list' != $output_args['type'] && isset( $output_args['items'] ) && $output_args['items'] == '1' && isset ( $atts['url'] ) ) {
        $output_args['url'] = $atts['url'];
    }

    return $output_args;
}
add_filter( 'udemy_shortcode_output_args', 'udemy_pro_shortcode_output_args', 10, 3 );

/*
 * Add click tracking settings field
 */
function udemy_pro_add_click_tracking_settings_field() {

    add_settings_field(
        'udemy_click_tracking',
        __('Click Tracking', 'udemy-pro'),
        'udemy_pro_click_tracking_settings_field_render',
        'udemy',
        'udemy_general',
        array('label_for' => 'udemy_click_tracking')
    );
}
add_action( 'udemy_settings_general_register', 'udemy_pro_add_click_tracking_settings_field', 11 );

function udemy_pro_click_tracking_settings_field_render() {

    $options = udemy_get_options();

    $click_tracking = ( isset ( $options['click_tracking'] ) && $options['click_tracking'] == '1' ) ? 1 : 0;
    ?>

    <p>
        <input type="checkbox" id="udemy_click_tracking" name="udemy[click_tracking]" value="1" <?php echo($click_tracking == 1 ? 'checked' : ''); ?>>
        <label for="udemy_click_tracking"><?php _e('Activate in order to track clicks on Udemy links by creating events via your favorite tracking tool. ', 'udemy-pro'); ?></label>
    </p>

    <p><small><strong><?php _e('Note:', 'udemy-pro'); ?></strong> <?php _e('Currently supported:', 'udemy-pro'); ?> Google Analytics & Piwik. <?php _e('In case you created custom templates, please take a look into the documentation.', 'udemy-pro'); ?></small></p>
    <?php
}