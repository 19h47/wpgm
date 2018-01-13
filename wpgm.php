<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the
 * plugin admin area. This file also includes all of the dependencies used by
 * the plugin, registers the activation and deactivation functions, and defines
 * a function that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       	WPGM
 * Plugin URI:        	http://www.19h47.fr
 * Description:       	Plugin allows posts to be linked to specific addresses
 * 						and coordinates and display plotted on a Google Map. Use
 * 						shortcode [wpgm] to display map directly in your
 * 						post/page. Map shows plots for each address added to the
 * 						post you are viewing.
 * Version:           	1.0.0
 * Author:            	Jérémy Levron
 * Author URI:        	http://www.19h47.fr
 * License:           	GPL-2.0+
 * License URI:       	http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       	WPGM
 * Domain Path:       	/languages
 */

// If this file is called directly, abort.
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpgm.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 				1.0.0
 */
function run_WPGM() {

	$plugin = new WPGM();
	$plugin->run();
}
run_WPGM();