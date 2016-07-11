<?php
/**
 * Settings
 *
 * @package     Udemy\Settings
 * @since       1.0.0
 */


// Exit if accessed directly
if (!defined('ABSPATH')) exit;


if (!class_exists('Udemy_Settings')) {

    class Udemy_Settings
    {
        public $options;

        private $checks = true;

        private $curl;
        private $php;

        public function __construct()
        {
            // Variables
            $this->options = get_option('udemy');

            // Checks
            $this->curl = $this->check_curl();

            // Initialize
            add_action('admin_menu', array( &$this, 'add_admin_menu') );
            add_action('admin_init', array( &$this, 'init_settings') );
        }

        function add_admin_menu()
        {

            add_options_page(
                'Udemy',
                'Udemy',
                'manage_options',
                'udemy',
                array( &$this, 'options_page' )
            );

        }

        function init_settings()
        {
            register_setting(
                'udemy',
                'udemy',
                array( &$this, 'validate_input_callback' )
            );

            // SECTION: Quickstart
            add_settings_section(
                'udemy_quickstart',
                __('Quickstart Guide', 'udemy'),
                array( &$this, 'section_quickstart_render' ),
                'udemy'
            );

            // SECTION: General
            add_settings_section(
                'udemy_general',
                __('General Settings', 'udemy'),
                false,
                'udemy'
            );

            add_settings_field(
                'udemy_api_client',
                __('Udemy API', 'udemy'),
                array(&$this, 'api_client_render'),
                'udemy',
                'udemy_general'
            );

            add_settings_field(
                'udemy_cache_duration',
                __('Cache Duration', 'udemy'),
                array(&$this, 'cache_duration_render'),
                'udemy',
                'udemy_general'
            );

            /*
             * Action to add more settings within this section
             */
            do_action( 'udemy_settings_general_register' );

            // SECTION: Output
            add_settings_section(
                'udemy_output',
                __('Output Settings', 'udemy'),
                false,
                'udemy'
            );

            add_settings_field(
                'udemy_default_templates',
                __('Standard Templates', 'udemy'),
                array(&$this, 'default_templates_render'),
                'udemy',
                'udemy_output'
            );

            add_settings_field(
                'udemy_course_details',
                __('Course Details', 'udemy'),
                array(&$this, 'course_details_render'),
                'udemy',
                'udemy_output',
                array('label_for' => 'udemy_course_details')
            );

            /*
             * Action to add more settings within this section
             */
            do_action( 'udemy_settings_output_register' );

            // SECTION: Debug
            add_settings_section(
                'udemy_other',
                __('Other Settings', 'udemy'),
                false,
                'udemy'
            );

            add_settings_field(
                'udemy_widget_text_shortcodes',
                __('Widgets & Shortcodes', 'udemy'),
                array(&$this, 'widget_text_shortcodes_render'),
                'udemy',
                'udemy_other',
                array('label_for' => 'udemy_widget_text_shortcodes')
            );

            add_settings_field(
                'udemy_credits',
                __('You love this plugin?', 'udemy'),
                array(&$this, 'credits_render'),
                'udemy',
                'udemy_other',
                array('label_for' => 'udemy_credits')
            );

            add_settings_field(
                'udemy_developer_mode',
                __('Developer Mode', 'udemy'),
                array(&$this, 'developer_mode_render'),
                'udemy',
                'udemy_other',
                array('label_for' => 'udemy_developer_mode')
            );

            if ( UDEMY_DEBUG ) {

                add_settings_field(
                    'udemy_debug_information',
                    __('Debug Information', 'udemy'),
                    array(&$this, 'debug_information_render'),
                    'udemy',
                    'udemy_other'
                );
            }

            /*
             * Action to add more settings within this section
             */
            do_action( 'udemy_settings_debug_register' );
        }

        function validate_input_callback( $input ) {

            //udemy_debug($input);

            $validation = ( isset ( $this->options['api_status'] ) ) ? $this->options['api_status'] : false;
            $error = ( isset ( $this->options['api_error'] ) ) ? $this->options['api_error'] : '';

            if ( ! empty ( $input['api_client_id'] ) && ! empty ( $input['api_client_password'] ) ) {

                $api_client_id = ( isset ( $this->options['api_client_id'] ) ) ? $this->options['api_client_id'] : '';
                $api_client_id_new = $input['api_client_id'];

                $api_client_password = ( isset ( $this->options['api_client_password'] ) ) ? $this->options['api_client_password'] : '';
                $api_client_password_new = $input['api_client_password'];

                if ( $api_client_id_new != $api_client_id || $api_client_password_new != $api_client_password ) {

                    $result = udemy_validate_api_credentials( $api_client_id_new, $api_client_password_new );

                    $validation = ( ! empty ( $result['status'] ) ) ? true : false;
                    $error = ( ! empty ( $result['error'] ) ) ? $result['error'] : '';
                }
            }

            $input['api_status'] = $validation;
            $input['api_error'] = $error;

            // Handle cache deletion
            if ( isset ( $input['delete_cache'] ) && $input['delete_cache'] === '1' ) {
                udemy_delete_cache();
                $input['delete_cache'] = '0';
            }

            // Handle cache deletion
            if ( isset ( $input['reset_log'] ) && $input['reset_log'] === '1' ) {
                delete_option('udemy_log');
                $input['reset_log'] = '0';
            }

            $input = apply_filters( 'udemy_settings_validate_input', $input );

            return $input;
        }

        function section_quickstart_render() {
            ?>

            <div class="postbox">
                <h3 class='hndle'><?php _e('Quickstart Guide', 'udemy'); ?></h3>
                <div class="inside">
                    <p><?php _e( 'There are two ways of displaying courses:', 'udemy' ); ?></p>
                    <p>
                        <strong><?php _e( 'Single course by ID', 'udemy' ); ?></strong><br />
                        <?php _e( 'In order to get the course ID, simply add the course to the cart and take the ID out of the url of your browser.', 'udemy' ); ?>
                    </p>
                    <p>
                        <code>[udemy id="480986"]</code>
                    </p>

                    <p>
                        <strong><?php _e( 'Search for courses', 'udemy' ); ?></strong><br />
                        <?php _e('Alternatively you can search for courses and display grids or lists of multiple courses.', 'udemy'); ?> <span style="color: darkorange; font-weight: bold;"><?php _e( 'This feature requires API keys!', 'udemy' ); ?></span>
                    <p>
                        <code>[udemy search="css" items="3" template="grid"]</code> <?php _e( 'or', 'udemy' ); ?> <code>[udemy search="html" items="6" template="list"]</code>
                    </p>

                    <p><?php printf( wp_kses( __( 'Please take a look into the <a href="%s">documentation</a> for more options.', 'udemy' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( 'https://wordpress.org/plugins/udemy/faq/' ) ); ?></p>

                    <?php do_action( 'udemy_settings_quickstart_render' ); ?>
                </div>
            </div>

            <?php
        }

        function api_client_render() {

            $api_client_id = ( !empty($this->options['api_client_id'] ) ) ? esc_attr( trim( $this->options['api_client_id'] ) ) : '';
            $api_client_password = ( !empty($this->options['api_client_password'] ) ) ? esc_attr( trim($this->options['api_client_password'] ) ) : '';

            ?>
            <h4 style="margin: 5px 0"><?php _e('Status', 'udemy'); ?></h4>
            <?php if ( ! empty( $api_client_id ) && ! empty( $api_client_password ) ) { ?>
                <?php $this->api_status_render(); ?>
            <?php } else { ?>
                <strong><?php _e('Note:', 'udemy'); ?></strong> <?php _e("API credentials are currently only required when searching courses or displaying categories.", 'udemy'); ?>
            <?php } ?>

            <h4 style="margin-bottom: 5px"><?php _e('Client ID', 'udemy'); ?></h4>
            <input type='text' name='udemy[api_client_id]' id="udemy_api_client_id"
                   value='<?php echo esc_attr( trim( $api_client_id ) ); ?>' style="width: 300px;">

            <h4 style="margin: 15px 0 5px 0;"><?php _e('Client Password', 'udemy'); ?></h4>
            <input type='text' name='udemy[api_client_password]' id="udemy_api_client_password"
                   value='<?php echo esc_attr( trim( $api_client_password ) ); ?>' style="width: 300px;">

            <p>
                <small>
                    <?php printf( wp_kses( __( 'Before entering your API credentials you have to create a new API Client <a href="%s">here</a>.', 'udemy' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( 'https://www.udemy.com/user/edit-api-clients/' ) ); ?>
                </small>
            </p>
            <?php
        }

        function cache_duration_render() {

            $cache_durations = array(
                '360' => __('6 Hours', 'udemy'),
                '720' => __('12 Hours', 'udemy'),
                '1440' => __('1 Day', 'udemy'),
                '4320' => __('3 Days', 'udemy'),
                '10080' => __('1 Week', 'udemy'),
            );

            $cache_duration = ( isset ( $this->options['cache_duration'] ) ) ? $this->options['cache_duration'] : '1440';

            ?>
            <select id="udemy_cache_duration" name="udemy[cache_duration]">
                <?php foreach ( $cache_durations as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $cache_duration, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <input type="hidden" id="udemy_delete_cache" name="udemy[delete_cache]" value="0" />
            <?php
        }

        function default_templates_render() {

            $templates = array(
                'standard' => __('Standard', 'udemy'),
                'grid' => __('Grid', 'udemy'),
                'list' => __('List', 'udemy')
            );

            $template_course = ( isset ( $this->options['template_course'] ) ) ? $this->options['template_course'] : 'standard';
            $template_courses = ( isset ( $this->options['template_courses'] ) ) ? $this->options['template_courses'] : 'list';

            ?>
            <h4 style="margin: 5px 0;"><?php _e('Single Course', 'udemy'); ?></h4>
            <p>
                <select id="udemy_template_course" name="udemy[template_course]">
                    <?php foreach ( $templates as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $template_course, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <br />

            <h4 style="margin: 5px 0;"><?php _e('Multiple Courses', 'udemy'); ?></h4>
            <p>
                <select id="udemy_template_courses" name="udemy[template_courses]">
                    <?php foreach ( $templates as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $template_courses, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <br />

            <p><?php printf( esc_html__( 'Available templates (%1$s) can be used to overwrite each shortcode individually: e.g.', 'udemy' ), 'standard, grid, list' ); ?> <code>[udemy id="1234,6789" template="list"]</code></p>
            <p></p>
            <?php
        }

        function course_details_render() {

            $course_details_options = array(
                'course' => __('Course Subtitle', 'udemy'),
                'instructor' => __('Instructor information', 'udemy'),
            );

            $course_details = ( isset ( $this->options['course_details'] ) ) ? $this->options['course_details'] : 'course';

            ?>
            <select id="udemy_course_details" name="udemy[course_details]">
                <?php foreach ( $course_details_options as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $course_details, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <p><small><?php _e('This will be applied to grid and list templates. The standard template already shows both information.', 'udemy'); ?></small></p>
            <?php
        }

        function widget_text_shortcodes_render() {

            $shortcodes = ( isset ( $this->options['widget_text_shortcodes'] ) && $this->options['widget_text_shortcodes'] == '1' ) ? 1 : 0;

            ?>
            <input type="checkbox" id="udemy_widget_text_shortcodes" name="udemy[widget_text_shortcodes]" value="1" <?php echo($shortcodes == 1 ? 'checked' : ''); ?>>
            <label for="udemy_widget_text_shortcodes"><?php _e("Activate if your theme doesn't support shortcodes within text widgets.", 'udemy'); ?></label>
            <?php
        }

        function credits_render() {

            $credits = ( isset ( $this->options['credits'] ) && $this->options['credits'] == '1' ) ? 1 : 0;

            ?>
            <input type="checkbox" id="udemy_credits" name="udemy[credits]" value="1" <?php echo($credits == 1 ? 'checked' : ''); ?>>
            <label for="udemy_credits"><?php _e('Activate if you love this plugin and want to support my work by affiliating the Udemy links.', 'udemy'); ?></label>
            <?php
        }

        function developer_mode_render() {

            $developer_mode = ( isset ( $this->options['developer_mode'] ) && $this->options['developer_mode'] == '1' ) ? 1 : 0;

            ?>
            <input type="checkbox" id="udemy_developer_mode" name="udemy[developer_mode]" value="1" <?php echo($developer_mode == 1 ? 'checked' : ''); ?>>
            <label for="udemy_developer_mode"><?php _e('Please activate for debugging reasons only', 'udemy'); ?></label>
            <?php
        }

        function debug_information_render() {

            global $wp_version;

            $enabled = '<span style="color: green;"><strong><span class="dashicons dashicons-yes"></span> ' . __('Enabled', 'udemy') . '</strong></span>';
            $disabled = '<span style="color: red;"><strong><span class="dashicons dashicons-no"></span> ' . __('Disabled', 'udemy') . '</strong></span>';

            ?>

            <table class="widefat udemy-settings-table">
                <thead>
                    <tr>
                        <th width="300"><?php _e('Setting', 'udemy'); ?></th>
                        <th><?php _e('Values', 'udemy'); ?></th>
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
                        <th><?php printf( esc_html__( 'PHP "%1$s" extension', 'udemy' ), 'cURL' ); ?></th>
                        <td>
                            <?php echo (isset ($this->curl['enabled']) && $this->curl['enabled']) ? $enabled : $disabled; ?>
                            <?php if (isset ($this->curl['version'])) echo ' (Version ' . $this->curl['version'] . ')'; ?>
                        </td>
                    </tr>
                    <tr class="alternate">
                        <th><?php _e('Cache', 'udemy'); ?></th>
                        <td>
                            <?php $cache = get_option( 'udemy_cache', udemy_get_cache_structure() ); ?>

                            <strong><?php _e('Size', 'udemy'); ?></strong><br />
                            <?php printf( esc_html__( '%1$s courses and %2$s lists.', 'udemy' ), '<strong>' . sizeof( $cache['items'] ) . '</strong>', '<strong>' . sizeof( $cache['lists'] ) . '</strong>' ); ?>
                            <br /><br />
                            <strong><?php _e('Last update', 'udemy'); ?></strong><br />
                            <?php echo ( ! empty ( $cache['last_update'] ) && is_numeric( $cache['last_update'] ) ) ? udemy_get_datetime( $cache['last_update'] ) : 'N/A'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Next Cron Execution', 'udemy'); ?></th>
                        <td><?php echo udemy_get_datetime( wp_next_scheduled( 'udemy_wp_scheduled_events' ) ); ?></td>
                    </tr>
                </tbody>
            </table>

            <p>
                <?php _e('In case one of the values above is <span style="color: red;"><strong>red</strong></span>, please get in contact with your webhoster in order to enable the missing PHP extensions.', 'udemy'); ?>
            </p>

            <br />

            <p>
                <strong><?php _e('Log file', 'udemy'); ?></strong><br />
                <textarea rows="5" style="width: 100%;"><?php echo get_option( 'udemy_log', __( 'No entries yet. ', 'udemy' ) ); ?></textarea>
            </p>
            <p>
                <input type="hidden" id="udemy_reset_log" name="udemy[reset_log]" value="0" />
                <?php submit_button( 'Reset log', 'delete button-secondary', 'udemy-reset-log-submit', false ); ?>
            </p>
            <?php
        }

        function options_page()
        {
            ?>

            <div class="wrap">
                <?php screen_icon(); ?>
                <h2><?php _e('Udemy', 'udemy'); ?></h2>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                                <form action="options.php" method="post">

                                    <?php
                                    settings_fields('udemy');
                                    udemy_do_settings_sections('udemy');
                                    ?>

                                    <p>
                                        <?php submit_button( 'Save Changes', 'button-primary', 'submit', false ); ?>
                                        &nbsp;
                                        <?php submit_button( 'Delete cache', 'delete button-secondary', 'udemy-delete-cache-submit', false ); ?>
                                    </p>

                                </form>
                            </div>

                        </div>
                        <!-- /#post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <div class="meta-box-sortables">
                                <?php
                                require_once UDEMY_DIR . 'vendor/flowdee_infobox.php';
                                $flowdee_infobox = new Flowdee_Infobox();
                                $flowdee_infobox->set_plugin_slug('udemywp');
                                $flowdee_infobox->display();
                                ?>
                            </div>
                            <!-- /.meta-box-sortables -->
                        </div>
                        <!-- /.postbox-container -->
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

            $message = ( $status ) ? __( 'Connected', 'udemy' ) : __( 'Disconnected', 'udemy' );
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

new Udemy_Settings();

/*
 * Custom settings section output
 *
 * Replacing: do_settings_sections('udemy');
 */
function udemy_do_settings_sections( $page ) {

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