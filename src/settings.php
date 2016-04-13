<?php

$env = require __DIR__ . '/../env.php';

return [
    'settings' => [
        'debug' => $env['POND_DEBUG'],

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'pond',
            'path' => __DIR__ . '/../logs/app.log',
        ],

        // Eloquent settings
        'eloquent' => [
            'driver' => 'mysql',
            'host' => $env['POND_DB_HOST'],
            'database' => $env['POND_DB_NAME'],
            'username' => $env['POND_DB_USERNAME'],
            'password' => $env['POND_DB_PASSWORD'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix' => '',
        ],

        // JWT settings
        'token' => [
            'key' => $env['POND_JWT_SIGN_KEY'],
            'iss' => 'http://pondedu.me',
            'aud' => 'http://pondedu.me',
            'lifetime' => 1 * 7 * 24 * 60 * 60, // 1 week
        ],

        //AWS settings
        'Ses' => [
            'profile' => 'default',
            'region' => 'us-west-2',
            'aws-access_key_id' => $env['AWS_ACCESS_KEY_ID'],
            'aws-secret_access_key' => $env['AWS_SECRET_ACCESS_KEY'],
        ]

    ],
];
