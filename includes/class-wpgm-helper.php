<?php
/**
 * Helpers
 * 
 * @link       http://www.19h47.fr
 * @since      1.0.0
 *
 * @package    wpgm
 * @subpackage wpgm/includes
 */


// check if class already exists
if( ! class_exists( 'WPGM_Helper' ) ) :

class WPGM_Helper {

    /**
     * Helper to generate our map markup
     *
     * @since  1.0.0
     * @param  string   $size  
     */
    static function map_markup( $size = array(), $city, $filter, $category = array(), $id = array() ) {

        // Check if we're passing custom values in our shortcode
        $custom = ( ! empty( $size['height'] ) || ! empty ( $size['width'] ) ? true : false );

        // Set defaults if empty
        if ( empty( $size['height'] ) ) {
            
            $size['height'] = '200px';
        }

        if ( empty( $size['width'] ) ) {
            
            $size['width'] = '100%';
        }

        if ( ! $custom ) {
            $size = apply_filters( 'wpgm_map_size', $size );
        }

        $output = '';

        //Don't apply our default filter if we pass a size or if we're calling 
        //our edit scren map
        if ( ! is_admin() && $filter === 'true' ) {

            // Open tag
            $output .= '<div class="Filters js-control-event-category">';
            $output .= '<div class="l-container">';
            $output .= '<div class="row">';
            $output .= '<div class="col-xs-12">';
        
            // Query arguments
            $args = array( 
                'post_type'         => 'event',
                'post_status'       => 'publish',
                'posts_per_page'    => -1
            );

            $args_tax_query = array( 
                'relation' => 'AND',
                array(
                    'taxonomy'  => 'city',
                    'field'     => 'slug',
                    'terms'     => $city
                ),
            );

            if ( ! empty( $category ) ) {
                $args_tax_query_category = array(
                    'taxonomy'  => 'event_category',
                    'field'     => 'term_id',
                    'terms'     => $category
                );
                array_push( $args_tax_query, $args_tax_query_category );
            }

            if ( ! empty( $id ) ) {

                $args['p'] = $id;
            }

            $args['tax_query'] = $args_tax_query;

            $query = new WP_Query( $args );

            $posts = $query->posts;
            
            // Instanciate arrays to populate it later
            $include_array = array();
            $date_days = array();

            // No need filter if there is only one post found
            if ( $query->found_posts > 1 ) {

                foreach ( $posts as $post ) {

                    // For each post, retrieve all current terms
                    $terms = get_the_terms( $post, 'event_category' );

                    // For each current terms 
                    foreach( $terms as $term ) {
                        
                        // Push in array the ID of current term to include it later 
                        // in wp_dropdown_categories
                        array_push( $include_array, $term->term_id );
                    }
                }


                // Date
                $output .= '<select class="js-filter Filters__select Button-base opacity-100">';
                $output .= '<option value="0" selected="selected">';
                $output .= __( 'Tous les évènements', 'feh' );
                $output .= '</option>';

                //loop over each event
                foreach ( $posts as $post ){
                    
                    //get the meta you need form each event
                    $date_day = get_post_meta( $post->ID, '_date_day', true );

                    // var_dump($post);
                    // If value isn't already in array, add it
                    if( ! empty( $date_day ) && ! in_array( $date_day, $date_days ) ) {
                        array_push( $date_days, $date_day );
                    }
                    
                };

                
                // For each value in array
                foreach ( $date_days as $date_day ) {

                    $output_date = '<option value="';
                    $output_date .= __( $date_day, 'feh' );
                    $output_date .= '">';
                    $output_date .= ucfirst( __( $date_day, 'feh' ) );
                    $output_date .= '</option>';

                    $output .= $output_date;

                }

                $output .= '</select>';

                $args = array(
                    'taxonomy'              => 'event_category',
                    'orderby'               => 'term_order',
                    'hierarchical'          => 1,
                    'include'               => $include_array,
                    'value_field'           => 'slug',
                    'class'                 => 'js-filter Filters__select Button-base opacity-100',
                    'show_option_none'      => __( 'Tous les parcours', 'feh' ),
                    'option_none_value'     => '0',
                    'hide_if_empty'         => true,
                    'echo'                  => 0
                );

                $output .= wp_dropdown_categories( $args );
            }
            
            $output .= '</div></div></div></div>';
            
        }

        if( is_admin() ) {
            // Return our map container markup
            return '<div id="map_canvas" style="height:' . esc_attr( $size['height'] ) . '; width:' . esc_attr( $size['width'] ) . ';"></div>';
        } 

        // Return our map container markup
        return '<div class="js-map l-single-map__container">' . $output . '<div class="map_canvas" style="height:' . esc_attr( $size['height'] ) . '; width:' . esc_attr( $size['width'] ) . ';"></div></div>';


    }
}


endif; // class_exists check
?>