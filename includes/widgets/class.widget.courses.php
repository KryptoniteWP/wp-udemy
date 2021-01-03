<?php
/**
 * Widget: Courses
 *
 * @package     UFWP\WidgetCourses
 * @since       1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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

            if ( empty( $instance['ids'] ) ) {
                esc_attr_e( 'Please enter a course ID.', 'wp-udemy' );
            }

            if ( ! empty( $instance['ids'] ) ) {
                // IDs
                $shortcode_atts = array(
                    'type' => 'widget',
                    'id'   => $instance['ids'],
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

            $title           = ! empty( $instance['title'] ) ? $instance['title'] : '';
            $ids             = ! empty( $instance['ids'] ) ? $instance['ids'] : '';
            $template        = ! empty( $instance['template'] ) ? $instance['template'] : 'widget';
            $template_custom = ! empty( $instance['template_custom'] ) ? $instance['template_custom'] : '';
            $style           = ! empty( $instance['style'] ) ? $instance['style'] : '';
            $url             = ! empty( $instance['url'] ) ? $instance['url'] : '';

            ?>
            <p>
                <label for="<?php esc_attr_e( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'wp-udemy' ); ?></label>
                <input class="widefat" id="<?php esc_attr_e( $this->get_field_id( 'title' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php esc_attr_e( $title ); ?>">
            </p>

            <p>
                <label for="<?php esc_attr_e( $this->get_field_id( 'ids' ) ); ?>"><?php esc_attr_e( 'Course IDs:', 'wp-udemy' ); ?></label>
                <input class="widefat" id="<?php esc_attr_e( $this->get_field_id( 'ids' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'ids' ) ); ?>" type="text" value="<?php esc_attr_e( $ids ); ?>">
                <br />
                <small>
                    <?php esc_attr_e( 'You can enter multiple course IDs and separate them by comma.', 'wp-udemy' ); ?>
                </small>
            </p>

            <?php
            $templates = array(
                'widget'       => __( 'Standard', 'wp-udemy' ),
                'widget_small' => __( 'Small', 'wp-udemy' )
            );
            ?>
            <p>
                <label for="<?php esc_attr_e( $this->get_field_id( 'template' ) ); ?>"><?php esc_attr_e( 'Template:', 'wp-udemy' ); ?></label>
                <select class="widefat" id="<?php esc_attr_e( $this->get_field_id( 'template' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'template' ) ); ?>">
                    <?php foreach ( $templates as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $template, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
                <br />
                <small>
                    <?php esc_attr_e( 'The templates listed above are optimized for widgets.', 'wp-udemy' ); ?>
                </small>
            </p>

            <p>
                <label for="<?php esc_attr_e( $this->get_field_id( 'template_custom' ) ); ?>"><?php esc_attr_e( 'Custom Template:', 'wp-udemy' ); ?></label>
                <input class="widefat" id="<?php esc_attr_e( $this->get_field_id( 'template_custom' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'template_custom' ) ); ?>" type="text" value="<?php esc_attr_e( $template_custom ); ?>">
                <br />
                <small>
                    <?php _e( 'You can use another template by entering the the name: e.g. <strong>my_widget</strong>.', 'wp-udemy' ); ?>
                </small>
            </p>

            <?php
            $styles = array(
                ''      => __( 'Standard', 'wp-udemy' ),
                'clean' => __( 'Clean', 'wp-udemy' ),
                'light' => __( 'Light', 'wp-udemy' ),
                'dark'  => __( 'Dark', 'wp-udemy' )
            );
            ?>
            <p>
                <label for="<?php esc_attr_e( $this->get_field_id( 'style' ) ); ?>"><?php esc_attr_e( 'Style:', 'wp-udemy' ); ?></label>
                <select class="widefat" id="<?php esc_attr_e( $this->get_field_id( 'style' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'style' ) ); ?>">
                    <?php foreach ( $styles as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $style, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <?php if ( ! defined( 'UFWP_PRO_NAME' ) || defined( 'UFWP_PRO_DEBUG' ) ) { ?>
            <p>
                <label for="<?php esc_attr_e( $this->get_field_id( 'url' ) ); ?>"><?php esc_attr_e( 'Custom URL:', 'wp-udemy' ); ?></label>
                <input class="widefat" id="<?php esc_attr_e( $this->get_field_id( 'url' ) ); ?>" name="<?php esc_attr_e( $this->get_field_name( 'url' ) ); ?>" type="text" value="<?php esc_attr_e( $url ); ?>">
                <br />
                <small>
                    <?php esc_attr_e( 'Only working when entering one course id.', 'wp-udemy' ); ?>
                </small>
            </p>
            <?php } ?>

            <?php
        }

        /**
         * Sanitize widget form values as they are saved.
         *
         * @see WP_Widget::update()
         *
         * @param array $new_instance
         * @param array $old_instance
         * @return array Updated safe values to be saved.
         */
        public function update( $new_instance, $old_instance ) {
            $instance = array();

            $instance['title']           = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            $instance['ids']             = ( ! empty( $new_instance['ids'] ) ) ? strip_tags( $new_instance['ids'] ) : '';
            $instance['template']        = ( ! empty( $new_instance['template'] ) ) ? strip_tags( $new_instance['template'] ) : '';
            $instance['template_custom'] = ( ! empty( $new_instance['template_custom'] ) ) ? strip_tags( $new_instance['template_custom'] ) : '';
            $instance['style']           = ( ! empty( $new_instance['style'] ) ) ? strip_tags( $new_instance['style'] ) : '';
            $instance['url']             = ( ! empty( $new_instance['url'] ) ) ? strip_tags( $new_instance['url'] ) : '';

            return $instance;
        }
    }

}
