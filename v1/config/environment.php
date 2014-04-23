<?php
/**
 * Includes specific environmental config files based on HOST_NAME
 *
 * User: tomlous
 * Date: 23/04/14
 * Time: 13:06
 */

$_ENV['APP_ENV'] = null;

if ($_SERVER['HTTP_HOST'] == 'localhost:9080') {
    $_ENV['APP_ENV'] = 'development';
} elseif ($_SERVER['HTTP_HOST'] == 'datatools01.appspot.com') {
    $_ENV['APP_ENV'] = 'production';
} else {
    throw new Exception('Unknown environment: ' . $_SERVER['HTTP_HOST']);
}

$configFile = 'environments' . DIRECTORY_SEPARATOR . $_ENV['APP_ENV'] . '.php';
$absPath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $configFile);
if (!file_exists($absPath)) {
    throw new Exception('Unknown environmental config file : ' . $absPath);
}

require_once($configFile);

