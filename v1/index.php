<?php
$_SERVER['SERVER_PORT'] = 80;
require '../vendor/autoload.php';

$app = new \Slim\Slim();

require_once('config/config.php');


$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});
$app->run();