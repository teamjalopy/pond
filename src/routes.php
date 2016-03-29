<?php
// Routes

$app->get('/api/users/{user_id}', function($req, $res, $args) {
    $users = Pond\User::find($args['user_id']);
    $stat = new \Pond\StatusContainer($users);
    $stat->success();
    $stat->message("Here is requested user");
    return $res->withJson($stat);
});


$app->post('/api/users', function($req, $res, $args) {
    $reg = new \Pond\UserController($this);
    $res = $reg->registrationHandler($req, $res);
    return $res;
});
