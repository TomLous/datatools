<?php
$_SERVER['SERVER_PORT'] = 80;
require '../vendor/autoload.php';

$app = new \Slim\Slim();

require_once('config/config.php');


$app->get('/hello/:name', function ($name) {
    echo "Hello, $name in ".$_ENV['APP_ENV'];
});

$app->post('/DataInterface/:api/:endpoint', function ($api, $endpoint) use($app){
    $data = null;
    try{
        $className = '\\DataInterface\\' . $api;
        $apiInstance = new $className();

        $data = $apiInstance->$endpoint($app->request()->params());
    }catch (Exception $e){
        $data = array('message'=>$e->getMessage());
    }
    header("Content-Type: application/json");
    print json_encode($data);




});

$app->run();