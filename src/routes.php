<?php
// Routes

$app->post('/api/auth', function ($req, $res, $args) {
    $auth = new \Pond\Auth($this);
    return $auth->loginHandler($req, $res);
});
$app->get('/api/auth/{user_id}', function ($req, $res) {
    $auth = new \Pond\Auth($this);
    $uid = $req->getAttribute('user_id');
    $isAuth = $auth->isRequestAuthorized($req,$uid);
    return $isAuth ? $res->withStatus(200) : $res->withStatus(401);
});

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
