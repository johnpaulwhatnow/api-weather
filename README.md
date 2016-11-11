API Weather

A simple wordpress plugin that provides shortcode for getting the current weather conditions. This uses AccuWeather’s API. 

# Installation Instructions

## Copy the "api-weather" folder into your site’s plugin directory.

Typically this is: "/wp-content/plugins/".

## Create an AccuWeather API Key.

To use this plugin, you will need to register as a developer with AccuWeather on their developer portal, then create an application. Creating an application will automatically create an API Key. For more information, visit the AccuWeather Developer website here: [https://developer.accuweather.com/](https://developer.accuweather.com/).

## Copy and Paste your API Key into the plugin. 

* Open up the main plugin file ("plugins/api-weather/api-weather.php").

* On line 21, there is a PHP variable called ```$accuweather_api_key```.  Replace that string with your AccuWeather API key. 

## Activate the Plugin from your Wordpress Admin Dashboard.

The plugin’s title is "Api Weather". Find the plugin in your site’s plugin directory and activate it. 

# Usage

## Using the shortcode.

This plugin provides one shortcode to render a location’s current conditions. And that shortcode is:

[api_weather]

## Plugin Options

### Location

By default, this plugin uses the location of "Nashville, TN". You can change that location with by using the location attribute like this:

[api_weather location="New Orleans, LA”]

### Using the User’s Location

If you’d like your to personalize the current condition weather for each visitor, then set the following attribute like this:

[api_weather use_user_location="1”]

This will find the user’s location based on IP address.  

**Note:** This feature takes precedence over the basic "location" field. So if a user in Seattle and you’ve set up the shortcode like this:  

[api_weather use_user_location="1” location=”Virginia Beach, VI”]

The plugin would render the weather for Seattle.

### Using a Weather Icon

By default, this plugin only includes the temperature and a word describing the weather ("Sunny", “Partly Cloudy”).  To include a matching weather icon, do the following:

[api_weather include_icon="1”]

### Metric or Imperial Units

This plugin uses the imperial (fahrenheit) system by default, but you can switch it by using the following attribute:

[api_weather units="metric”]

# Plugin Architecture

## Api-weather.php

This file includes the required classes, sets the API key variable, and includes the Wordpress configuration.  The Wordpress configuration was intentionally left on this file, so that one could easily find the ways this plugin integrates with Wordpress.

## Includes

This folder contains the two main classes used by this plugin.

## includes/class-api-weather.php

This is the main class that contains the logic to utilize the AccuWeather API, parse the shortcode attributes and render the html.

## includes/class-curl-wrapper.php

This is a short class that abstracts making cURL request. If the request is invalid, then it will fail silently. If you’d rather take another action on failed request, then implement that at line 38. 

## Public

This directory contains all the assets needed for the public facing part of the plugin.  This plugin uses AccuWeather’s weather icons to render images. You can find more about those weather icons here: [https://developer.accuweather.com/weather-icons](https://developer.accuweather.com/weather-icons)

