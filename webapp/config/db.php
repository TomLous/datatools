<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];

// live?
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:unix_socket=/cloudsql/yii-framework:db;charset=utf8',
    'emulatePrepare' => true,
    'username' => 'dbuser',
    'password' => 'dbpass',
    'charset' => 'utf8',
];
