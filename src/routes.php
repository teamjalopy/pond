<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

// Routes
// Authentication Endpoint

$app->post('/api/auth', function ($req, $res, $args) {
    $auth = new \Pond\Auth($this);
    return $auth->loginHandler($req, $res);
});

// User Endpoints
$app->any('/api/users[/{user_id}]', '\Pond\UserController');

// Lesson Endpoints
$app->get('/api/users/{user_id}/lessons', '\Pond\LessonController:getUserLessonsHandler');
$app->any('/api/lessons[/{lesson_id}]', '\Pond\LessonController');

// Enrollment Endpoints
$app->get('/api/users/{user_id}/enrolled', '\Pond\LessonController:getEnrolledLessonsHandler');

//Module quiz Endpoints
$app->get('/api/lessons/{lesson_id}/quizzes', '\Pond\QuizController');
