<?php
// Routes

$app->get('/', function ($request, $response, $args) {

    $this->logger->info("Pond '/' route");

    return $this->renderer->render($response, 'index.phtml', $args);
});


$app->get('/users', function($req, $res, $args) {
    $users = \Pond\User::all();
    $stat = new \Pond\StatusContainer($users);
    $stat->message("Here are all the users");
    return $res->withJson($stat);
});
 
