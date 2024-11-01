<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.tenbucks.io
 * @since             1.0.0
 * @package           Tenbucks
 *
 * @wordpress-plugin
 * Plugin Name:       Tenbucks
 * Plugin URI:        https://www.tenbucks.io
 * Description:       This plugin allow you to use Tenbucks extensions for WooCommerce.
 * Version:           1.1.0
 * Author:            Web In Color
 * Author URI:        http://www.webincolor.fr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tenbucks
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tenbucks-activator.php
 */
function activate_tenbucks() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tenbucks-activator.php';
	Tenbucks_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tenbucks-deactivator.php
 */
function deactivate_tenbucks() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tenbucks-deactivator.php';
	Tenbucks_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tenbucks' );
register_deactivation_hook( __FILE__, 'deactivate_tenbucks' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tenbucks.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tenbucks() {

	$plugin = new Tenbucks();
	$plugin->run();

}
run_tenbucks();
