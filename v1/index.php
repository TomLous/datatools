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

$app->run();