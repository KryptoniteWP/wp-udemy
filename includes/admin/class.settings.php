<?php
/**
 * Settings
 *
 * @package     UFWP\Settings
 * @since       1.0.0
 */


// Exit if accessed directly
if (!defined('ABSPATH')) exit;


if (!class_exists('UFWP_Settings')) {

    class UFWP_Settings
    {
        public $options;

        private $checks = true;

        private $curl;
        private $php;

        public function __construct()
        {
            // Variables
            $this->options = ufwp_get_options();

            // Checks
            $this->curl = $this->check_curl();

            // Initialize
            add_action('admin_menu', array( &$this, 'add_admin_menu') );
            add_action('admin_init', array( &$this, 'init_settings') );
        }

        function add_admin_menu()
        {

            add_options_page(
                __('UFWP Courses', 'wp-udemy'),
                __('UFWP Courses', 'wp-udemy'),
                'manage_options',
                'wp-udemy',
                array( &$this, 'options_page' )
            );

        }

        function init_settings()
        {
            register_setting(
                'ufwp_settings',
                'ufwp_settings',
                array( &$this, 'validate_input_callback' )
            );

            // SECTION: Quickstart
            add_settings_section(
                'ufwp_quickstart',
                __('Quickstart Guide', 'wp-udemy'),
                array( &$this, 'section_quickstart_render' ),
                'ufwp_settings'
            );

            /*
             * Action to add more settings right after the quickstart
             */
            do_action( 'ufwp_settings_register' );

            // SECTION: General
            add_settings_section(
                'ufwp_settings_general',
                __('General Settings', 'wp-udemy'),
                false,
                'ufwp_settings'
            );

            add_settings_field(
                'ufwp_api_client',
                __('API', 'wp-udemy'),
                array(&$this, 'api_client_render'),
                'ufwp_settings',
                'ufwp_settings_general'
            );

            add_settings_field(
                'ufwp_cache_duration',
                __('Cache Duration', 'wp-udemy'),
                array(&$this, 'cache_duration_render'),
                'ufwp_settings',
                'ufwp_settings_general'
            );

            /*
             * Action to add more settings within this section
             */
            do_action( 'ufwp_settings_general_register' );

            // SECTION: Output
            add_settings_section(
                'ufwp_settings_output',
                __('Output Settings', 'wp-udemy'),
                false,
                'ufwp_settings'
            );

            add_settings_field(
                'ufwp_default_templates',
                __('Standard Templates', 'wp-udemy'),
                array(&$this, 'default_templates_render'),
                'ufwp_settings',
                'ufwp_settings_output'
            );

            add_settings_field(
                'ufwp_course_details',
                __('Course Details', 'wp-udemy'),
                array(&$this, 'course_details_render'),
                'ufwp_settings',
                'ufwp_settings_output',
                array('label_for' => 'ufwp_course_details')
            );

            add_settings_field(
                'ufwp_custom_css',
                __('Custom CSS', 'wp-udemy'),
                array(&$this, 'custom_css_render'),
                'ufwp_settings',
                'ufwp_settings_output',
                array('label_for' => 'ufwp_custom_css')
            );

            /*
             * Action to add more settings within this section
             */
            do_action( 'ufwp_settings_output_register' );

            // SECTION: Debug
            add_settings_section(
                'ufwp_settings_other',
                __('Other Settings', 'wp-udemy'),
                false,
                'ufwp_settings'
            );

            add_settings_field(
                'ufwp_widget_text_shortcodes',
                __('Widgets & Shortcodes', 'wp-udemy'),
                array(&$this, 'widget_text_shortcodes_render'),
                'ufwp_settings',
                'ufwp_settings_other',
                array('label_for' => 'ufwp_widget_text_shortcodes')
            );

            add_settings_field(
                'ufwp_credits',
                __('You love this plugin?', 'wp-udemy'),
                array(&$this, 'credits_render'),
                'ufwp_settings',
                'ufwp_settings_other',
                array('label_for' => 'ufwp_credits')
            );

            add_settings_field(
                'ufwp_developer_mode',
                __('Developer Mode', 'wp-udemy'),
                array(&$this, 'developer_mode_render'),
                'ufwp_settings',
                'ufwp_settings_other',
                array('label_for' => 'ufwp_developer_mode')
            );

            if ( UFWP_DEBUG ) {

                add_settings_field(
                    'ufwp_debug_information',
                    __('Debug Information', 'wp-udemy'),
                    array(&$this, 'debug_information_render'),
                    'ufwp_settings',
                    'ufwp_settings_other'
                );
            }

            /*
             * Action to add more settings within this section
             */
            do_action( 'ufwp_settings_debug_register' );
        }

        function validate_input_callback( $input ) {

            //ufwp_debug($input);

            $validation = ( isset ( $this->options['api_status'] ) ) ? $this->options['api_status'] : false;
            $error = ( isset ( $this->options['api_error'] ) ) ? $this->options['api_error'] : '';

            if ( ! empty ( $input['api_client_id'] ) && ! empty ( $input['api_client_password'] ) ) {

                $api_client_id = ( isset ( $this->options['api_client_id'] ) ) ? $this->options['api_client_id'] : '';
                $api_client_id_new = $input['api_client_id'];

                $api_client_password = ( isset ( $this->options['api_client_password'] ) ) ? $this->options['api_client_password'] : '';
                $api_client_password_new = $input['api_client_password'];

                if ( $api_client_id_new != $api_client_id || $api_client_password_new != $api_client_password ) {

                    $result = ufwp_validate_api_credentials( $api_client_id_new, $api_client_password_new );

                    $validation = ( ! empty ( $result['status'] ) ) ? true : false;
                    $error = ( ! empty ( $result['error'] ) ) ? $result['error'] : '';
                }
            }

            $input['api_status'] = $validation;
            $input['api_error'] = $error;

            // Handle cache deletion
            if ( isset ( $input['delete_cache'] ) && $input['delete_cache'] === '1' ) {
                ufwp_delete_cache();
                $input['delete_cache'] = '0';
            }

            // Handle cache deletion
            if ( isset ( $input['reset_log'] ) && $input['reset_log'] === '1' ) {
                delete_option('ufwp_log');
                $input['reset_log'] = '0';
            }

            $input = apply_filters( 'ufwp_settings_validate_input', $input );

            return $input;
        }

        function section_quickstart_render() {
            ?>

            <div class="postbox">
                <h3 class='hndle'><?php _e('Quickstart Guide', 'wp-udemy'); ?></h3>
                <div class="inside">
                    <p><?php _e( 'There are two ways of displaying courses:', 'wp-udemy' ); ?></p>
                    <p>
                        <strong><?php _e( 'Single course by ID', 'wp-udemy' ); ?></strong><br />
                        <?php _e( 'In order to get the course ID, simply add the course to the cart and take the ID out of the url of your browser.', 'wp-udemy' ); ?>
                    </p>
                    <p>
                        <code>[ufwp id="480986"]</code>
                    </p>

                    <p>
                        <strong><?php _e( 'Search for courses', 'wp-udemy' ); ?></strong><br />
                        <?php _e('Alternatively you can search for courses and display grids or lists of multiple courses.', 'wp-udemy'); ?> <span style="color: darkorange; font-weight: bold;"><?php _e( 'This feature requires API keys!', 'wp-udemy' ); ?></span>
                    <p>
                        <code>[ufwp search="css" items="6" template="grid" grid="3"]</code> <?php _e( 'or', 'wp-udemy' ); ?> <code>[ufwp search="html" items="6" template="list"]</code>
                    </p>

                    <p><?php printf( wp_kses( __( 'Please take a look into the <a href="%s">documentation</a> for more options.', 'wp-udemy' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( 'https://coder.flowdee.de/docs/article/wp-udemy/' ) ); ?></p>

                    <?php do_action( 'ufwp_settings_quickstart_render' ); ?>
                </div>
            </div>

            <?php
        }

        function api_client_render() {

            $api_client_id = ( !empty($this->options['api_client_id'] ) ) ? esc_attr( trim( $this->options['api_client_id'] ) ) : '';
            $api_client_password = ( !empty($this->options['api_client_password'] ) ) ? esc_attr( trim($this->options['api_client_password'] ) ) : '';

            ?>
            <h4 style="margin: 5px 0"><?php _e('Status', 'wp-udemy'); ?></h4>
            <?php if ( ! empty( $api_client_id ) && ! empty( $api_client_password ) ) { ?>
                <?php $this->api_status_render(); ?>
            <?php } else { ?>
                <span style="color: dodgerblue;"><?php _e("API credentials are currently only required when searching courses or displaying categories.", 'wp-udemy'); ?></span>
            <?php } ?>

            <h4 style="margin-bottom: 5px"><?php _e('Client ID', 'wp-udemy'); ?></h4>
            <input type='text' name='ufwp_settings[api_client_id]' id="ufwp_api_client_id"
                   value='<?php echo esc_attr( trim( $api_client_id ) ); ?>' style="width: 350px;">

            <h4 style="margin: 15px 0 5px 0;"><?php _e('Client Password', 'wp-udemy'); ?></h4>
            <input type='text' name='ufwp_settings[api_client_password]' id="ufwp_api_client_password"
                   value='<?php echo esc_attr( trim( $api_client_password ) ); ?>' style="width: 350px;">

            <p>
                <small>
                    <?php printf( wp_kses( __( 'Before entering your API credentials you have to create a new API Client <a href="%s">here</a>.', 'wp-udemy' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( 'https://www.udemy.com/user/edit-api-clients/' ) ); ?>
                </small>
            </p>
            <?php
        }

        function cache_duration_render() {

            $cache_durations = array(
                '360' => __('6 Hours', 'wp-udemy'),
                '720' => __('12 Hours', 'wp-udemy'),
                '1440' => __('1 Day', 'wp-udemy'),
                '4320' => __('3 Days', 'wp-udemy'),
                '10080' => __('1 Week', 'wp-udemy'),
            );

            $cache_duration = ( isset ( $this->options['cache_duration'] ) ) ? $this->options['cache_duration'] : '1440';

            ?>
            <select id="ufwp_cache_duration" name="ufwp_settings[cache_duration]">
                <?php foreach ( $cache_durations as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $cache_duration, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <input type="hidden" id="ufwp_delete_cache" name="ufwp_settings[delete_cache]" value="0" />
            <?php
        }

        function default_templates_render() {

            $templates = array(
                'standard' => __('Standard', 'wp-udemy'),
                'grid' => __('Grid', 'wp-udemy'),
                'list' => __('List', 'wp-udemy')
            );

            $template_course = ( isset ( $this->options['template_course'] ) ) ? $this->options['template_course'] : 'standard';
            $template_courses = ( isset ( $this->options['template_courses'] ) ) ? $this->options['template_courses'] : 'list';

            ?>
            <h4 style="margin: 5px 0;"><?php _e('Single Course', 'wp-udemy'); ?></h4>
            <p>
                <select id="ufwp_template_course" name="ufwp_settings[template_course]">
                    <?php foreach ( $templates as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $template_course, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <br />

            <h4 style="margin: 5px 0;"><?php _e('Multiple Courses', 'wp-udemy'); ?></h4>
            <p>
                <select id="ufwp_template_courses" name="ufwp_settings[template_courses]">
                    <?php foreach ( $templates as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $template_courses, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <br />

            <p><?php printf( esc_html__( 'Available templates (%1$s) can be used to overwrite each shortcode individually: e.g.', 'wp-udemy' ), 'standard, grid, list' ); ?> <code>[ufwp id="1234,6789" template="list"]</code></p>
            <p></p>
            <?php
        }

        function course_details_render() {

            $course_details_options = array(
                'course' => __('Course Subtitle', 'wp-udemy'),
                'instructor' => __('Instructor information', 'wp-udemy'),
            );

            $course_details = ( isset ( $this->options['course_details'] ) ) ? $this->options['course_details'] : 'course';

            ?>
            <select id="ufwp_course_details" name="ufwp_settings[course_details]">
                <?php foreach ( $course_details_options as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $course_details, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <p><small><?php _e('This will be applied to grid and list templates. The standard template already shows both information.', 'wp-udemy'); ?></small></p>

            <?php $course_meta = ( isset ( $this->options['course_meta'] ) && $this->options['course_meta'] == '1' ) ? 1 : 0; ?>
            <p>
                <input type="checkbox" id="ufwp_course_meta" name="ufwp_settings[course_meta]" value="1" <?php echo($course_meta == 1 ? 'checked' : ''); ?>>
                <label for="ufwp_course_meta"><?php _e('Show lectures and playing time', 'wp-udemy'); ?></label>
            </p>
            <?php
        }

        function custom_css_render() {
            
            $custom_css_activated = ( isset ( $this->options['custom_css_activated'] ) && $this->options['custom_css_activated'] == '1' ) ? 1 : 0;
            $custom_css = ( !empty ( $this->options['custom_css'] ) ) ? $this->options['custom_css'] : '';
            ?>

            <p>
                <input type="checkbox" id="ufwp_custom_css_activated" name="ufwp_settings[custom_css_activated]" value="1" <?php echo($custom_css_activated == 1 ? 'checked' : ''); ?>>
                <label for="ufwp_custom_css_activated"><?php _e('Output custom CSS styles', 'wp-udemy'); ?></label>
            </p>
            <br />
            <textarea id="ufwp_custom_css" name="ufwp_settings[custom_css]" rows="10" cols="80" style="width: 100%;"><?php echo stripslashes($custom_css); ?></textarea>
            <p>
                <small><?php _e("Please don't use the <code>style</code> tag. Simply paste you CSS classes/definitions e.g. <code>.ufwp .ufwp-course { background-color: #333; color: #fff; }</code>", 'wp-udemy' ) ?></small>
            </p>

            <?php
        }

        function widget_text_shortcodes_render() {

            $shortcodes = ( isset ( $this->options['widget_text_shortcodes'] ) && $this->options['widget_text_shortcodes'] == '1' ) ? 1 : 0;

            ?>
            <input type="checkbox" id="ufwp_widget_text_shortcodes" name="ufwp_settings[widget_text_shortcodes]" value="1" <?php echo($shortcodes == 1 ? 'checked' : ''); ?>>
            <label for="ufwp_widget_text_shortcodes"><?php _e("Activate if your theme doesn't support shortcodes within text widgets.", 'wp-udemy'); ?></label>
            <?php
        }

        function credits_render() {

            $credits = ( isset ( $this->options['credits'] ) && $this->options['credits'] == '1' ) ? 1 : 0;

            ?>
            <input type="checkbox" id="ufwp_credits" name="ufwp_settings[credits]" value="1" <?php echo($credits == 1 ? 'checked' : ''); ?>>
            <label for="ufwp_credits"><?php _e('Activate if you love this plugin and spread it to the world!', 'wp-udemy'); ?> :-)</label>
            <?php
        }

        function developer_mode_render() {

            $developer_mode = ( isset ( $this->options['developer_mode'] ) && $this->options['developer_mode'] == '1' ) ? 1 : 0;

            ?>
            <input type="checkbox" id="ufwp_developer_mode" name="ufwp_settings[developer_mode]" value="1" <?php echo($developer_mode == 1 ? 'checked' : ''); ?>>
            <label for="ufwp_developer_mode"><?php _e('Please activate for debugging reasons only', 'wp-udemy'); ?></label>
            <?php
        }

        function debug_information_render() {

            global $wp_version;

            $enabled = '<span style="color: green;"><strong><span class="dashicons dashicons-yes"></span> ' . __('Enabled', 'wp-udemy') . '</strong></span>';
            $disabled = '<span style="color: red;"><strong><span class="dashicons dashicons-no"></span> ' . __('Disabled', 'wp-udemy') . '</strong></span>';

            ?>

            <table class="widefat ufwp-settings-table">
                <thead>
                    <tr>
                        <th width="300"><?php _e('Setting', 'wp-udemy'); ?></th>
                        <th><?php _e('Values', 'wp-udemy'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>WordPress</th>
                        <td>Version <?php echo $wp_version; ?></td>
                    </tr>
                    <tr class="alternate">
                        <th>PHP</th>
                        <td>Version <strong><?php echo phpversion(); ?></strong></td>
                    </tr>
                    <tr>
                        <th><?php printf( esc_html__( 'PHP "%1$s" extension', 'wp-udemy' ), 'cURL' ); ?></th>
                        <td>
                            <?php echo (isset ($this->curl['enabled']) && $this->curl['enabled']) ? $enabled : $disabled; ?>
                            <?php if (isset ($this->curl['version'])) echo ' (Version ' . $this->curl['version'] . ')'; ?>
                        </td>
                    </tr>
                    <tr class="alternate">
                        <th><?php _e('Cache', 'wp-udemy'); ?></th>
                        <td>
                            <?php $cache = get_option( 'ufwp_cache', ufwp_get_cache_structure() ); ?>

                            <strong><?php _e('Size', 'wp-udemy'); ?></strong><br />
                            <?php printf( esc_html__( '%1$s courses and %2$s lists.', 'wp-udemy' ), '<strong>' . sizeof( $cache['items'] ) . '</strong>', '<strong>' . sizeof( $cache['lists'] ) . '</strong>' ); ?>
                            <br /><br />
                            <strong><?php _e('Last update', 'wp-udemy'); ?></strong><br />
                            <?php echo ( ! empty ( $cache['last_update'] ) && is_numeric( $cache['last_update'] ) ) ? ufwp_get_datetime( $cache['last_update'] ) : 'N/A'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Next Cron Execution', 'wp-udemy'); ?></th>
                        <td><?php echo ufwp_get_datetime( wp_next_scheduled( 'ufwp_wp_scheduled_events' ) ); ?></td>
                    </tr>
                </tbody>
            </table>

            <p>
                <?php _e('In case one of the values above is <span style="color: red;"><strong>red</strong></span>, please get in contact with your webhoster in order to enable the missing PHP extensions.', 'wp-udemy'); ?>
            </p>

            <br />

            <p>
                <strong><?php _e('Log file', 'wp-udemy'); ?></strong><br />
                <textarea rows="5" style="width: 100%;"><?php echo get_option( 'ufwp_log', __( 'No entries yet. ', 'wp-udemy' ) ); ?></textarea>
            </p>
            <p>
                <input type="hidden" id="ufwp_reset_log" name="ufwp_settings[reset_log]" value="0" />
                <?php submit_button( 'Reset log', 'delete button-secondary', 'ufwp-reset-log-submit', false ); ?>
            </p>
            <?php
        }

        function options_page()
        {
            ?>

            <div class="ufwp-settings">
                <div class="wrap">
                    <?php screen_icon(); ?>
                    <h2><?php _e('Online Learning Courses', 'wp-udemy'); ?></h2>

                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content">
                                <div class="meta-box-sortables ui-sortable">
                                    <form action="options.php" method="post">

                                        <?php
                                        settings_fields('ufwp_settings');
                                        ufwp_do_settings_sections('ufwp_settings');
                                        ?>

                                        <p>
                                            <?php submit_button( 'Save Changes', 'button-primary', 'submit', false ); ?>
                                            &nbsp;
                                            <?php submit_button( 'Delete cache', 'delete button-secondary', 'ufwp-delete-cache-submit', false ); ?>
                                        </p>

                                    </form>
                                </div>

                            </div>
                            <!-- /#post-body-content -->
                            <div id="postbox-container-1" class="postbox-container">
                                <div class="meta-box-sortables">
                                    <?php
                                    $settings_infobox_plugin_slug = apply_filters( 'ufwp_settings_infobox_plugin_slug', 'udemy' );

                                    require_once UFWP_DIR . 'includes/libs/flowdee_infobox.php';
                                    $flowdee_infobox = new Flowdee_Infobox();
                                    $flowdee_infobox->set_plugin_slug( $settings_infobox_plugin_slug );
                                    $flowdee_infobox->display();
                                    ?>
                                </div>

                                <?php if ( ! defined( 'UFWP_PRO_NAME' ) || defined( 'UFWP_PRO_DEBUG' ) ) { ?>
                                    <div class="postbox">
                                        <h3><span><?php _e('Upgrade to PRO Version', 'wp-udemy'); ?></span></h3>
                                        <div class="inside">

                                            <p><?php _e('Do you want to <strong>earn money</strong> with course sales? The PRO version extends the plugin exclusively with our affiliate links feature.', 'wp-udemy'); ?></p>

                                            <ul>
                                                <li><span class="dashicons dashicons-star-filled ufwp-settings-star"></span> <strong><?php _e('Affiliate Links', 'wp-udemy'); ?></strong></li>
                                                <li><span class="dashicons dashicons-star-filled ufwp-settings-star"></span> <strong><?php _e('Masked Links', 'wp-udemy'); ?></strong></li>
                                                <li><span class="dashicons dashicons-star-filled ufwp-settings-star"></span> <strong><?php _e('Click Tracking', 'wp-udemy'); ?></strong></li>
                                                <li><span class="dashicons dashicons-star-filled ufwp-settings-star"></span> <strong><?php _e('Custom Templates', 'wp-udemy'); ?></strong></li>
                                            </ul>

                                            <p>
                                                <?php _e('I would be happy if you give it a chance!', 'wp-udemy'); ?>
                                            </p>

                                            <p>
                                                <?php
                                                $upgrade_link = esc_url( add_query_arg( array(
                                                        'utm_source'   => 'settings-page',
                                                        'utm_medium'   => 'infobox',
                                                        'utm_campaign' => 'Udemy for WordPress - PRO',
                                                    ), 'https://coder.flowdee.de/downloads/wp-udemy-pro/' )
                                                );
                                                ?>
                                                <a class="ufwp-settings-button ufwp-settings-button--block" target="_blank" href="<?php echo $upgrade_link; ?>" rel="nofollow"><?php _e('More details', 'wp-udemy'); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                <?php } ?>

                                <!-- /.meta-box-sortables -->
                            </div>
                            <!-- /.postbox-container -->
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        /*
         * API Status field
         */
        function api_status_render() {

            $status = ( ! empty ( $this->options['api_status'] ) ) ? true : false;
            $error = ( ! empty ( $this->options['api_error'] ) ) ? $this->options['api_error'] : '';

            $message = ( $status ) ? __( 'Connected', 'wp-udemy' ) : __( 'Disconnected', 'wp-udemy' );
            $color = ( $status ) ? 'darkgreen' : 'darkred';

            ?>
            <span style="color: <?php echo $color; ?>; font-weight: bold;"><?php echo ( ! empty ( $error ) ) ? $error : $message; ?></span>
            <?php
        }

        private function check_curl() {

            if ( ( function_exists('curl_version') ) ) {

                $curl_data = curl_version();
                $version = ( isset ( $curl_data['version'] ) ) ? $curl_data['version'] : null;

                return array(
                    'enabled' => true,
                    'version' => $version
                );
            } else {
                $this->checks = false;
                return false;
            }
        }
    }
}

new UFWP_Settings();

/*
 * Custom settings section output
 *
 * Replacing: do_settings_sections('wp-udemy');
 */
function ufwp_do_settings_sections( $page ) {

    global $wp_settings_sections, $wp_settings_fields;

    if (!isset($wp_settings_sections[$page]))
        return;

    foreach ((array)$wp_settings_sections[$page] as $section) {

        $title = '';

        if ($section['title'])
            $title = "<h3 class='hndle'>{$section['title']}</h3>\n";

        if ($section['callback'])
            call_user_func($section['callback'], $section);

        if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]))
            continue;

        echo '<div class="postbox">';
        echo $title;
        echo '<div class="inside">';
        echo '<table class="form-table">';
        do_settings_fields($page, $section['id']);
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }
}

?>