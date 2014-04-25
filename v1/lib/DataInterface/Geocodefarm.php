<?php
/**
 * Created by PhpStorm.
 * User: tomlous
 * Date: 23/04/14
 * Time: 15:38
 * @todo fix section
 */

namespace DataInterface;


use DataInterface\Exception\IncompatibleInterfaceException;
use DataInterface\Exception\IncompatibleInputException;
use models\Address;
use models\GeoLocation;

class Geocodefarm extends DataInterface
{
    /**
     * @var null apiKey for communication with Geocodefarm
     */
    protected $apiKey = null; // account API KEY

    /**
     * @var int limit number of requests per day
     * @todo do something with this info
     */
    protected $limit = 0; // max. number of requests

    /**
     * @var null time of day limits get reset
     * @todo do something with this info
     */
    protected $limitResetTime = null; // time of reset

    /**
     * @var int remainingQueries
     * @todo do something with this info
     */
    protected static $remainingQueries = 0;

    /**
     * @var int usedQueries
     * @todo do something with this info
     */
    protected static $usedQueries = 0;

    /**
     * Constanst used for this API
     */
    const apiUrl = 'https://www.geocodefarm.com/api/';
    const returnType = 'json';

    /**
     * Request geoloaction for address string, passed as addressString or address (\Address) property in array
     * @param array $params
     * @return array|null
     * @throws Exception\IncompatibleInputException
     */
    public function forwardCoding($params = array())
    {
        // sanitize input params
        if (!is_array($params)){
            throw new IncompatibleInputException('Missing addressString property');
        }

        // define params
        $addressString = null;
        $inputAddress= null;

        if(isset($params['addressString']) && is_scalar($params['addressString'])) {
            $addressString = $params['addressString'];
            $inputAddress = new Address();
            $inputAddress->setAddressString($addressString);
            $inputAddress->parseString();
        }
        elseif(isset($params['address']) && $params['address'] instanceof Address) {
            $inputAddress = $params['address'];
            $addressString = $inputAddress->getAddressString();
        }

        // create a new URL for this request e.g. https://www.geocodefarm.com/api/forward/json/[key]/address
        $requestUrl = $this->buildUrl('forward', array($addressString));

        // do request to Geocodefarms
        $returnData = $this->doRequestAndInterpretJSON($requestUrl);

        $returnData['AddressProvided'] = $inputAddress;

        return $returnData;
    }


    /**
     * Makes a request with url to geocodefarm and interprets the meta data of the result
     * @param $url
     * @return array|null
     * @throws Exception\IncompatibleInterfaceException
     */
    private function doRequestAndInterpretJSON($url)
    {
        $returnData = array();
        $returnData['Meta'] = array();

        // Retrieve JSON for url
        $json = $this->doJSONGetRequest($url);

        if (!array_key_exists('geocoding_results', $json)) {
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

        // Handle Status Info
        $statusInfo = $json['STATUS'];

        if ($statusInfo['status'] == 'FAILED, ACCESS_DENIED') {
            throw new IncompatibleInterfaceException('Access Denied to service reason: ' . $statusInfo['access'] . ' for request to ' . $url);
        } else if ($statusInfo['status'] == 'FAILED, NO_RESULTS') {
            return null;
        }

        // @todo do something with this info
        // Set Account Info
        $accountInfo = $json['ACCOUNT'];

        self::$remainingQueries = (int)$accountInfo['remaining_queries'];
        self::$usedQueries = (int)$accountInfo['used_today'];

        // Lat / Long
        $coordinateInfo = $json['COORDINATES'];

        $geoLocation = null;

        if(is_numeric($coordinateInfo['latitude']) && is_numeric($coordinateInfo['longitude'])){
            $geoLocation = new GeoLocation($coordinateInfo['latitude'], $coordinateInfo['longitude']);
        }

        $returnData['GeoLocation'] = $geoLocation;

        // Address Info
        $addressInfo = $json['ADDRESS'];
        $address = new Address();


        if(strlen($addressInfo['address_returned']) > 0){
            $address->setAddressString($addressInfo['address_returned']);
            $address->parseString();
        }

        $returnData['AddressReturned'] = $address;

        $returnData['Meta']['resultAccuracy'] = $this->translateAccuracy($addressInfo['accuracy']);


        // Statistics
        $statisticsInfo = isset($json['STATISTICS'])?$json['STATISTICS']:array();
        $returnData['Meta']['loadTime'] = isset($statisticsInfo['load_time'])?$statisticsInfo['load_time']:null;


        return $returnData;
    }

    /**
     * Builds a geocodefarm url based on static, local and user params
     * @param $endpoint
     * @param array $properties
     * @return string
     */
    private function buildUrl($endpoint, $properties = array())
    {
        $parameters = implode('/', array_map('rawurlencode', $properties));
        $url = self::apiUrl . $endpoint . '/' . self::returnType . '/' . $this->apiKey . '/' . $parameters;
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
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        $result = curl_exec($ch);
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

    private function translateAccuracy($accuracyString){
        $accuracy = 0.0;

        switch($accuracyString){
            // This is the highest level of accuracy and usually indicates a spot-on match.
            case 'VERY ACCURATE':
                $accuracy = 1.0;
                break;
            // This is the second highest level of accuracy and usually indicates a range match, within a few hundred feet most.
            case 'GOOD ACCURACY':
                $accuracy = 0.7;
                break;
            //  This is the third level of accuracy and usually indicates a geographical area match, such as the metro area, town, or city.
            case 'ACCURATE':
                $accuracy = 0.3;
                break;
            // The accuracy of this result is unable to be determined and an exact match may or may not have been obtained.
            case 'UNKNOWN ACCURACY':
                $accuracy = 0.1;
                break;

        }

       return $accuracy;

    }


}