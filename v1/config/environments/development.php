<?php
/**
 * Config file for development enviroment
 *
 * User: tomlous
 * Date: 23/04/14
 * Time: 13:04
 */
$app->config('debug', true);
$app->config('mode', $_ENV['APP_ENV']);

$log = $app->getLog();
$log->setEnabled(true);
$log->setLevel(\Slim\Log::DEBUG);