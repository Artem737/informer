<?php

namespace app\config;

try {
//    $host = EnvHelper::getParam('DB');
    $host = '192.168.10.1';
} catch (\Exception $ex) {
    $host = '192.168.10.6';
}

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlsrv:Server=' . $host . ';Database=Aquamir',
//    'username' => 'UserPublic',
//    'password' => 'VDT_pps',
    'username' => 'Guest',
    'password' => 'Gu_5t-!2%',
    'charset' => 'windows-1252',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
