<?php
/**
 * API: https://developers.google.com/places/documentation/index
 *
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     DataInterface
 * Datetime:     28/04/14 15:13
 */

namespace DataInterface\GoogleMaps;


use DataInterface\GoogleMaps\GoogleMapsPlaceTypes;
use DataInterface\DataInterface;
use DataInterface\Exception\IncompatibleInterfaceException;
use DataInterface\Exception\IncompatibleInputException;
use models\Address;
use models\GeoLocation;

class GoogleMapsPlaces extends DataInterface
{


    //'apiKey' => $GOOGLE_MAPS_PLACES_APIKEY, // server api key as created in developer console
//'limit'  => $GOOGLE_MAPS_PLACES_LIMIT, // max. request units per 24h (1000, but 100000 after registering)
//'nearbysearchUnit' => $GOOGLE_MAPS_PLACES_NEARBYSEARCH_UNIT, // request unit for nearby search (1)
//'textsearchUnit' => $GOOGLE_MAPS_PLACES_TEXTSEARCH_UNIT,  // request unit for text search (10)
//'radarsearchunit' =>  $GOOGLE_MAPS_PLACES_RADARSEARCH_UNIT // request unit for radar search (5)
    protected $apiKey = null;
    protected $limit = null;
    protected $nearbysearchUnit = null;
    protected $textsearchUnit = null;
    protected $radarsearchUnit = null;


    /**
     * Constanst used for this API
     */
    const apiUrl = 'https://maps.googleapis.com/maps/api/place/';
    const returnType = 'json';
    const sensor = 'false'; //  Indicates whether or not the Place request came from a device using a location sensor (e.g. a GPS) to determine the location sent in this request.


    function nearbySearch($params = array())
    {
        // sanitize input params
        if (!is_array($params)) {
            throw new IncompatibleInputException('Missing properties');
        }

        // define params
        $latitude = 0;
        $longitude = 0;
        $radius = 1000;
        $rankby = null;
        $keyword = null;
        $language = null;
        $name = null;
        $opennow = null;
        $types = null;
        $pagetoken = null;



        $inputGeoLocation = null;
        $queryParameters = array();

        /**
         * Check input params / defaults
         */

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


        if (isset($params['radius']) && is_scalar($params['radius']) && $params['radius']>0) {
            $radius = intval($params['radius']);
        }

        if (isset($params['rankby']) && is_scalar($params['rankby']) && ($params['rankby']=='distance' || $params['rankby']=='prominence')) {
            $rankby = $params['rankby'];
        }

        //https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1
        if (isset($params['language']) && is_scalar($params['language']) && strlen($params['language']) < 6) {
            $language = $params['language'];
        }

        if (isset($params['name']) && is_scalar($params['name'])) {
            $name = $params['name'];
        }

        if (isset($params['keyword']) && is_scalar($params['keyword'])) {
            $keyword = $params['keyword'];
        }

        if (isset($params['opennow']) && is_scalar($params['opennow'])) {
            $opennow = strtolower($params['opennow'])=='true' || $params['opennow']?'true':'false';
        }

        if (isset($params['pagetoken']) && is_scalar($params['pagetoken'])) {
            $pagetoken = $params['pagetoken'];
        }

        if(isset($params['types'])){
            $types = $params['types'];
        }elseif(isset($params['typesCategory'])){
            $methodName = 'get'.$params['typesCategory'].'Types';//
            if(method_exists(__NAMESPACE__.'\GoogleMapsPlaceTypes', $methodName)){
                $types = call_user_func(array(__NAMESPACE__ .'\GoogleMapsPlaceTypes', $methodName));
            }
        }


        /**
         * create query params
         */
        $queryParameters['location'] = $latitude . ',' . $longitude;
        if ($rankby != 'distance') {
            $queryParameters['radius'] = $radius;
        }

        if ($rankby !== null) {
            $queryParameters['rankby'] = $rankby;
        }

        if ($language !== null) {
            $queryParameters['language'] = $language;
        }

        if ($name !== null) {
            $queryParameters['name'] = $name;
        }

        if ($keyword !== null) {
            $queryParameters['keyword'] = $keyword;
        }

        if ($opennow !== null) {
            $queryParameters['opennow'] = $opennow;
        }


        if ($types !== null) {
            if(is_array($types)){
                $queryParameters['types'] = implode('|', $types);
            }else{
                $queryParameters['types'] = $types;
            }
        }

        if ($pagetoken !== null) {
            $queryParameters['pagetoken'] = $pagetoken;
        }


        // create a new URL for this request e.g. https://maps.googleapis.com/maps/api/place/[endpoint]/[type]/?
        $requestUrl = $this->buildUrl('nearbysearch', $queryParameters);

        return $this->doRequestAndInterpretJSON($requestUrl);
    }


    private function doRequestAndInterpretJSON($url)
    {
        $returnData = array();
        $returnData['Meta'] = array();

        // Retrieve JSON for url
        $json = $this->doJSONGetRequest($url);


        if(isset($json['next_page_token'])){
            $returnData['Meta']['next_page_token'] = $json['next_page_token'];
        }





        $returnData['data'] =  $json['results'];

        return $returnData;
    }




    private function buildUrl($endpoint, $queryParameters = array())
    {
        $queryParameters['sensor'] = self::sensor;
        $queryParameters['key'] = $this->apiKey;
        $queryString = http_build_query($queryParameters);
        $url = self::apiUrl . $endpoint . '/' . self::returnType . '?' . $queryString;
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