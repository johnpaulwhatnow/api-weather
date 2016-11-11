<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Api_Weather
 *
 * @wordpress-plugin
 * Plugin Name:       API Weather
 * Plugin URI:        http://johnpaulwhatnow.com/
 * Description:       A simple plugin that adds a weather shortcode to wordpress
 * Version:           1.0
 * Author:            John Paul
 * Author URI:        http://johnpaulwhatnow.com/
 * License:           GPL2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       api-weather
 * Domain Path:       /languages
 */
 
 



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-api-weather-activator.php
 */
function activate_api_weather() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-api-weather-activator.php';
	Api_Weather_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-api-weather-deactivator.php
 */
function deactivate_api_weather() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-api-weather-deactivator.php';
	Api_Weather_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_api_weather' );
register_deactivation_hook( __FILE__, 'deactivate_api_weather' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-api-weather.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_api_weather() {

	$plugin = new Api_Weather();
	$plugin->run();

}
run_api_weather();
