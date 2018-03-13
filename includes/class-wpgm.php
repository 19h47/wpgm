<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.19h47.fr
 * @since      1.0.0
 *
 * @package    wpgm
 * @subpackage wpgm/includes
 */


/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    wpgm
 * @subpackage wvgm/includes
 * @author     Jérémy Levron <jeremylevron@19h47.fr>
 */

class WPGM {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WPGM_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;


    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;


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
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct() {

        $this->plugin_name = 'wpgm';
        $this->version = '1.0.0';

        $this->gmap = array(
            'version'   => '3.32',
            'key'       => 'AIzaSyARcbB73-4Xmg9vSkA30EUxslzgvnRsrQY'
        );

        $this->geographical_coordinates = array(
            'latitude'  => '',
            'longitude' => ''
        );

        $this->default_post_types = array( 'event' );

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();

        $this->define_metabox_hooks();

        $this->include_before_theme();
    }


    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WPGM_Loader. Orchestrates the hooks of the plugin.
     * - WPGM_Admin. Defines all hooks for the admin area.
     * - WPGM_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of
         * the core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpgm-loader.php';


        /**
         * The class responsible for defining all actions that occur in the
         * admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpgm-admin.php';


        /**
         * The class responsible for defining all actions relating to metaboxes.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpgm-admin-metaboxes.php';


        /**
         * The class responsible for defining all actions that occur in the
         * public-facing side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpgm-public.php';


        $this->loader = new WPGM_Loader();
    }


    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new WPGM_Admin(
            $this->get_plugin_name(),
            $this->get_version(),
            $this->get_latitude(),
            $this->get_longitude(),
            $this->get_default_post_types(),
            $this->gmap['key']
        );

        $this->loader->add_action(
            'admin_enqueue_scripts',
            $plugin_admin,
            'enqueue_scripts'
        );

        $this->loader->add_action(
            "wp_ajax_{$this->plugin_name}_address_search",
            $plugin_admin,
            'google_address_search'
        );

        $this->loader->add_action(
            "wp_ajax_nopriv_{$this->plugin_name}_address_search",
            $plugin_admin,
            'google_address_search'
        );

        add_action( 'init', array( $this, 'register_scripts' ) );
    }


    /*
     * Include_before_theme
     *
     * This function will include core files before the theme's functions.php
     * file has been excecuted.
     */
    function include_before_theme() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'core/api.php';
    }


    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new WPGM_Public(
            $this->get_plugin_name(),
            $this->get_version(),
            $this->get_default_post_types()
        );


        $this->loader->add_shortcode(
            $this->plugin_name,
            $plugin_public,
            $this->plugin_name . '_shortcode'
        );


        $this->loader->add_action(
            "wp_ajax_{$this->plugin_name}_get_markers",
            $plugin_public,
            $this->plugin_name . '_get_markers'
        );


        $this->loader->add_action(
            "wp_ajax_nopriv_{$this->plugin_name}_get_markers",
            $plugin_public,
            $this->plugin_name . '_get_markers'
        );
    }


    /**
     * Register all of the hooks related to metaboxes
     *
     * @since       1.0.0
     * @access      private
     */
    private function define_metabox_hooks() {

        $plugin_metaboxes = new WPGM_Admin_Metaboxes(
            $this->get_plugin_name(),
            $this->get_version(),
            $this->get_latitude(),
            $this->get_longitude(),
            $this->get_default_post_types()
        );

        $this->loader->add_action( 'add_meta_boxes', $plugin_metaboxes, 'add_metaboxes' );
        $this->loader->add_action( 'save_post', $plugin_metaboxes, 'map_metabox_save' );
    }


    /**
     * Register scripts
     *
     * @access public
     * @author Jérémy Levron <jeremylevron@19h47.fr>
     */
    public function register_scripts() {

        wp_register_script(
            'google-maps',
            "https://maps.googleapis.com/maps/api/js?v={$this->gmap['version']}&key={$this->gmap['key']}",
            '',
            $this->version,
            null,
            false
        );

        wp_register_script(
            $this->plugin_name,
            plugin_dir_url( ( __FILE__ ) ) . 'js/' . $this->plugin_name . '.js',
            array(
                'google-maps'
            ),
            null,
            true
        );
    }


    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }


    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }


    /**
     * Retrieve latitude
     *
     * @return latitude
     * @author Jérémy Levron <jeremylevron@19h47.fr>
     */
    public function get_latitude() {
        return $this->geographical_coordinates['latitude'];
    }


    /**
     * Retrieve the longitude
     *
     * @return longitude
     * @author Jérémy Levron <jeremylevron@19h47.fr>
     */
    public function get_longitude() {
        return $this->geographical_coordinates['longitude'];
    }


    /**
     * Retrieve the default post types
     */
    public function get_default_post_types() {
        return $this->default_post_types;
    }


    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin
     */
    public function get_loader() {
        return $this->loader;
    }


    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}