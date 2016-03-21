<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/lib/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Bootstrap Eloquent
// [CITE] http://www.slimframework.com/2013/03/21/slim-and-laravel-eloquent-orm.html
$connectionFactory = new \Illuminate\Database\Connectors\ConnectionFactory();
$connection = $connectionFactory->make($settings['eloquent']);

$resolver = new \Illuminate\Database\ConnectionResolver();
$resolver->addConnection('default', $connection);
$resolver->setDefaultConnection('default');

\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
