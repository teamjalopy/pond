<?php
// Routes

$app->get('/', function ($request, $response, $args) {

    $this->logger->info("Pond '/' route");

    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/users/{user_id}', function($req, $res, $args) {
    $users = Pond\User::find($args['user_id']);
    $stat = new \Pond\StatusContainer($users);
    $stat->message("Here is requested user");
    return $res->withJson($stat);
});


$app->post('/users', function($req, $res, $args) {
  $reg = new \Pond\Reg($this);
  $valid = $reg->userHandler($req, $res);
});
