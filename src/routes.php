<?php
// Routes
use Illuminate\Database\Eloquent\ModelNotFoundException;

// Authentication Endpoint

$app->post('/api/auth', function ($req, $res, $args) {
    $auth = new \Pond\Auth($this);
    return $auth->loginHandler($req, $res);
});

// User Endpoints

$app->get('/api/users/{user_id}', function($req, $res, $args) {
    $users = Pond\User::find( $req->getAttribute('user_id') );
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

// Lesson Endpoints

$app->any('/api/lessons[/{lesson_id}]', '\Pond\LessonController');

// $app->get('/api/lessons/{lesson_id}', function($req, $res, $args) {
//     $lc = new \Pond\LessonController($this);
//     return $lc->getLessonHandler($req,$res);
// });
//
// $app->post('/api/lessons', function($req, $res, $args) {
//     $lc = new \Pond\LessonController($this);
//     return $lc->postLessonHandler($req,$res);
// });
//
// $app->put('/api/lessons/{lesson_id}', function($req, $res, $args) {
//     $lc = new \Pond\LessonController($this);
//     return $lc->putLessonHandler($req,$res);
// });
//
// $app->delete('/api/lessons/{lesson_id}', function($req, $res, $args) {
//     $lc = new \Pond\LessonController($this);
//     return $lc->deleteLessonHandler($req,$res);
// });
//
// $app->get('/api/lessons', function($req, $res, $args) {
//     $lc = new \Pond\LessonController($this);
//     return $lc->getLessonCollectionHandler($req,$res);
// });
