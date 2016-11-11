<?php
/**
 * Name: CurlWrapper
 * Description: This is a short class that abstracts making cURL request. If the request is invalid, then we will fail silently. 
 *
 */
class CurlWrapper
{
    public function request($endpoint){
        //endpoint we are hitting
        $url = $endpoint;

        //curl init
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        //execute
        $response = curl_exec($ch);

        //get headers
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //Do we have a valid request?
        if($httpcode == 200 || $httpcode == 304){
            //we have a valid request
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            //json decode to php array
            $response = json_decode($body, true);
            return $response;
        } else{
            //in this case, fail silently
            exit();

        }




    }
}