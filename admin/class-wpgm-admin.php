<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.19h47.fr
 * @since      1.0.0
 *
 * @package    wpgm
 * @subpackage wpgm/includes
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    wpgm
 * @subpackage wpgm/includes
 * @author     Jérémy Levron levronjeremy@19h47.fr
 */
class WPGM_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $latitude, $longitude, $default_post_types, $gmap_key ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->default_post_types = $default_post_types;

        $this->gmap_key = $gmap_key;
	}


	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'google-maps' );

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            plugin_dir_url( ( __FILE__ ) ) . 'js/' . $this->plugin_name . '-admin.js',
            array(
                'google-maps'
            ),
            $this->version,
            false
        );

		wp_localize_script(
            $this->plugin_name . '-admin',
            $this->plugin_name . '_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );
	}


	/**
     * AJAX callback to lookup an address on Google
     *
     * @since  1.0
     * @return void
     */
    public function google_address_search() {

        //Empty vars in case we return nothing
        $latitude = '';
        $longitude = '';

        // Sanitize our input
        $address = sanitize_text_field( $_REQUEST['address'] );

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode( $address ) . '&key=' . $this->gmap_key;

        //use the WordPress HTTP API to call the Google Maps API and get coordinates
        $result = wp_remote_get( $url );

        if( ! is_wp_error( $result ) ) {

            $json = json_decode( $result['body'] );

            //set lat/long for address from JSON response
            $latitude = $json->results[0]->geometry->location->lat;
            $longitude = $json->results[0]->geometry->location->lng;
        }


        // Send back our coordinates
        echo json_encode(
            array(
                'latitude'  => $latitude,
                'longitude' => $longitude
            )
        );

        die();
    }
}