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

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Errors to log rather than to end user
$c = $app->getContainer();

// Exceptions get sent through this handler
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $c->get('logger')->error("[Exception] ".$exception->getMessage());

        $stat = new \Pond\StatusContainer();
        $stat->error("ObfuscatedInternalServerError");
        $stat->message("Something went wrong on our side!");

        if($c->get('settings')['debug']) {
            $stat->data = [
                'type' => get_class($exception),
                'errorMessage' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'trace' => $exception->getTrace()
            ];
        }

        return $response->withStatus(500)
                        ->withJson($stat);
    };
};

// PHP 7 Error objects get sent through this handler
$c['phpErrorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $c->get('logger')->error("[PHP Error] ".$exception->getMessage());

        $stat = new \Pond\StatusContainer();
        $stat->error("ObfuscatedInternalServerError");
        $stat->message("Something went wrong on our side!");

        if($c->get('settings')['debug']) {
            $stat->data = [
                'type' => get_class($exception),
                'errorMessage' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'trace' => $exception->getTrace()
            ];
        }

        return $response->withStatus(500)
                        ->withJson($stat);
    };
};

// Bootstrap Eloquent
// [CITE] https://github.com/illuminate/database/blob/master/README.md
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($settings['settings']['eloquent']);

use Illuminate\Container\Container;
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$c->get('logger')->info("Starting app");
$app->run();
