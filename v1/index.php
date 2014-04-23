<?php
//$_SERVER['SERVER_PORT'] = 80;
require '../vendor/autoload.php';

$app = new \Slim\Slim();
$app->config('debug', true);
$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});
$app->run();