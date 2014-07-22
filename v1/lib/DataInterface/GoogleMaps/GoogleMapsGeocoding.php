<?php
/**
 * Created by PhpStorm.
 * User: tomlous
 * Date: 23/04/14
 * Time: 15:38
 * @todo fix section
 */

namespace DataInterface\GoogleMaps;


use DataInterface\DataInterface;
use DataInterface\Exception\IncompatibleInterfaceException;
use DataInterface\Exception\IncompatibleInputException;
use DataInterface\Exception\InterfaceQuotaExceededException;
use models\Address;
use models\GeoLocation;

class GoogleMapsGeocoding extends DataInterface
{
    /**
     * @var null apiKey for communication with GoogleMaps
     */
    protected $apiKey = null; // account API KEY

    /**
     * @var null max. request units per 24h (1000, but 100000 after registering)
     */
    protected $limit = null;

    /**
     * @var null time of day limits get reset
     * @todo do something with this info
     */
    protected $limitResetTime = null; // time of reset


    /**
     * Constanst used for this API
     */
    const apiUrl = 'https://maps.googleapis.com/maps/api/';
    const returnType = 'json';

    /**
     * Request geoloaction for addressNl string, passed as addressString or addressNl (\Address) property in array, optional key param
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
        $language = 'en';


        if (isset($params['addressString']) && is_scalar($params['addressString'])) {
            $addressString = $params['addressString'];
            $inputAddress = new Address();
            $inputAddress->setAddressString($addressString);
            $inputAddress->parseString();
        } elseif (isset($params['addressNl']) && $params['addressNl'] instanceof Address) {
            $inputAddress = $params['addressNl'];
            $addressString = $inputAddress->getAddressString();
        } else {
            throw new IncompatibleInputException('Missing addressString or addressNl property');
        }

        if (isset($params['key']) && is_scalar($params['key'])) {
            $customKey = $params['key'];
        }

        if (isset($params['language']) && is_scalar($params['language'])) {
            $language = $params['language'];
        }


        // create a new URL for this request e.g. https://maps.googleapis.com/maps/api/geocode/json[key]/addressNl
        $requestUrl = $this->buildUrl('geocode', array('address'=>$addressString,'language'=>$language),$customKey);

        // do request to GoogleMapss
        $returnData = $this->doRequestAndInterpretJSON($requestUrl);

        $returnData['AddressProvided'] = $inputAddress;

        return $returnData;
    }

    /**
     * Request addressNl for latitude, longitude variable, passed as doubles (latitude, longitude) or geoLocation (\GeoLocation) property in array, optional key param
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
        $language = 'en';

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

        if (isset($params['language']) && is_scalar($params['language'])) {
            $language = $params['language'];
        }

        // create a new URL for this request e.g. https://www.geocodefarm.com/api/reverse/json/[key]/latitude/longitude
        $requestUrl = $this->buildUrl('geocode', array('latlng' => "{$latitude},{$longitude}", 'language'=>$language), $customKey);

        // do request to GoogleMaps
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

        if (!array_key_exists('results', $json)) {
            throw new IncompatibleInterfaceException('Missing  results in result from request to ' . $url);
        }

        $statusInfo = $json['status'];
        $results = $json['results'];


        // Check result
        $neededProperties = array('address_components', 'formatted_address', 'geometry', 'types');
        $lastAccuracy = -1;
        foreach($results as $result){

            foreach ($neededProperties as $property) {
                if (!array_key_exists($property, $result)) {
                    throw new IncompatibleInterfaceException('Missing property ' . $property . ' in result from request to ' . $url);
                }
            }

            $coordinateInfo = $result['geometry'];

            $accuracy = $this->translateAccuracy($coordinateInfo['location_type']);

            if($lastAccuracy < $accuracy){

                $geoLocation = null;

                if (is_numeric($coordinateInfo['location']['lat']) && is_numeric($coordinateInfo['location']['lng'])) {
                    $geoLocation = new GeoLocation($coordinateInfo['location']['lat'], $coordinateInfo['location']['lng']);
                }

                $returnData['GeoLocationReturned'] = $geoLocation;

                // Address Info
                $addressInfo = $result['address_components'];
                $addressStr = $result['formatted_address'];
                $address = new Address();


                if (strlen($addressStr) > 0) {
                    $address->setAddressString($addressStr);
                    $address->parseString();
                }

                if (count($addressInfo) > 0) {
                    // @todo set all parts
                }

                $returnData['AddressReturned'] = $address;

                $lastAccuracy = $accuracy;
                $returnData['Meta']['resultAccuracy'] = $accuracy;
            }

        }

        // Handle Status Info
        // https://www.geocodefarm.com/dashboard/documentation/


        if ($statusInfo == 'REQUEST_DENIED' || $statusInfo == 'INVALID_REQUEST' || $statusInfo == 'UNKNOWN_ERROR') {
            throw new IncompatibleInterfaceException('Access Denied to service reason: ' . $statusInfo . ' for request to ' . $url);
        } elseif ( $statusInfo == 'OVER_QUERY_LIMIT') {
            throw new InterfaceQuotaExceededException('Access Denied to service reason: ' . $statusInfo . ' for request to ' . $url);
        } elseif ($statusInfo == 'ZERO_RESULTS') {
            return null;
        }

//        // @todo do something with this info
//        // Set Account Info
//        $accountInfo = $json['ACCOUNT'];
//
//        static::setRemainingQueries((int)$accountInfo['remaining_queries'], true);
//        static::setUsedQueries((int)$accountInfo['used_today'], true);

        // Lat / Long

        $returnData['Meta']['raw'] = $json;

//        // Statistics
//        $statisticsInfo = isset($json['STATISTICS']) ? $json['STATISTICS'] : array();
//        $returnData['Meta']['loadTime'] = isset($statisticsInfo['load_time']) ? $statisticsInfo['load_time'] : null;


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
        $properties['key'] = $key;
        $parameters = http_build_query($properties);
        $url = self::apiUrl . $endpoint . '/' . self::returnType  . '?' . $parameters;
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

//        print_r($url);
//        print_r($result);

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
            // indicates that the returned result is a precise geocode for which we have location information accurate down to street address precision..
            case 'ROOFTOP':
                $accuracy = 1.0;
                break;
            // indicates that the returned result reflects an approximation (usually on a road) interpolated between two precise points (such as intersections). Interpolated results are generally returned when rooftop geocodes are unavailable for a street address.
            case 'RANGE_INTERPOLATED':
                $accuracy = 0.7;
                break;
            //  indicates that the returned result is the geometric center of a result such as a polyline (for example, a street) or polygon (region).
            case 'GEOMETRIC_CENTER':
                $accuracy = 0.4;
                break;
            // indicates that the returned result is approximate.
            case 'APPROXIMATE':
                $accuracy = 0.2;
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