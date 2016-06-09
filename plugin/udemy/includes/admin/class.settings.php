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

        public function __construct()
        {
            // Variables
            $this->options = get_option('udemy');

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
                __('General settings', 'udemy'),
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
                __('Cache duration', 'udemy'),
                array(&$this, 'cache_duration_render'),
                'udemy',
                'udemy_general'
            );

            // SECTION: Output
            add_settings_section(
                'udemy_output',
                false,
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

            return $input;
        }

        function section_quickstart_render() {
            ?>

            <div class="postbox">
                <h3 class='hndle'><?php _e('Quickstart Guide', 'udemy'); ?></h3>
                <div class="inside">
                    <p>Here is a quickstart guide! :)</p>
                </div>
            </div>

            <?php
        }

        function api_client_render() {

            $api_client_id = ( !empty($this->options['api_client_id'] ) ) ? esc_attr( trim( $this->options['api_client_id'] ) ) : '';
            $api_client_password = ( !empty($this->options['api_client_password'] ) ) ? esc_attr( trim($this->options['api_client_password'] ) ) : '';

            //udemy_debug(udemy_api_validate_credentials( $api_client_id, $api_client_password ) );

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
                '6' => __('6 Hours', 'udemy'),
                '12' => __('12 Hours', 'udemy'),
                '1' => __('1 Day', 'udemy'),
            );

            $cache_duration = ( isset ( $this->options['cache_duration'] ) ) ? $this->options['cache_duration'] : 'course';

            ?>
            <select id="udemy_cache_duration" name="udemy[cache_duration]">
                <?php foreach ( $cache_durations as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $cache_duration, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>

            <p>
                Cache size: <?php $cache = get_option( 'udemy_cache', udemy_get_cache_structure() ); echo sizeof( $cache['items'] ); ?>
            </p>

            <input type="hidden" id="udemy_delete_cache" name="udemy[delete_cache]" value="0" />
            <?php
        }

        function default_templates_render() {

            $templates = array(
                'single' => __('Single', 'udemy'),
                'grid' => __('Grid', 'udemy'),
                'list' => __('List', 'udemy')
            );

            $template_course = ( isset ( $this->options['template_course'] ) ) ? $this->options['template_course'] : 'single';
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

            <p><?php printf( esc_html__( 'Available templates (%1$s) can be used to overwrite each shortcode individually: e.g.', 'udemy' ), 'single, grid, list' ); ?> <code>[udemy id="1234,6789" template="list"]</code></p>
            <p></p>
            <?php
        }

        function course_details_render() {

            $course_details_options = array(
                'course' => __('Course headline', 'udemy'),
                'instructor' => __('Instructor information', 'udemy'),
            );

            $course_details = ( isset ( $this->options['course_details'] ) ) ? $this->options['course_details'] : 'course';

            ?>
            <select id="udemy_course_details" name="udemy[course_details]">
                <?php foreach ( $course_details_options as $key => $label ) { ?>
                    <option value="<?php echo $key; ?>" <?php selected( $course_details, $key ); ?>><?php echo $label; ?></option>
                <?php } ?>
            </select>
            <p><small><?php _e('This will be applied to grid and list templates. The single template already shows both information.', 'udemy'); ?></small></p>
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

                                    <script type="application/javascript">
                                        jQuery( document ).on( 'click', '#udemy-delete-cache-submit', function(event) {
                                            jQuery('#udemy_delete_cache').val('1');
                                        });
                                    </script>

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
                                <!-- TODO: Infobox -->
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