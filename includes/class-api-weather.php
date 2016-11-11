<?php
/**
 * Title: ApiWeather
 * Description: This is the main class that contains the logic to utilize the AccuWeather API, parse the shortcode attributes and render the html.
 */
final class ApiWeather {

    /*--------------------------------------------*
    * Properties
    *--------------------------------------------*/
    /**
     * Specifies what version of the plugin we're using.
     */
    private $pluginVersion;

    /**
     * Specifies the name of this plugin.
     */
    private $pluginName;

    /**
     * This is the AccuWeather API Key. In the future, this would be good to move to the admin panel.
     * For now this is hardcoded in the constructor.
     */
    private $apiKey;

    /**
     * This is a small utility used to use PHP's cURL library.
     */
    private $curlWrapper;

    /**
     * If the user doesn't provide a location string, we'll use this location by default.
     */
    private $defaultLocation;

    /**
     * This is the endpoint used by AccuWeather to get a location key from a location string.
     */
    private $locationEndpoint;

    /**
     * This is the endpoint used by AccuWeather to get a location's current conditions based off a location key.
     */
    private $currentConditionsEndpoint;

    /**
     * This is the endpoint used by AccuWeather to get a location key from an IP address.
     */
    private $ipEndpoint;

    /**
     * This is the tempeture units. Either "metric" or "imperial". The default is set in the constructor.
     */
    private $units;

    /**
     * This is the root of this paticular plugin. Identifying this in the constructor makes it easy to always find the correct path for css and img paths.
     */
    private $pluginDirRootPath;

    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/

    /**
     * Initializes the plugin by setting api parameters and setting dependencies.
     */
    function __construct($curlWrapper, $plugin_dir_url_root) {
        //plugin attributes
        $this->pluginName = 'api-weather';
        $this->pluginVersion = '1.0.0';

        //set API Key
        $this->apiKey = 'GouPKXdpolqMzM1nGixB8JMzBfKll9s3';

        //set curl wrapper used for calls
        $this->curlWrapper = $curlWrapper;

        //set default location
        $this->defaultLocation = 'Nashville, TN';

        //set the url we'll be using for retreiving the location ID (used to get forcast)
        $this->locationEndpoint = 'http://dataservice.accuweather.com/locations/v1/search?q=';

        //set the url we'll be using to get the current conditions
        $this->currentConditionsEndpoint = 'http://dataservice.accuweather.com/currentconditions/v1/';

        //set the url used to get the user's location by ip address
        $this->ipEndpoint = 'http://dataservice.accuweather.com/locations/v1/cities/ipaddress?q=';

        //by default, we'll use the imperial system
        $this->units = 'imperial';

        //used to grab the assets from the root level of this plugin, in case the location of this class moves around.
        $this->pluginDirUrlRoot = $plugin_dir_url_root;

        //added this filter in case other classes / plugins need access
        //ref: http://wordpress.stackexchange.com/questions/61437/php-error-with-shortcode-handler-from-a-class
        add_filter( 'get_api_weather_instance', array($this, 'getInstance') );


    } // end constructor

    /*--------------------------------------------*
    * Getters
    *--------------------------------------------*/
    //in the future, how we resolve this API key could be more complicated, so let's wrap this in a simple method for maintainability.
    public function getApiKey(){
        return $this->apiKey;
    }


    /*--------------------------------------------*
    * Other Methods
    *--------------------------------------------*/
    /**
     * Name: api_weather_func
     * Description: This method is tied to the WP "add_shortcode" method in the "api-weather.php" on the root of this plugin. This is the primary method used to execute the shortcode.
     * Note: The casing is different on this method because it used by wordpress directly, so it follows the WP function convention.
     */
    public  function api_weather_func($atts){
        $atts = shortcode_atts(
            array(
                'units' => $this->units,
                'use_user_location' => false,
                'location'=>$this->defaultLocation,
                'include_icon'=>false
            ), $atts, 'api_weather' );
        //did the user specify units?
        if( !empty($atts['units'])){
            //are the units metric? (check user casing)
            if( strtolower($atts['units']) == 'metric'){
                $this->units = 'metric';
            } else{
                $this->units = 'imperial';
            }
        }


        //does the shortcode use the "use_user_location" flag, as this would trump a particular location
        if( $atts['use_user_location'] !== false) {
            $location_key = $this->getLocationKeyByIp();

        } else{
            //now that we do not have a high priority use_user_location flag, let's see if they provided a specific location
            //did the user specify a location?
            if( !empty($atts['location'])){
                $location_key = $this->getLocationKey($atts['location']);
            } else{
                $location_key = $this->getLocationKey($this->defaultLocation);
            }
        }


        $current_conditions = $this->getCurrentConditions($location_key);
        //did the user specify to include the icon?
        if( $atts['include_icon'] !== false) {
            $current_conditions['include_icon'] = true;
        } else{
            $current_conditions['include_icon'] = false;
        }
        $current_conditions_html = $this->renderCurrentConditions($current_conditions);

        return $current_conditions_html;
    }

