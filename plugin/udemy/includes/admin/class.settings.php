<?php
/**
 * Settings
 *
 * @package     RollbarWP\Settings
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
            register_setting('udemy', 'udemy');

            // SECTION: General
            add_settings_section(
                'udemy_general',
                false,
                false,
                'udemy'
            );

            // On/off
            add_settings_field(
                'udemy_status',
                __('Status', 'rollbar'),
                array(&$this, 'status_render'),
                'udemy',
                'udemy_general'
            );

            // Token
            add_settings_field(
                'udemy_access_token',
                __('Access Token', 'rollbar'),
                array(&$this, 'access_token_render'),
                'udemy',
                'udemy_general'
            );

            // Config
            add_settings_field(
                'udemy_environment',
                __('Environment', 'rollbar'),
                array(&$this, 'environment_render'),
                'udemy',
                'udemy_general',
                array( 'label_for' => 'udemy_environment' )
            );

            add_settings_field(
                'udemy_logging_level',
                __('Logging level', 'rollbar'),
                array(&$this, 'logging_level_render'),
                'udemy',
                'udemy_general',
                array( 'label_for' => 'udemy_logging_level' )
            );
        }

        function status_render()
        {
            $php_logging_enabled = (!empty($this->options['php_logging_enabled'])) ? 1 : 0;
            ?>

            <input type='checkbox' name='udemy[php_logging_enabled]'
                   id="udemy_php_logging_enabled" <?php checked($php_logging_enabled, 1); ?> value='1'/>
            <label for="udemy_php_logging_enabled"><?php _e('PHP error logging', 'rollbar-wp'); ?></label>
            <?php
        }

        function access_token_render()
        {
            $client_side_access_token = (!empty($this->options['client_side_access_token'])) ? esc_attr(trim($this->options['client_side_access_token'])) : null;
            $server_side_access_token = (!empty($this->options['server_side_access_token'])) ? esc_attr(trim($this->options['server_side_access_token'])) : null;

            ?>
            <h4 style="margin: 5px 0;"><?php _e('Client Side Access Token', 'rollbar-wp'); ?> <small>(post_client_item)</small></h4>
            <input type='text' name='udemy[client_side_access_token]' id="udemy_client_side_access_token"
                   value='<?php echo esc_attr(trim($client_side_access_token)); ?>' style="width: 300px;">

            <h4 style="margin: 15px 0 5px 0;"><?php _e('Server Side Access Token', 'rollbar-wp'); ?> <small>(post_server_item)</small></h4>
            <input type='text' name='udemy[server_side_access_token]' id="udemy_server_side_access_token"
                   value='<?php echo esc_attr(trim($server_side_access_token)); ?>' style="width: 300px;">
            <p>
                <small><?php _e('You can find your access tokens under your project settings: <strong>Project Access Tokens</strong>.', 'rollbar-wp'); ?></small>
            </p>
            <?php
        }

        function environment_render()
        {
            $environment = (!empty($this->options['environment'])) ? esc_attr(trim($this->options['environment'])) : '';

            ?>
            <input type='text' name='udemy[environment]' id="udemy_environment"
                   value='<?php echo esc_attr(trim($environment)); ?>'>
            <p>
                <small><?php _e('Define the current environment: e.g. "production" or "development".', 'rollbar-wp'); ?></small>
            </p>
            <?php
        }

        function logging_level_render()
        {
            $logging_level = (!empty($this->options['logging_level'])) ? esc_attr(trim($this->options['logging_level'])) : 1024;

            ?>

            <select name="udemy[logging_level]" id="udemy_logging_level">
                <option
                    value="1" <?php selected($logging_level, 1); ?>><?php _e('Fatal run-time errors (E_ERROR) only', 'rollbar-wp'); ?></option>
                <option
                    value="2" <?php selected($logging_level, 2); ?>><?php _e('Run-time warnings (E_WARNING) and above', 'rollbar-wp'); ?></option>
            </select>

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

                                    <div class="postbox">
                                        <div class="inside">

                                            <?php
                                            settings_fields('udemy');
                                            do_settings_sections('udemy');
                                            submit_button();
                                            ?>

                                        </div>
                                    </div>
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
    }
}

new Udemy_Settings();

?>