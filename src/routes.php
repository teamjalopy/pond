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
    /*$form = $req->getParsedBody();
    $user = new Pond\User;
    $user->email = @$form['email'];
    $user->name = @$form['name'];
    $user->type = @$form['type'];
    $user->password = @$form['password'];
    $user->save();*/
    $reg = new \Pond\UserController($this);
    // $var = $reg->registrationHandler($req, $res);
    // var_dump($var);
    return $res;
});
