<?php
/**
 * Created by PhpStorm.
 * User: tomlous
 * Date: 23/04/14
 * Time: 15:38
 */

namespace DataInterface;


use DataInterface\Exception\IncompatibleInterfaceException;

class Geocodefarm extends DataInterface
{

    protected $apiKey = null; // account API KEY
    protected $limit = 0; // max. number of requests
    protected $limitResetTime = null; // time of reset

    protected static $remainingQueries = 0;
    protected static $usedQueries = 0;

    const apiUrl = 'https://www.geocodefarm.com/api/';
    const returnType = 'json';


    public function forwardCoding($encodingString)
    {
        // create a new URL for this request e.g. https://www.geocodefarm.com/api/forward/json/[key]/address
        $requestUrl = $this->buildUrl('forward', array($encodingString));

        //
        $json = $this->doRequestAndInterpretJSON($requestUrl);

        return $json;
    }


    /**
     * Makes a request with url to geocodefarm and interprets the meta data of the result
     * @param $url
     * @return array|null
     * @throws Exception\IncompatibleInterfaceException
     */
    private function doRequestAndInterpretJSON($url)
    {
        // Retrieve JSON for url
        $json = $this->doJSONGetRequest($url);

        if(!array_key_exists('geocoding_results', $json)){
            throw new IncompatibleInterfaceException('Missing  geocoding_results in result from request to ' . $url);
        }

        $json = $json['geocoding_results'];

        // Check result
        $neededProperties = array('STATUS', 'ACCOUNT', 'ADDRESS', 'COORDINATES');

        foreach ($neededProperties as $property) {
            if (!array_key_exists($property, $json)) {
                throw new IncompatibleInterfaceException('Missing property ' . $property . ' in result from request to ' . $url);
            }
        }

        // Hanlde Status Info
        $statusInfo = $json['STATUS'];

        if($statusInfo['status'] == 'FAILED, ACCESS_DENIED'){
            throw new IncompatibleInterfaceException('Access Denied to service reason: '.$statusInfo['access'].' for request to '.$url);
        }
        else if($statusInfo['status'] == 'FAILED, NO_RESULTS'){
            return null;
        }

        // Set Account Info
        $accountInfo = $json['ACCOUNT'];

        self::$remainingQueries = (int)$accountInfo['remaining_queries'];
        self::$usedQueries = (int)$accountInfo['used_today'];


        return $json;
        // @todo return address & geo location


    }

    /**
     * Builds a geocodefarm url based on static, local and user params
     * @param $endpoint
     * @param array $properties
     * @return string
     */
    private function buildUrl($endpoint, $properties=array()){
        $parameters = implode('/',array_map('urlencode', $properties));
        $url = self::apiUrl . $endpoint . '/' . self::returnType .'/'.$this->apiKey . '/' .$parameters;
        return $url;
    }

    /**
     * Retrieves JSON from given url and returns it as a PHP array
     * @param $url
     * @return array json
     * @throws IncompatibleInterfaceException when returnd data is not JSON
     *
     */
    private function doJSONGetRequest($url)
    {
        // We don't use CURL, since GAE won't support it
//        //  Initiate curl
//        $ch = curl_init();
//
//        // Disable SSL verification
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//
//        // Will return the response, if false it print the response
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//        // Set the url
//        curl_setopt($ch, CURLOPT_URL, $url);
//
//        // Execute
//        $result = curl_exec($ch);
//
//        // Close connection
//        curl_close($ch);

        // retrieve data from url
        $result = file_get_contents($url);

        // Decode the result to JSON php array
        $json = json_decode($result, true);

        // If it's not json, throw an alert
        if ($json === null) {
            throw new IncompatibleInterfaceException('Invalid (non-json) result from request to ' . $url . ' result: ' . $result);
        }

        return $json;
    }


} 