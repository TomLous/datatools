<?php
/**
 * Created by PhpStorm.
 * User: tomlous
 * Date: 23/04/14
 * Time: 15:38
 * @todo fix section
 */

namespace DataInterface\Geocodefarm;


use DataInterface\DataInterface;
use DataInterface\Exception\IncompatibleInterfaceException;
use DataInterface\Exception\IncompatibleInputException;
use DataInterface\Exception\InterfaceQuotaExceededException;
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
     * Constanst used for this API
     */
    const apiUrl = 'https://www.geocodefarm.com/api/';
    const returnType = 'json';

    /**
     * Request geoloaction for address string, passed as addressString or address (\Address) property in array, optional key param
     * @param array $params
     * @throws \DataInterface\Exception\IncompatibleInputException
     * @return array|null
     */
    public function forwardCoding($params = array())
    {
        // sanitize input params
        if (!is_array($params)) {
            throw new IncompatibleInputException('Missing properties');
        }

        // define params
        $addressString = null;
        $inputAddress = null;
        $customKey = null;

        if (isset($params['addressString']) && is_scalar($params['addressString'])) {
            $addressString = $params['addressString'];
            $inputAddress = new Address();
            $inputAddress->setAddressString($addressString);
            $inputAddress->parseString();
        } elseif (isset($params['address']) && $params['address'] instanceof Address) {
            $inputAddress = $params['address'];
            $addressString = $inputAddress->getAddressString();
        } else {
            throw new IncompatibleInputException('Missing addressString or address property');
        }

        if (isset($params['key']) && is_scalar($params['key'])) {
            $customKey = $params['key'];
        }


        // create a new URL for this request e.g. https://www.geocodefarm.com/api/forward/json/[key]/address
        $requestUrl = $this->buildUrl('forward', array($addressString),$customKey);

        // do request to Geocodefarms
        $returnData = $this->doRequestAndInterpretJSON($requestUrl);

        $returnData['AddressProvided'] = $inputAddress;

        return $returnData;
    }

    /**
     * Request address for latitude, longitude variable, passed as doubles (latitude, longitude) or geoLocation (\GeoLocation) property in array, optional key param
     * @param array $params
     * @throws \DataInterface\Exception\IncompatibleInputException
     * @return array|null
     */
    public function reverseCoding($params = array())
    {
        // sanitize input params
        if (!is_array($params)) {
            throw new IncompatibleInputException('Missing properties');
        }

        // define params
        $latitude = 0;
        $longitude = 0;
        $inputGeoLocation = null;
        $customKey = null;

        if (isset($params['latitude']) && is_scalar($params['latitude']) && isset($params['longitude']) && is_scalar($params['longitude'])) {
            $latitude = $params['latitude'];
            $longitude = $params['longitude'];
            $inputGeoLocation = new GeoLocation($latitude, $longitude);

        } elseif (isset($params['geoLocation']) && $params['geoLocation'] instanceof GeoLocation) {
            $inputGeoLocation = $params['geoLocation'];
            $latitude = $inputGeoLocation->getLatitude();
            $longitude = $inputGeoLocation->getLongitude();
        } else {
            throw new IncompatibleInputException('Missing latitude & longitude or geoLocation property');
        }

        if (isset($params['key']) && is_scalar($params['key'])) {
            $customKey = $params['key'];
        }

        // create a new URL for this request e.g. https://www.geocodefarm.com/api/reverse/json/[key]/latitude/longitude
        $requestUrl = $this->buildUrl('reverse', array($latitude, $longitude), $customKey);

        // do request to Geocodefarms
        $returnData = $this->doRequestAndInterpretJSON($requestUrl);

        $returnData['GeoLocationProvided'] = $inputGeoLocation;

        return $returnData;
    }


    /**
     * Makes a request with url to geocodefarm and interprets the meta data of the result
     * @param $url
     * @throws \DataInterface\Exception\IncompatibleInterfaceException
     * @throws \DataInterface\Exception\InterfaceQuotaExceededException
     * @return array|null
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
        // https://www.geocodefarm.com/dashboard/documentation/
        $statusInfo = $json['STATUS'];

        if ($statusInfo['status'] == 'FAILED, ACCESS_DENIED' || $statusInfo['status'] == 'API_KEY_INVALID' || $statusInfo['status'] == 'ACCOUNT_NOT_ACTIVE') {
            throw new IncompatibleInterfaceException('Access Denied to service reason: ' . $statusInfo['access'] . ' for request to ' . $url);
        } elseif ($statusInfo['status'] == 'BILL_PAST_DUE' || $statusInfo['status'] == 'OVER_QUERY_LIMIT') {
            throw new InterfaceQuotaExceededException('Access Denied to service reason: ' . $statusInfo['access'] . ' for request to ' . $url);
        } elseif ($statusInfo['status'] == 'FAILED, NO_RESULTS') {
            return null;
        }

        // @todo do something with this info
        // Set Account Info
        $accountInfo = $json['ACCOUNT'];

        static::setRemainingQueries((int)$accountInfo['remaining_queries'], true);
        static::setUsedQueries((int)$accountInfo['used_today'], true);

        // Lat / Long
        $coordinateInfo = $json['COORDINATES'];

        $geoLocation = null;

        if (is_numeric($coordinateInfo['latitude']) && is_numeric($coordinateInfo['longitude'])) {
            $geoLocation = new GeoLocation($coordinateInfo['latitude'], $coordinateInfo['longitude']);
        }

        $returnData['GeoLocationReturned'] = $geoLocation;

        // Address Info
        $addressInfo = $json['ADDRESS'];
        $address = new Address();


        if (strlen($addressInfo['address_returned']) > 0) {
            $address->setAddressString($addressInfo['address_returned']);
            $address->parseString();
        } elseif (strlen($addressInfo['address']) > 0) {
            $address->setAddressString($addressInfo['address']);
            $address->parseString();
        }

        $returnData['AddressReturned'] = $address;

        $returnData['Meta']['resultAccuracy'] = $this->translateAccuracy($addressInfo['accuracy']);

//        $returnData['Meta']['raw'] = $json;

        // Statistics
        $statisticsInfo = isset($json['STATISTICS']) ? $json['STATISTICS'] : array();
        $returnData['Meta']['loadTime'] = isset($statisticsInfo['load_time']) ? $statisticsInfo['load_time'] : null;


        return $returnData;
    }

    /**
     * Builds a geocodefarm url based on static, local and user params
     * @param $endpoint
     * @param array $properties
     * @param null $key
     * @return string
     */
    private function buildUrl($endpoint, $properties = array(), $key=null)
    {
        $key = $key===null?$this->apiKey:$key;
        $parameters = implode('/', array_map('rawurlencode', $properties));
        $url = self::apiUrl . $endpoint . '/' . self::returnType . '/' . $key . '/' . $parameters;
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

    private function translateAccuracy($accuracyString)
    {
        $accuracy = 0.0;

        switch ($accuracyString) {
            // This is the highest level of accuracy and usually indicates a spot-on match.
            case 'VERY ACCURATE':
                $accuracy = 1.0;
                break;
            // This is the second highest level of accuracy and usually indicates a range match, within a few hundred feet most.
            case 'GOOD ACCURACY':
                $accuracy = 0.7;
                break;
            //  This is the third level of accuracy and usually indicates a geographical area match, such as the metro area, locality, or city.
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

    protected function initQueryQuota(){
        static::setRemainingQueries($this->limit);
        static::setUsedQueries(0);

        $nextReset = strtotime(date('Y-m-d '). $this->limitResetTime);
        $nowTimestamp = time();

        if($nextReset < $nowTimestamp){
            $nextReset += (24*60*60);
        }
        static::setQuotaResetTimestamp($nextReset, true);
    }



}