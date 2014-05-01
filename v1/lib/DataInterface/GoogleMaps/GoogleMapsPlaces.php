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


use DataInterface\Exception\InterfaceQuotaExceededException;
use DataInterface\GoogleMaps\GoogleMapsPlaceTypes;
use DataInterface\DataInterface;
use DataInterface\Exception\IncompatibleInterfaceException;
use DataInterface\Exception\IncompatibleInputException;
use models\Address;
use models\GeoLocation;

class GoogleMapsPlaces extends DataInterface
{

    /**
     * @var null server api key as created in developer console
     */
    protected $apiKey = null;

    /**
     * @var null max. request units per 24h (1000, but 100000 after registering)
     */
    protected $limit = null;

    /**
     * @var null request unit for nearby search (1)
     */
    protected $nearbysearchUnit = null;

    /**
     * @var null request unit for text search (10)
     */
    protected $textsearchUnit = null;

    /**
     * @var null request unit for radar search (5)
     */
    protected $radarsearchUnit = null;


    /**
     * Constanst used for this API
     */
    const apiUrl = 'https://maps.googleapis.com/maps/api/place/';
    const returnType = 'json';
    const sensor = 'false'; //  Indicates whether or not the Place request came from a device using a location sensor (e.g. a GPS) to determine the location sent in this request.

    /**
     * Performs nearby search Query on Google Maps places API
     * @see: https://developers.google.com/places/documentation/search#PlaceSearchRequests
     * Required params:
     * double latitude & double longitude or geoLocation (GeoLocation)
     * optional:
     * int radius (default 1000)
     * string rankby
     * string language
     * string key
     * string name
     * string keyword
     * boolean opennow
     * boolean sensor
     * string pagetoken
     * string types or array types or string typesCategory => GoogleMapsPlaceTypes::get[typesCategory]Types()
     *
     * @param array $params
     * @return array|null
     * @throws \DataInterface\Exception\IncompatibleInputException
     */
    public function nearbySearch($params = array())
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
        $key = $this->apiKey;
        $sensor = self::sensor;
        $follow_pagetoken = false;


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


        if (isset($params['radius']) && is_scalar($params['radius']) && $params['radius'] > 0) {
            $radius = intval($params['radius']);
        }

        if (isset($params['rankby']) && is_scalar($params['rankby']) && ($params['rankby'] == 'distance' || $params['rankby'] == 'prominence')) {
            $rankby = $params['rankby'];
        }

        //https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1
        if (isset($params['language']) && is_scalar($params['language']) && strlen($params['language']) < 6) {
            $language = $params['language'];
        }

        if (isset($params['key']) && is_scalar($params['key'])) {
            $key = $params['key'];
        }

        if (isset($params['name']) && is_scalar($params['name'])) {
            $name = $params['name'];
        }

        if (isset($params['keyword']) && is_scalar($params['keyword'])) {
            $keyword = $params['keyword'];
        }

        if (isset($params['opennow']) && is_scalar($params['opennow'])) {
            $opennow = strtolower($params['opennow']) == 'true' || $params['opennow'] == 1 ? 'true' : 'false';
        }

        if (isset($params['sensor']) && is_scalar($params['sensor'])) {
            $sensor = strtolower($params['sensor']) == 'true' || $params['sensor'] == 1 ? 'true' : 'false';
        }


        if (isset($params['pagetoken']) && is_scalar($params['pagetoken'])) {
            $pagetoken = $params['pagetoken'];
        }

        if (isset($params['follow_pagetoken']) && is_scalar($params['follow_pagetoken'])) {
            $follow_pagetoken = strtolower($params['follow_pagetoken']) == 'true' || $params['follow_pagetoken'] == 1;
        }

