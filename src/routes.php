<?php
// Routes
use Illuminate\Database\Eloquent\ModelNotFoundException;

// Authentication Endpoint

$app->post('/api/auth', function ($req, $res, $args) {
    $auth = new \Pond\Auth($this);
    return $auth->loginHandler($req, $res);
});

// User Endpoints
$app->any('/api/users[/{user_id}]', '\Pond\UserController');

// Lesson Endpoints
$app->any('/api/lessons[/{lesson_id}]', '\Pond\LessonController');

$app->post('/users/{user_id}/reset_password/{reset_token}',function($req,$res,$args)){
    
});
