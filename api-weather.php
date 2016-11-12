<?php

/*
Plugin Name: API Weather
Plugin URI: http://johnpaulwhatnow.com/
Description: A simple plugin that adds a weather shortcode to wordpress
Version: 1.0
Author: John Paul
Author URI: http://johnpaulwhatnow.com/
License: GPL2
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/*--------------------------------------------*
* Plugin Configuration
*--------------------------------------------*/
$accuweather_api_key = 'XXXXXXXXXXXXXXXXXXXXXXXXXXX';

//load the needed files.  This plugin calls two classes directly. Both are included below.
require_once plugin_dir_path(__FILE__) . 'includes/class-curl-wrapper.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-api-weather.php';

//Here we get the root of the plugin. This is used for css and imgs, and is explained more in includes/class-api-weather.php.
$plugin_dir_url_root = plugin_dir_url(__FILE__);

// instantiate plugin's class
$ApiWeather = new ApiWeather(new CurlWrapper(), $plugin_dir_url_root, $accuweather_api_key);

//make the class globally availalble.
$GLOBALS['api_weather'] = $ApiWeather;

// Since the "api_weather" shortcode can be placed in widgets, let's be sure to make to run the "do_shortcode" filter for these text areas.
//In the future, this should be moved to an admin setting.
add_filter('widget_text', 'do_shortcode');

//add this plugin's only shortcode "api_ weather".
add_shortcode('api_weather', array($ApiWeather, 'api_weather_func'));

//enque the scripts, so they are included at the right time.
add_action('wp_enqueue_scripts', array($ApiWeather, 'api_weather_enqueue_scripts'));