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
            $this->course  = $course; // https://www.udemy.com/api-2.0/courses/1229104/?fields[course]=@all
            $this->args    = $args;

            //ufwp_debug( $course );
        }

        /**
         * Get course id
         *
         * @return int
         */
        public function get_id() {
            return ( isset ( $this->course['id'] ) ) ? $this->course['id'] : 0;
        }

        /**
         * Output course classes
         *
         * @param $classes
         */
        public function the_classes( $classes ) {

            $add_classes = array();

            // Sale?
            if ( $this->is_on_sale() )
                $add_classes[] = 'sale';

            $add_classes = apply_filters( 'ufwp_course_add_classes', $add_classes, $this );

            // Maybe add extra classes
            if ( sizeof( $add_classes ) > 0 ) {
                foreach ( $add_classes as $class ) {
                    $classes .= ' ufwp-course--' . $class;
                }
            }

            // Output
            echo esc_attr( $classes );
        }

        /**
         * Output container attributes
         *
         * @param bool $echo
         * @return string
         */
        public function the_container( $echo = true ) {

            $output = '';

            $attributes = array();

            // HTML ID
            //$output .= ' id="wp-udemy-course-' . $this->get_id() . '"';

            // Course ID
            $attributes['course-id'] = $this->get_id();

            // Add more via filter
            $attributes = apply_filters( 'ufwp_course_container_attributes', $attributes, $this );

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

        /**
         * Output the course badges
         */
        public function the_badges() {

            $badges = array();

            $badges = apply_filters( 'ufwp_course_badges', $badges, $this );

            // Output badges
            if ( sizeof( $badges ) > 0 ) {
                foreach ( $badges as $key => $text ) {
                    ?><span class="ufwp-course__badge ufwp-badge ufwp-badge--<?php echo esc_html( $key ); ?>"><?php echo $text; ?></span><?php
                }
            }
        }

        /**
         * Get title
         *
         * @return mixed|string
         */
        public function get_title() {

            $title = ( isset ( $this->course['title'] ) ) ? $this->course['title'] : '';

            $title = apply_filters( 'ufwp_course_title', $title );

            return $title;
        }

        /**
         * Get image
         *
         * @param null $size
         * @return string
         */
        public function get_image( $size = null ) {

            $image_placeholder = UFWP_URL . 'public/img/placeholder-course-image.png';

            //ufwp_debug_log( $this->course );

            $image_size = 'image_480x270';

            if ( 'small' === $size )
	            $image_size = 'image_125_H';

            if ( 'list' === $size )
	            $image_size = 'image_200_H';

            if ( 'widget_small' === $size )
	            $image_size = 'image_75x75';

            if ( ! isset( $this->course[$image_size] ) )
                return $image_placeholder;

            $image = ( 'download' === ufwp_get_option( 'images', false ) ) ? $this->get_downloaded_image( $image_size ) : $this->course[$image_size];

            $image = apply_filters( 'ufwp_course_image', $image );

            return ( ! empty( $image ) ) ? $image : $image_placeholder;
        }

	    /**
         * Get downloaded image
         *
	     * @param $image_size
	     *
	     * @return null|string
	     */
        private function get_downloaded_image( $image_size ) {

            $file_name = 'course_' . $this->get_id() . '_' . $image_size . '.jpg';

            if ( ufwp_downloaded_course_image_exists( $file_name ) )
                return ufwp_get_downloaded_course_image_url( $file_name );

            $downloaded_image = ufwp_download_course_image( $file_name, $this->course[$image_size] );

            return ( is_array( $downloaded_image ) && isset( $downloaded_image['url'] ) ) ? $downloaded_image['url'] : '';
        }

        /**
         * Get image alt attribute
         *
         * @return mixed
         */
        public function get_image_alt() {

            $title = $this->get_title();

            return str_replace('"', "'", $title );
        }

        /**
         * Get headline
         *
         * @return string
         */
        public function get_headline() {
            return ( isset ( $this->course['headline'] ) ) ? $this->course['headline'] : '';
        }

        /**
         * Get url
         *
         * @return mixed|string
         */
        public function get_url() {

            if ( isset ( $this->args['url'] ) )
                return $this->args['url'];

            $url = 'https://www.udemy.com';

            if ( isset ( $this->course['url'] ) )
                $url .= $this->course['url'];

            $url = apply_filters( 'ufwp_course_url', $url, $this->course['url'] );

            return $url;
        }

        /**
         * Check whether to show price or not
         *
         * @return bool
         */
        public function show_price() {

            $show_price = true;

            if ( isset ( $this->options['hide_course_prices'] ) && '1' == $this->options['hide_course_prices'] )
                $show_price = false;

            if ( ! empty( $this->args['price'] ) ) {

                if ( 'show' === $this->args['price'] ) {
                    $show_price = true;

                } elseif ( 'none' === $this->args['price'] || 'hide' === $this->args['price'] ) {
                    $show_price = false;
                }
            }

            return $show_price;
        }

        /**
         * Get price
         *
         * @return string
         */
        public function get_price() {

            $price = ( isset ( $this->course['price'] ) ) ? $this->course['price'] : '';

            if ( isset ( $this->course['discount']['price']['price_string'] ) )
                $price = $this->course['discount']['price']['price_string'];

            return $price;
        }

        /**
         * Get list price
         *
         * @return string
         */
        public function get_list_price() {
            return ( isset ( $this->course['discount']['list_price']['price_string'] ) ) ? $this->course['discount']['list_price']['price_string'] : '';
        }

        /**
         * Check whether course is on sale
         *
         * @return bool
         */
        public function is_on_sale() {
            return ( isset ( $this->course['discount_price'] ) && is_array( $this->course['discount_price'] ) ) ? true : false;
        }

        /**
         * Check whether course is a bestseller
         *
         * @return bool
         */
        public function is_bestseller() {
            return ( isset ( $this->course['bestseller_badge_content'] ) && is_array( $this->course['bestseller_badge_content'] ) ) ? true : false;
        }

        /**
         * Check whether course is a bestseller
         *
         * @return bool
         */
        public function is_new() {
            return ( isset ( $this->course['is_recently_published'] ) && true == $this->course['is_recently_published'] ) ? true : false;
        }

        /**
         * Get instructors
         *
         * @return string
         */
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

        /**
         * Get details
         *
         * @return string
         */
        public function get_details() {

            $options_details = ( isset ( $this->options['course_details'] ) ) ? $this->options['course_details'] : 'course';

            if ( 'course' === $options_details )
                return $this->get_headline();

            if ( 'instructor' === $options_details )
                return $this->get_instructors();

            return __('No info available', 'wp-udemy');
        }

        /**
         * Get rating
         *
         * @return float|int
         */
        public function get_rating() {
            return ( isset ( $this->course['avg_rating'] ) ) ? round( $this->course['avg_rating'], 1) : 0;
        }

        /**
         * Output star rating
         */
        public function the_star_rating() {

            $rating = $this->get_rating();

            $percent = ( 100 * $rating ) / 5;

            $star_rating = '<span class="ufwp-star-rating"><span style="width: ' . $percent . '%;"></span></span>';

            echo $star_rating;
        }

        /**
         * Get reviews
         *
         * @return string
         */
        public function get_reviews() {
            return ( isset ( $this->course['num_reviews'] ) ) ? $this->course['num_reviews'] : '';
        }

        /**
         * Check whether to show meta or not
         *
         * @return bool
         */
        public function show_meta() {
            return ( isset ( $this->options['course_meta'] ) ) ? true : false;
        }

        /**
         * Get lectures
         *
         * @return int
         */
        public function get_lectures() {
            return ( isset ( $this->course['num_published_lectures'] ) ) ? $this->course['num_published_lectures'] : 0;
        }

        /**
         * Get length
         *
         * @return int
         */
        public function get_length() {
            return ( isset ( $this->course['estimated_content_length'] ) ) ? intval( $this->course['estimated_content_length'] ) : 0;
        }

        /**
         * Get playing time
         *
         * @return float|string
         */
        public function get_playing_time() {

            $length = $this->get_length();

            return ( ! empty ( $length ) ) ? round ( $length / 60 ) : '';
        }

        /**
         * Get level
         *
         * @return string
         */
        public function get_level() {
            return ( isset ( $this->course['instructional_level'] ) ) ? $this->course['instructional_level'] : '';
        }


    }
}