<?php
// Routes

$app->get('/', function ($request, $response, $args) {

    $this->logger->info("Pond '/' route");

    return $this->renderer->render($response, 'index.phtml', $args);
});


/*$app->get('/users', function($req, $res, $args) {
    $users = \Pond\User::all();
    $stat = new \Pond\StatusContainer($users);
    $stat->message("Here are all the users");
    return $res->withJson($stat);
});
 */

$app->get('/users/{user_id}', function($req, $res, $args) {
    $users = \Pond\User::find(2);
    $stat = new \Pond\StatusContainer($users);
    $stat->message("Here is user_id 1");
    return $res->withJson($stat);
});

$app->post('/users', function($req, $res, $args) {
    $users = \Pond\User::create(array('user_id' => 2, 'username' =>'kBeth', 'name' => "Kimberly Beth", 'type' => 'STUDENT', 'password' => '21346', 'salt' => 'peanut', 'created_at' => '2008-11-11 13:23:44', 'updated_at' => '2008-11-11 11:12:01' ));
    $stat = new \Pond\StatusContainer($users);
    $stat->message("Here is a newly created user");
    return $res->withJson($stat);
});
