<?php
/**
 * Widget: Courses
 *
 * @package     UFWP\WidgetCourses
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UFWP_Courses_Widget' ) ) {

    /**
     * Adds UFWP_Courses widget.
     */
    class UFWP_Courses_Widget extends WP_Widget {

        protected static $did_script = false;

        /**
         * Register widget with WordPress.
         */
        function __construct() {
            parent::__construct(
                'ufwp_single_widget', // Base ID
                __( 'UFWP - Courses', 'wp-udemy' ), // Name
                array( 'description' => __( 'Displaying courses by their ids.', 'wp-udemy' ), ) // Args
            );

            add_action('wp_enqueue_scripts', array( $this, 'scripts' ) );
        }

        /**
         * Front-end display of widget.
         *
         * @see WP_Widget::widget()
         *
         * @param array $args     Widget arguments.
         * @param array $instance Saved values from database.
         */
        public function widget( $args, $instance ) {

            echo $args['before_widget'];

            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
            }

            if ( ! empty ( $instance['ids'] ) ) {

                // IDs
                $shortcode_atts = array(
                    'type' => 'widget',
                    'id' => $instance['ids'],
                );

                // Template
                if ( ! empty ( $instance['template_custom'] ) ) {
                    $shortcode_atts['template'] = $instance['template_custom'];
                } elseif ( ! empty ( $instance['template'] ) ) {
                    $shortcode_atts['template'] = $instance['template'];
                }

                // Style
                if ( ! empty ( $instance['style'] ) )
                    $shortcode_atts['style'] = $instance['style'];

                // URL
                if ( ! empty ( $instance['url'] ) )
                    $shortcode_atts['url'] = $instance['url'];

                // Execute Shortcode
                ufwp_widget_do_shortcode( $shortcode_atts );

            } else {
                _e( 'Please enter a course ID.', 'wp-udemy' );
            }

            echo $args['after_widget'];
        }

        /**
         * Back-end widget form.
         *
         * @see WP_Widget::form()
         *
         * @param array $instance Previously saved values from database.
         */
        public function form( $instance ) {

            $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
            $ids = ! empty( $instance['ids'] ) ? $instance['ids'] : '';
            $template = ! empty( $instance['template'] ) ? $instance['template'] : 'widget';
            $template_custom = ! empty( $instance['template_custom'] ) ? $instance['template_custom'] : '';
            $style = ! empty( $instance['style'] ) ? $instance['style'] : '';
            $url = ! empty( $instance['url'] ) ? $instance['url'] : '';

            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ), 'wp-udemy' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'ids' ) ); ?>"><?php _e( 'Course IDs:', 'wp-udemy' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ids' ) ); ?>" type="text" value="<?php echo esc_attr( $ids ); ?>">
                <br />
                <small>
                    <?php _e( 'You can enter multiple course IDs and separate them by comma.', 'wp-udemy' ); ?>
                </small>
            </p>

            <?php
            $templates = array(
                'widget' => __('Standard', 'wp-udemy'),
                'widget_small' => __('Small', 'wp-udemy')
            );
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>"><?php _e( 'Template:', 'wp-udemy' ); ?></label>
                <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>">
                    <?php foreach ( $templates as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $template, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
                <br />
                <small>
                    <?php _e( 'The templates listed above are optimized for widgets.', 'wp-udemy' ); ?>
                </small>
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'template_custom' ) ); ?>"><?php _e( 'Custom Template:', 'wp-udemy' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'template_custom' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'template_custom' ) ); ?>" type="text" value="<?php echo esc_attr( $template_custom ); ?>">
                <br />
                <small>
                    <?php _e( 'You can use another template by entering the the name: e.g. <strong>my_widget</strong>.', 'wp-udemy' ); ?>
                </small>
            </p>

            <?php
            $styles = array(
                '' => __('Standard', 'wp-udemy'),
                'clean' => __('Clean', 'wp-udemy'),
                'light' => __('Light', 'wp-udemy'),
                'dark' => __('Dark', 'wp-udemy')
            );
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>"><?php _e( 'Style:', 'wp-udemy' ); ?></label>
                <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>">
                    <?php foreach ( $styles as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $style, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>"><?php _e( 'Custom URL:', 'wp-udemy' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'url' ) ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>">
                <br />
                <small>
                    <?php _e( 'Only working when entering one course id.', 'wp-udemy' ); ?>
                </small>
            </p>

            <?php
        }

        /**
         * Sanitize widget form values as they are saved.
         *
         * @see WP_Widget::update()
         *
         * @param array $new_instance Values just sent to be saved.
         * @param array $old_instance Previously saved values from database.
         *
         * @return array Updated safe values to be saved.
         */
        public function update( $new_instance, $old_instance ) {
            $instance = array();

            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            $instance['ids'] = ( ! empty( $new_instance['ids'] ) ) ? strip_tags( $new_instance['ids'] ) : '';
            $instance['template'] = ( ! empty( $new_instance['template'] ) ) ? strip_tags( $new_instance['template'] ) : '';
            $instance['template_custom'] = ( ! empty( $new_instance['template_custom'] ) ) ? strip_tags( $new_instance['template_custom'] ) : '';
            $instance['style'] = ( ! empty( $new_instance['style'] ) ) ? strip_tags( $new_instance['style'] ) : '';
            $instance['url'] = ( ! empty( $new_instance['url'] ) ) ? strip_tags( $new_instance['url'] ) : '';

            return $instance;
        }

        /**
         * Enqueue scripts
         */
        public function scripts() {

            if( !self::$did_script && is_active_widget(false, false, $this->id_base, true) ) {
                ufwp_load_scripts();
                self::$did_script = true;
            }
        }
    }

}