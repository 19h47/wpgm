<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.19h47.fr
 * @since      1.0.0
 *
 * @package    wpgm
 * @subpackage wpgm/includes
 */


/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    wpgm
 * @subpackage wpgm/public
 * @author     Jérémy Levron <jeremylevron@19h47.fr>
 */
class WPGM_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since      1.0.0
	 * @access     private
	 * @var        string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;


	/**
	 * The version of this plugin.
	 *
	 * @since      1.0.0
	 * @access     private
	 * @var        string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * Current city
	 *
	 * Will be update by shortcode
	 *
	 * @since      1.0.0
	 * @access     public
	 * @var        string    $city
	 */
	 public $city;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $default_post_types ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->default_post_types = $default_post_types;
		$this->city = 'Paris';

		// Require helper file
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpgm-helper.php';
	}

	/**
	 * Google Map shortcode handler
	 *
	 * @since       1.0
	 * @param       array  $atts An array of shortcode attributes
	 * @return      string       A string of html markup on success, empty on failure
	 */
	public function wpgm_shortcode( $atts ) {

		$map = '';

		// Set our shortcode args blank if not passed in
		extract(
			shortcode_atts(
				array(
					'height'    => '',
					'width'     => '',
					'category'  => '',
					'city'      => '',
					'event'     => '',
					'filter'    => '',
					'id'        => '',
					'selector'  => '',
				),
				$atts
			)
		);

		$this->setCity( $city );

		$this->enqueue_scripts( $city, $category, $id );

		// Generate our base map markup to return
		$map = WPGM_Helper::map_markup(
			array(
				'height'    => $height,
				'width'     => $width
			),
			$this->getCity(),
			$filter,
			$category,
			$id
		);

		return apply_filters( $this->plugin_name . '_shortcode', $map );
	}


	/**
	 * Set city
	 *
	 * @param       $value
	 */
	public function setCity( $value ) {

		$this->city = $value;
	}


	/**
	 * Get city
	 *
	 * @param       $value
	 */
	public function getCity() {

		return $this->city;
	}


	/**
	 * Enqueue script
	 */
	function enqueue_scripts( $city, $category = array(), $id = array() ) {

		wp_enqueue_script( 'google-maps' );
		// wp_enqueue_script( 'jquery-ui-maps' );
		wp_enqueue_script( $this->plugin_name );

		wp_localize_script(
			$this->plugin_name,
			$this->plugin_name . '_ajax',
			array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'city'      => $city,
				'category'  => $category,
				'id'        => $id,
			)
		);
	}


	/**
	 * wpgm_get_markers
	 */
	function wpgm_get_markers(){

		$list_markers = [];

		 $args = array(
			'posts_per_page'    => -1,
			'post_type'         => $this->default_post_types,
			'post_status'       => 'publish',
		);

		 // ID
		 if ( isset( $_POST['id'] ) && ! empty( $_POST['id'] ) ) {
			$args['post__in'] = array( $_POST['id'] );
		}

		$args_tax_query = array(
			'relation' => 'AND'
		);


		// City
		if ( isset( $_POST['city'] ) && ! empty( $_POST['city'] ) ) {

			$args_tax_query_city = array(
				'taxonomy'  => 'city',
				'field'     => 'slug',
				'terms'     => $_POST['city']
			);
			array_push( $args_tax_query, $args_tax_query_city );
		}


		// Category
		if ( isset( $_POST['category'] ) && ! empty( $_POST['category'] ) ) {
			$args_tax_query_category = array(
				'taxonomy'  => 'event_category',
				'field'     => 'term_id',
				'terms'     => $_POST['category']
			);
			array_push($args_tax_query, $args_tax_query_category);
		}


		// Add taxonomy query to WP_Query arguments
		$args['tax_query'] = $args_tax_query;

		$query = new WP_Query( $args );

		if( $query->found_posts < 1 ) {
			return;
		}

		while ( $query->have_posts() ) {

			$query->the_post();

			// Coordinates
			$coordinates = get_post_meta(
				$query->post->ID,
				'_' . $this->plugin_name . '_details',
				true
			);

			// print_r( $coordinates );

			// If post has latitude and longitude attach
			if ( empty ( $coordinates ) ) {
				continue;
			}

			// Event category
			$categories = get_the_terms( $query->post->ID, 'event_category' );


			// If so, generate our map output
			if ( $coordinates['latitude'] && $coordinates['longitude'] && $categories ) {

				// Empty array to stock categories
				$post_categories = [];

				// Empty array to stock filters
				$filters = [];

				// Make an array of all categories
				foreach( $categories as $category ) {

					array_push( $post_categories, $category->slug );
					array_push( $filters, $category->slug );
				};

				// Date
				$day_format = 'l';
				$hour_format = 'h';

				// Date in category
				if( have_rows( 'dates', $query->post->ID ) ) {

					while ( have_rows( 'dates', $query->post->ID ) ) {
						the_row();

						$unixtimestamp = strtotime( get_sub_field( 'date' ) );

						$current_date = date_i18n( $day_format, $unixtimestamp );

						if( ! in_array( $current_date, $post_categories ) ) {

							array_push( $filters, $current_date );
						}
					}
				}


				// Empty array for date output
				$post_dates = [];

				if( have_rows( 'dates', $query->post->ID ) ) {

					while ( have_rows( 'dates', $query->post->ID ) ) {

						the_row();

						// $date = get_sub_field( 'date' );

						$unixtimestamp = strtotime( get_sub_field( 'date' ) );

						$output  = '<span class="Date-event uppercase">';
						$output .= date_i18n( $day_format, $unixtimestamp );
						$output .= ' &#45;&nbsp;' . date_i18n( $hour_format, $unixtimestamp ) . 'h';
						$output .= '</span>';

						array_push( $post_dates, $output );
					}
				}


				// Thumbnail
				$thumbnail = get_the_post_thumbnail_url( $query->post->ID, 'medium' );


				// Permalink
				$permalink = get_the_permalink( $query->post->ID );


				// Marker informations
				$marker_infos = apply_filters(
					$this->plugin_name . '_get_gmap_marker_infos',
					array(
						'title'     => $query->post->post_title,
						'permalink' => $permalink,
						'content'   => $query->post->post_content,
						'date'      => $post_dates,
						'category'  => $post_categories,
						'filters'   => $filters,
						'address'   => get_wpgm_address( $query->post->ID ),
						'thumbnail' => $thumbnail
					)
				);


				$marker = array(
					'coordinates' => array(
						'latitude'  => $coordinates['latitude'],
						'longitude' => $coordinates['longitude']
					)
				);


				$marker = array_merge( $marker, $marker_infos );


				array_push( $list_markers, $marker );
			}
		}
		wp_reset_postdata();

		echo wp_json_encode(
			array(
				'markers' => $list_markers
			)
		);

		wp_die();
	}
}
