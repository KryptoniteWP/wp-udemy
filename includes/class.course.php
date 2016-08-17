<?php
/**
 * Course
 *
 * @package     UFWP\Course
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('UFWP_Course')) {

    class UFWP_Course
    {
        public $course;
        public $options;
        public $args;

        public function __construct( $course, $args ) {

            // Variables
            $this->options = ufwp_get_options();
            $this->course = $course;
            $this->args = $args;
        }

        public function get_id() {
            return ( isset ( $this->course['id'] ) ) ? $this->course['id'] : 0;
        }

        public function the_container( $echo = true ) {

            $output = '';

            $attributes = array();

            // HTML ID
            //$output .= ' id="wp-udemy-course-' . $this->get_id() . '"';

            // Course ID
            $attributes['course-id'] = $this->get_id();

            // Add more via filter
            $attributes = apply_filters( 'ufwp_course_container_attributes', $attributes, $this->course );

            if ( sizeof( $attributes ) != 0 ) {

                foreach ( $attributes as $key => $value ) {

                    // Add attribute to output
                    if ( ! empty ( $value ) )
                        $output .= ' data-ufwp-' . $key . '="' . str_replace('"', "'", $value) . '"';
                }
            }

            if ( ! $echo )
                return $output;

            if ( ! empty ( $output ) )
                echo $output;

        }

        public function get_title() {

            $title = ( isset ( $this->course['title'] ) ) ? $this->course['title'] : '';

            $title = apply_filters( 'ufwp_course_title', $title );

            return $title;
        }

        public function get_image( $size = null ) {

            $image = 'image_480x270';

            if ( 'small' === $size )
                $image = 'image_125_H';

            if ( 'list' === $size )
                $image = 'image_200_H';

            if ( 'widget_small' === $size )
                $image = 'image_75x75';

            return ( isset ( $this->course[$image] ) ) ? $this->course[$image] : '';
        }

        public function get_image_alt() {

            $title = $this->get_title();

            return str_replace('"', "'", $title );
        }

        public function get_headline() {
            return ( isset ( $this->course['headline'] ) ) ? $this->course['headline'] : '';
        }

        public function get_url() {

            if ( isset ( $this->args['url'] ) )
                return $this->args['url'];

            $url = 'https://www.udemy.com';

            if ( isset ( $this->course['url'] ) )
                $url .= $this->course['url'];

            $url = apply_filters( 'ufwp_course_url', $url, $this->course['url'] );

            return $url;
        }

        public function get_price() {
            return ( isset ( $this->course['price'] ) ) ? $this->course['price'] : '';
        }

        public function get_instructors() {

            if ( ! isset ( $this->course['visible_instructors'] ) || ! is_array( $this->course['visible_instructors'] ) )
                return '';

            $instructors = '';

            $authors_count = sizeof( $this->course['visible_instructors'] );

            if ( 1 === $authors_count ) {

                $instructors .= ( ! empty ( $this->course['visible_instructors'][0]['display_name'] ) ) ? $this->course['visible_instructors'][0]['display_name'] : '';
                $instructors .= ( ! empty ( $this->course['visible_instructors'][0]['job_title'] ) ) ? ', ' . $this->course['visible_instructors'][0]['job_title'] : '';

            } elseif ( 1 < $authors_count ) {

                foreach ( $this->course['visible_instructors'] as $key => $instructor ) {

                    if ( ! empty ( $instructor['display_name'] ) ) {

                        if ( 0 != $key )
                            $instructors .= ', ';

                        $instructors .= $instructor['display_name'];
                    }
                }
            }

            return $instructors;
        }

        public function get_details() {

            $options_details = ( isset ( $this->options['course_details'] ) ) ? $this->options['course_details'] : 'course';

            if ( 'course' === $options_details )
                return $this->get_headline();

            if ( 'instructor' === $options_details )
                return $this->get_instructors();

            return __('No info available', 'wp-udemy');
        }

        public function get_rating() {
            return ( isset ( $this->course['avg_rating'] ) ) ? round( $this->course['avg_rating'], 1) : 0;
        }

        public function the_star_rating() {

            $rating = $this->get_rating();

            $percent = ( 100 * $rating ) / 5;

            $star_rating = '<span class="ufwp-star-rating"><span style="width: ' . $percent . '%;"></span></span>';

            echo $star_rating;
        }

        public function get_reviews() {
            return ( isset ( $this->course['num_reviews'] ) ) ? $this->course['num_reviews'] : '';
        }

        public function show_meta() {
            return ( isset ( $this->options['course_meta'] ) ) ? true : false;
        }

        public function get_lectures() {
            return ( isset ( $this->course['num_published_lectures'] ) ) ? $this->course['num_published_lectures'] : 0;
        }

        public function get_length() {
            return ( isset ( $this->course['estimated_content_length'] ) ) ? intval( $this->course['estimated_content_length'] ) : 0;
        }

        public function get_playing_time() {

            $length = $this->get_length();

            return ( ! empty ( $length ) ) ? round ( $length / 60 ) : '';
        }

        public function get_level() {
            return ( isset ( $this->course['instructional_level'] ) ) ? $this->course['instructional_level'] : '';
        }
    }
}