<?php
/**
 * Config file for production enviroment
 *
 * User: tomlous
 * Date: 23/04/14
 * Time: 13:04
 */
// include gitignored globals from inc file
$configFile =   $_ENV['APP_ENV'] . '.inc.php';
$absPath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $configFile);
if (!file_exists($absPath)) {
    throw new Exception('Unknown environmental config param file : ' . $absPath);
}
require_once($configFile);


/**
 * Slim config
 */
$app->config('debug', true);
$app->config('mode', $_ENV['APP_ENV']);

$app->config('log.writer', new \Slim\SysLogWriter());

$log = $app->getLog();
$log->setEnabled(true);
$log->setLevel(\Slim\Log::DEBUG);

/**
 * Global settings
 */
$dataInterface = array(
    'Geocodefarm' => array(
        'Geocodefarm' => array(
            'apiKey' => $GEOCODEFARM_APIKEY, // account API KEY
            'limit' => $GEOCODEFARM_LIMIT, // max. number of requests
            'limitResetTime' => $GEOCODEFARM_LIMIT_RESET_TIME // time of reset
        ),
    ),
    'GoogleMaps' => array(
        'GoogleMapsPlaces' => array(
            'apiKey' => $GOOGLE_MAPS_PLACES_APIKEY, // server api key as created in developer console
            'limit'  => $GOOGLE_MAPS_PLACES_LIMIT, // max. request units per 24h (1000, but 100000 after registering)
            'nearbysearchUnit' => $GOOGLE_MAPS_PLACES_NEARBYSEARCH_UNIT, // request unit for nearby search (1)
            'textsearchUnit' => $GOOGLE_MAPS_PLACES_TEXTSEARCH_UNIT,  // request unit for text search (10)
            'radarsearchUnit' =>  $GOOGLE_MAPS_PLACES_RADARSEARCH_UNIT, // request unit for radar search (5)
            'detailhUnit' =>  $GOOGLE_MAPS_PLACES_DETAIL_UNIT // request unit for detail search (1)
        ),
    )
);
$app->environment()['DataInterface'] = $dataInterface;