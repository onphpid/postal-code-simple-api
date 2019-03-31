<?php
namespace OnPhpId\IndonesiaPostalCode;

use Pentagonal\DatabaseDBAL\Database;
use Slim\Container;
use Slim\Http\Environment;

$developmentMode = getenv('DEVELOPMENT_MODE');
return new Container([
    'settings' => [
        'displayErrorDetails' => $developmentMode == 'true' || $developmentMode == '1',
    ],
    'db' => function() : Database {
        $db = new Database([
            'driver' => Database::DRIVER_SQLITE,
            'path' => __DIR__ .'/../storage/database/sqlite_provinces.sqlite'
        ]);
        $db->connect();
        return $db;
    },
    'environment' => function() {
        $serverParams  = $_SERVER;
        // convert error with accept application/json only
        $serverParams['HTTP_ACCEPT'] = 'application/json';
        // FIX CLOUDFLARE HTTPS
        if (isset($serverParams['HTTP_X_FORWARDED_PROTO']) && $serverParams['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $serverParams['HTTPS'] = 'on';
        }
        // if does not have request time
        if (!isset($serverParams['REQUEST_TIME_FLOAT'])) {
            $serverParams['REQUEST_TIME_FLOAT'] = microtime(true);
            $serverParams['REQUEST_TIME'] = intval($serverParams['REQUEST_TIME_FLOAT']);
        }

        return Environment::mock($serverParams);
    },
]);