        if (isset($params['types'])) {
            $types = $params['types'];
        } elseif (isset($params['typesCategory'])) {
            $methodName = 'get' . $params['typesCategory'] . 'Types'; //
            if (method_exists(__NAMESPACE__ . '\GoogleMapsPlaceTypes', $methodName)) {
                $types = call_user_func(array(__NAMESPACE__ . '\GoogleMapsPlaceTypes', $methodName));
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
            if (is_array($types)) {
                $queryParameters['types'] = implode('|', $types);
            } else {
                $queryParameters['types'] = $types;
            }
        }

        $queryParameters['key'] = $key;
        $queryParameters['sensor'] = $sensor;

        if ($pagetoken !== null) {
            $queryParameters['pagetoken'] = $pagetoken;
        }


        // create a new URL for this request e.g. https://maps.googleapis.com/maps/api/place/[endpoint]/[type]/?
        $requestUrl = $this->buildUrl('nearbysearch', $queryParameters);

        // increment used quota
        $this->incrementUsedQueries($this->nearbysearchUnit);

        // json returned
        $returnData = $this->doRequestAndInterpretJSON($requestUrl, $follow_pagetoken);

        $returnData['GeoLocationProvided'] = $inputGeoLocation;

        return $returnData;
    }

    /**
     * Performs text search Query on Google Maps places API
     * @see: https://developers.google.com/places/documentation/search#TextSearchRequests
     * Required params:
     * string query
     * optional:
     * double latitude & double longitude or geoLocation (GeoLocation)
     * int radius (default 1000)
     * string language
     * string key
     * boolean opennow
     * boolean sensor
     * string pagetoken
     *
     * @param array $params
     * @return array|null
     * @throws \DataInterface\Exception\IncompatibleInputException
     */
    public function textSearch($params = array())
    {
        // sanitize input params
        if (!is_array($params)) {
            throw new IncompatibleInputException('Missing properties');
        }

        // define params
        $query = null;
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
        $key = $this->apiKey;
        $sensor = self::sensor;
        $follow_pagetoken = false;

        $inputGeoLocation = null;
        $queryParameters = array();

        /**
         * Check input params / defaults
         */

        if (isset($params['query']) && is_scalar($params['query'])) {
            $query = $params['query'];
        } else {
            throw new IncompatibleInputException('Missing query property');

        }

        if (isset($params['latitude']) && is_scalar($params['latitude']) && isset($params['longitude']) && is_scalar($params['longitude'])) {
            $latitude = $params['latitude'];
            $longitude = $params['longitude'];
            $inputGeoLocation = new GeoLocation($latitude, $longitude);

        } elseif (isset($params['geoLocation']) && $params['geoLocation'] instanceof GeoLocation) {
            $inputGeoLocation = $params['geoLocation'];
            $latitude = $inputGeoLocation->getLatitude();
            $longitude = $inputGeoLocation->getLongitude();
        }


        if (isset($params['radius']) && is_scalar($params['radius']) && $params['radius'] > 0) {
            $radius = intval($params['radius']);
        }


        //https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1
        if (isset($params['language']) && is_scalar($params['language']) && strlen($params['language']) < 6) {
            $language = $params['language'];
        }

        if (isset($params['key']) && is_scalar($params['key'])) {
            $key = $params['key'];
        }


        if (isset($params['opennow']) && is_scalar($params['opennow'])) {
            $opennow = strtolower($params['opennow']) == 'true' || $params['opennow'] == 1 ? 'true' : 'false';
        }

        if (isset($params['sensor']) && is_scalar($params['sensor'])) {
            $sensor = strtolower($params['sensor']) == 'true' || $params['sensor'] == 1 ? 'true' : 'false';
        }


        if (isset($params['pagetoken']) && is_scalar($params['pagetoken'])) {
            $pagetoken = $params['pagetoken'];
        }

        if (isset($params['follow_pagetoken']) && is_scalar($params['follow_pagetoken'])) {
            $follow_pagetoken = strtolower($params['follow_pagetoken']) == 'true' || $params['follow_pagetoken'] == 1;
        }

        /**
         * create query params
         */
        $queryParameters['query'] = $query;

        if ($inputGeoLocation !== null) {
            $queryParameters['location'] = $latitude . ',' . $longitude;
            $queryParameters['radius'] = $radius;
        }


        if ($language !== null) {
            $queryParameters['language'] = $language;
        }


        if ($opennow !== null) {
            $queryParameters['opennow'] = $opennow;
        }

        $queryParameters['key'] = $key;
        $queryParameters['sensor'] = $sensor;

        if ($pagetoken !== null) {
            $queryParameters['pagetoken'] = $pagetoken;
        }


        // create a new URL for this request e.g. https://maps.googleapis.com/maps/api/place/[endpoint]/[type]/?
        $requestUrl = $this->buildUrl('textsearch', $queryParameters);

        // increment used quota
        $this->incrementUsedQueries($this->textsearchUnit);

        // json returned
        $returnData = $this->doRequestAndInterpretJSON($requestUrl, $follow_pagetoken);

        if ($inputGeoLocation) {
            $returnData['GeoLocationProvided'] = $inputGeoLocation;
        }

        return $returnData;
    }


