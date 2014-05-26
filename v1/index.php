<?php
// added for GAE purposes
$_SERVER['SERVER_PORT'] = 80;
require '../vendor/autoload.php';

$app = new \Slim\Slim();

require_once('config/config.php');

/**
 * Creat API routes for all DataInterfaces
 */
\DataInterface\DataInterface::createSlimAPIRoutes($app);

\Tool\Tool::createSlimToolRoutes($app);

//if ($app->config('debug')) {
//    $routes = $app->router()->getNamedRoutes();
//    foreach($routes as $route){
//        print_r($route);
//    }
//}

$app->get('/', function() use ($app){
    $app->render('index.php');
});

$app->run();