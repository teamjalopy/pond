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

$app->get('/lessons/{lesson_id}', function($req, $res, $args) {
    
});

$app->put('/lessons/{lesson_id}', function($req, $res, $args) {
    
});

$app->delete('/lessons/{lesson_id}', function($req, $res, $args) {
    
});

$app->get('/lessons', function($req, $res, $args) {
    
});

$app->post('/lessons', function($req, $res, $args) {
    $less = new \Pond\Lesson($this);
    return $less->lessonHandler($req, $res);
});