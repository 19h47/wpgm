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
class WPGM_Admin_Metaboxes {

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
	public function __construct( $plugin_name, $version, $latitude, $longitude, $default_post_types ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->default_post_types = $default_post_types;

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpgm-helper.php';
	}

    /**
     * Registers metaboxes with WordPress
     *
     * @since  1.0
     * @return void
     */
    public function add_metaboxes() {

        $post_types = apply_filters( $this->plugin_name . '_post_types', $this->default_post_types );

        foreach ( $post_types as $post_type ) {

            // add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );
            add_meta_box(
                $this->plugin_name . '_meta_box',
                __( 'Coordonnées Carte', $this->plugin_name ),
                array(
                    $this,
                    'show_metaboxes'
                ),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Output metabox markup
     *
     * @since  1.0
     * @return void
     */
    public function show_metaboxes() {

        if ( ! is_admin() ) {
            return;
        }

        global $post;

        $fields = $this->map_metabox_get_fields( $post->ID );

        include( plugin_dir_path( __FILE__ ) . "partials/{$this->plugin_name}-admin-metabox.php" );
    }


    /**
     * Save map metabox
     *
     * @since  1.0
     * @param  int      $post_id    The ID of the post we're saving
     * @return int      $post_id    The ID of the post we're saving
     */
    public function map_metabox_save( $post_id ) {

        // Verify nonce
        if ( ! isset( $_POST[$this->plugin_name . '_nonce'] ) || ! wp_verify_nonce( $_POST[$this->plugin_name . '_nonce'], $this->plugin_name . '_details' ) ) {
            return $post_id;
        }

        // Make sure we're not doing an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        // Sanitize our fields
        $fields = $this->map_metabox_sanitize_fields();

        // print_r($fields);

        // If we have fields, save them, if not, attempt to delete them
        $meta = ! empty( $fields ) ? update_post_meta( $post_id, "_{$this->plugin_name}_details", $fields ) : delete_post_meta( $post_id, "_{$this->plugin_name}_details" );

        return apply_filters( 'map_metabox_save', $post_id );
    }


    /**
     * Get and set our field defaults for metabox output
     *
     * @since  1.0
     * @param  int      $post   The post ID we're displaying our metabox on
     * @return array            An array of fields
     */
    private function map_metabox_get_fields( $post_id = '' ) {

        $fields = get_post_meta( $post_id, "_{$this->plugin_name}_details", true );

        if ( ! $fields ) {
            return;
        }

        $fields['address'] = ! empty( $fields['address'] ) ? $fields['address'] : '';
        $fields['latitude'] = ! empty( $fields['latitude'] ) ? $fields['latitude'] : '';
        $fields['longitude'] = ! empty( $fields['longitude'] ) ? $fields['longitude'] : '';
        // wp_die( var_dump( $fields ) );

        return apply_filters( 'map_metabox_get_fields', $fields );
    }


    /**
     * Sanitize metabox input fields
     *
     * @since  1.0
     * @return array  An array of sanitized fields
     */
    private function map_metabox_sanitize_fields() {
        $fields = array();

        if ( ! $_POST[$this->plugin_name . '_address'] ) {
            return;
        }

        if ( ! $_POST[$this->plugin_name . '_latitude'] ) {
            return;
        }

        if ( ! $_POST[$this->plugin_name . '_longitude'] ) {
            return;
        }

        // Sanitize our input fields
        $fields['address'] = sanitize_text_field( $_POST[$this->plugin_name . '_address'] );
        $fields['latitude'] = sanitize_text_field( $_POST[$this->plugin_name . '_latitude'] );
        $fields['longitude'] = sanitize_text_field( $_POST[$this->plugin_name . '_longitude'] );

        return apply_filters( 'map_metabox_sanitize_fields', $fields );
    }
}