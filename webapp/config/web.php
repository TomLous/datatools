<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');


$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
//        'assetManager'=>array(
//            // This is special Asset Manger which can work under Google App Engine
//            'class'=>'application.components.CGAssetManager',
//            // CHANGE THIS: Enter here your own Google Cloud Storage bucket name Google App Engine
//            'basePath'=>ENV_DEV
//                    ? Yii::getPathOfAlias('assets')   // basePath for development version, assets path alias was defined above
//                    : 'gs://yii-assets',    // basePath for production version
//            // CHANGE THIS: All files on Google Cloud Storage can be accessed via the URL below,
//            // note the bucket name at the end, should be the same as in basePath above
//            'baseUrl'=>ENV_DEV
//                    ? '/assets'                                            // baseUrl for development App Engine
//                    : 'http://commondatastorage.googleapis.com/yii-assets' // baseUrl for production App Engine
//        ),
        'request'=>array(
            'baseUrl' => '/',
            'scriptUrl' => '/',
        ),
//        'user'=>array(
//            // enable cookie-based authentication
//            'allowAutoLogin'=>true,
//        ),
        // uncomment the following to enable URLs in path-format

//        'urlManager'=>array(
//            'urlFormat'=>'path',
//            'baseUrl'=>'', // added to fix URL issues under Google App Engine
//            'rules'=>array(
//                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
//                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
//                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
//            ),
//        ),
    ],

    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