    /**
     * Performs radar search Query on Google Maps places API
     * @see: https://developers.google.com/places/documentation/search#RadarSearchRequests
     * Required params:
     * string query
     * optional:
     * double latitude & double longitude or geoLocation (GeoLocation)
     * int radius (default 1000)
     * string language
     * string key
     * boolean opennow
     * boolean sensor
     * string pagetoken
     *
     * @param array $params
     * @return array|null
     * @throws \DataInterface\Exception\IncompatibleInputException
     */
    public function radarSearch($params = array())
    {
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
        $key = $this->apiKey;
        $sensor = self::sensor;
        $follow_pagetoken = false;

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


        if (isset($params['radius']) && is_scalar($params['radius']) && $params['radius'] > 0) {
            $radius = intval($params['radius']);
        }

        if (isset($params['rankby']) && is_scalar($params['rankby']) && ($params['rankby'] == 'distance' || $params['rankby'] == 'prominence')) {
            $rankby = $params['rankby'];
        }

        //https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1
        if (isset($params['language']) && is_scalar($params['language']) && strlen($params['language']) < 6) {
            $language = $params['language'];
        }

        if (isset($params['key']) && is_scalar($params['key'])) {
            $key = $params['key'];
        }

        if (isset($params['name']) && is_scalar($params['name'])) {
            $name = $params['name'];
        }

        if (isset($params['keyword']) && is_scalar($params['keyword'])) {
            $keyword = $params['keyword'];
        }

        if (isset($params['opennow']) && is_scalar($params['opennow'])) {
            $opennow = strtolower($params['opennow']) == 'true' || $params['opennow'] == 1 ? 'true' : 'false';
        }

        if (isset($params['sensor']) && is_scalar($params['sensor'])) {
            $sensor = strtolower($params['sensor']) == 'true' || $params['sensor'] == 1 ? 'true' : 'false';
        }


        if (isset($params['pagetoken']) && is_scalar($params['pagetoken'])) {
            $pagetoken = $params['pagetoken'];
        }

        if (isset($params['follow_pagetoken']) && is_scalar($params['follow_pagetoken'])) {
            $follow_pagetoken = strtolower($params['follow_pagetoken']) == 'true' || $params['follow_pagetoken'] == 1;
        }

        if (isset($params['types'])) {
            $types = $params['types'];
        } elseif (isset($params['typesCategory'])) {
            $methodName = 'get' . $params['typesCategory'] . 'Types'; //
            if (method_exists(__NAMESPACE__ . '\GoogleMapsPlaceTypes', $methodName)) {
                $types = call_user_func(array(__NAMESPACE__ . '\GoogleMapsPlaceTypes', $methodName));
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
            if (is_array($types)) {
                $queryParameters['types'] = implode('|', $types);
            } else {
                $queryParameters['types'] = $types;
            }
        }

        $queryParameters['key'] = $key;
        $queryParameters['sensor'] = $sensor;

        if ($pagetoken !== null) {
            $queryParameters['pagetoken'] = $pagetoken;
        }


        // create a new URL for this request e.g. https://maps.googleapis.com/maps/api/place/[endpoint]/[type]/?
        $requestUrl = $this->buildUrl('radarsearch', $queryParameters);

        // increment used quota
        $this->incrementUsedQueries($this->radarsearchUnit);

        // json returned
        $returnData = $this->doRequestAndInterpretJSON($requestUrl, $follow_pagetoken);

        $returnData['GeoLocationProvided'] = $inputGeoLocation;

        return $returnData;
    }


    /**
     * Makes a request with url to google maps API and interprets the meta data of the result
     * When follow_pagetoken isset, it will keep following the 'next page' until no more data (pages) is available or the $maxLoopCount is reached.
     * @param $url
     * @param bool $follow_pagetoken
     * @param int $maxLoopCount 5
     * @throws \DataInterface\Exception\IncompatibleInterfaceException
     * @throws \DataInterface\Exception\IncompatibleInputException
     * @throws \DataInterface\Exception\InterfaceQuotaExceededException
     * @return array|null
     */
    private function doRequestAndInterpretJSON($url, $follow_pagetoken=false, $maxLoopCount=5)
    {
        $returnData = array();
        $returnData['Meta'] = array();
//        $returnData['Meta']['url'] = $url;
        $nextUrl = null;

        // Retrieve JSON for url
        $json = $this->doJSONGetRequest($url);

        // check for error message (optional)
        $errorMessage = isset($json['error_message']) ? $json['error_message'] : null;

        // should have a status, else the api is broken
        if (!array_key_exists('status', $json)) {
            throw new IncompatibleInterfaceException('Missing data in result from request to ' . $url . ' message: ' . $errorMessage);
        }

        // Handle status
        // https://developers.google.com/places/documentation/search#PlaceSearchStatusCodes
        if ($json['status'] == 'OVER_QUERY_LIMIT') {
            throw new InterfaceQuotaExceededException('Access Denied to service reason: ' . $json['status'] . ' for request to ' . $url . ' message: ' . $errorMessage);
        } elseif ($json['status'] == 'REQUEST_DENIED') {
            throw new IncompatibleInterfaceException('Access Denied to service reason: ' . $json['status'] . ' for request to ' . $url . ' message: ' . $errorMessage);
        } elseif ($json['status'] == 'INVALID_REQUEST') {
            throw new IncompatibleInputException('Failed request to service reason: ' . $json['status'] . ' for request to ' . str_replace($this->apiKey, '[key]', $url) . ' message: ' . $errorMessage);
        } elseif ($json['status'] == 'ZERO_RESULTS') {
            return null;
        }

        // set next page tokeb
        if (isset($json['next_page_token'])) {
            if($follow_pagetoken){
                $nextUrl = preg_replace('/([?&])pagetoken=[^&]+(&|^)/is', '$1$2', $url).'&pagetoken='.$json['next_page_token'];
//                $nextUrl = $url.='&pagetoken='.$json['next_page_token'];
//                $returnData['Meta']['nexturl'] = $nextUrl;
            }else{
                $returnData['Meta']['next_pagetoken'] = $json['next_page_token'];
            }

        }

        // loop results and reformat them to general api needs
        $returnData['data'] = array();
        foreach ($json['results'] as $result) {
            $googleMapsPlace = new GoogleMapsPlace();

            $googleMapsPlace->setGeoLocation(new GeoLocation($result['geometry']['location']['lat'], $result['geometry']['location']['lng']));
            $googleMapsPlace->setId($result['id']);
            $googleMapsPlace->setReference($result['reference']);
            $googleMapsPlace->setName($result['name']);
            $googleMapsPlace->setTypes($result['types']);
            $googleMapsPlace->setRating($result['rating']);


            if(isset($result['formatted_address']) || isset($result['vicinity'])){
                $address = new Address();
                $address->setAddressString(isset($result['formatted_address'])?$result['formatted_address']:$result['vicinity']);
                $address->parseString();
                $googleMapsPlace->setAddress($address);
            }


            $returnData['data']['GoogleMapsPlace_'.$result['id']] = $googleMapsPlace;
        }

        //
        if($nextUrl && $maxLoopCount > 0){
            sleep(2); // to get google to generate the page token data
            $newData = $this->doRequestAndInterpretJSON($nextUrl, $follow_pagetoken, --$maxLoopCount);
            $returnData['data'] = array_merge($returnData['data'], $newData['data']);
            $returnData['Meta'] = array_merge($returnData['Meta'], $newData['Meta']);
        }

        return $returnData;
    }


    /**
     * Creates a Google Maps places api call
     * @param $endpoint
     * @param array $queryParameters
     * @return string
     */
    private function buildUrl($endpoint, $queryParameters = array())
    {
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