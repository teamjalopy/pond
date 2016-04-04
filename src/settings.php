<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],

        // Eloquent settings
        'eloquent' => [
            'driver' => 'mysql',
            'host' => getenv('POND_DB_HOST'),
            'database' => getenv('POND_DB_NAME'),
            'username' => getenv('POND_DB_USERNAME'),
            'password' => getenv('POND_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix' => '',
        ],

        'token' => [
            'key' => getenv('POND_JWT_SIGN_KEY'),
            'iss' => 'http://pondedu.me',
            'aud' => 'http://pondedu.me',
            'lifetime' => 1 * 7 * 24 * 60 * 60, // 1 week
        ]

    ],
];