    /**
     * Name: GetInstance
     * Description: This method allows other plugins and the site's functions.php have access to the current instance of this plugin.
     */
    public function getInstance()
    {
        return $this; // return the object
    }

    /**
     * Name: GetLocationKey
     * Description: This method takes in a location string and returns the AccuWeather location key.
     */
    private function getLocationKey($location_str = ''){
        //if we don't have a location string, then use the default
        if( empty($location_str) ){
            $location_str = $this->defaultLocation;
        }
        //url encode location str
        $location_str = urlencode($location_str);
        //get api key
        $api_key = $this->getApiKey();

        //create API url
        //ref: https://developer.accuweather.com/accuweather-locations-api/apis/get/locations/v1/search
        $url =$this->locationEndpoint . $location_str . '&apikey=' . $api_key;

        //curl the request
        $response = $this->curlWrapper->request($url);

        //get actual location key
        $location_key = $response[0]['Key'];
        return $location_key;



    }

    /**
     * Name: getLocationKeyByIp
     * Description: Similar to the GetLocationKey method, but uses PHP's globals to get the user's IP address.
     */
    private function getLocationKeyByIp(){
        $ip = $this->getUserIpAddress();

        //get api key
        $api_key = $this->getApiKey();

        //create API url
        //ref:
        $url =$this->ipEndpoint . $ip . '&apikey=' . $api_key;

        //curl the request
        $response = $this->curlWrapper->request($url);

        //get actual location key
        $location_key = $response['Key'];
        return $location_key;

    }

    /**
     * Name: getUserIpAddress
     * Description: Helper method to getLocationKeyByIp.
     */
    private function getUserIpAddress(){
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return apply_filters( 'wpb_get_ip', $ip );
    }

    /**
     * Name: getCurrentConditions
     * Description: uses the location key from one of the "getLocationKey" methods and sends another request to AccuWeather's servers to get the current conditions for that location key.
     */
    private function getCurrentConditions($location_key){
        //get api key
        $api_key = $this->getApiKey();

        //create API url
        //ref: https://developer.accuweather.com/accuweather-current-conditions-api/apis/get/currentconditions/v1/%7BlocationKey%7D
        $url =$this->currentConditionsEndpoint . $location_key . '?apikey=' . $api_key;

        //curl the request
        $response = $this->curlWrapper->request($url);

        //let's get the right units based on the user
        $temperature = array();
        if($this->units == 'metric'){
            $temperature = $response[0]['Temperature']['Metric'];
        } else{
            $temperature = $response[0]['Temperature']['Imperial'];
        }

        //let's get the weather icon
        $weather_icon_number = $response[0]['WeatherIcon'];
        $weather_icon = $this->pluginDirUrlRoot . 'public/images/icons/weather-icon-' . $weather_icon_number. '.png';

        //weather text
        $weather_text = $response[0]['WeatherText'];
        //return everything we need
        $current_conditions = array(
            'temperature'=>$temperature,
            'weather_icon'=>$weather_icon,
            'weather_text'=>$weather_text
        );
        return $current_conditions;


    }

    /**
     * Name: renderCurrentConditions
     * Description: A small utility method to render html for the current conditions
     */
    private function renderCurrentConditions($current_conditions){
        $html = '<div id="api-weather-shell">';

        $html .='<div id="api-weather-temperature" class="api-weather-column">';
        $html .= $current_conditions['temperature']['Value'] . '&deg;  ' . $current_conditions['temperature']['Unit'];
        $html .='</div>';

            $html .= '<div id="api-weather-weather-text" class="api-weather-column">';
                $html .= $current_conditions['weather_text'];
            $html .='</div>';

            if($current_conditions['include_icon']){
                $html .='<div id="api-weather-icon-shell" class="api-weather-column">';
                    $html .= '<img id="api-weather-icon" src="'.$current_conditions['weather_icon'].'" alt="' . $current_conditions['weather_text'] . '" />';
                $html .='</div>';
            }

        $html .='</div>';
        return $html;
    }

    /**
     * Name: api_weather_enqueue_scripts
     * Description: Small method to use this plugin's css.
     * Note: The casing is different on this method because it used by wordpress directly, so it follows the WP function convention.
     */
    public function api_weather_enqueue_scripts(){
        //use styles
        wp_enqueue_style( $this->pluginName, $this->pluginDirUrlRoot . 'public/css/api-weather.css', array(), $this->pluginVersion, 'all' );
    }

}