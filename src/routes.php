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
$app->get('/api/lessons/{lesson_id}/students', '\Pond\LessonController:getLessonStudentsHandler');
$app->post('/api/lessons/{lesson_id}/students', '\Pond\LessonController:postLessonStudentsHandler');
$app->delete('/api/lessons/{lesson_id}/students/{user_id}', '\Pond\LessonController:deleteLessonStudentsHandler');

// Modules Endpoints
$app->get('/api/lessons/{lesson_id}/modules', '\Pond\LessonController:getModuleCollectionHandler');
$app->post('/api/lessons/{lesson_id}/modules', '\Pond\LessonController:postModuleCollectionHandler');

$app->get('/api/lessons/{lesson_id}/modules/{module_id}', '\Pond\LessonController:getModuleHandler');
$app->delete('/api/lessons/{lesson_id}/modules/{module_id}', '\Pond\LessonController:deleteModuleHandler');

// Quiz Endpoints
$app->post('/api/lessons/{lesson_id}/quizzes/{module_id}/questions', '\Pond\QuizController:questionCollectionHandler');
$app->get('/api/lessons/{lesson_id}/quizzes/{module_id}/questions', '\Pond\QuizController:questionCollectionHandler');
$app->get('/api/lessons/{lesson_id}/quizzes/{quiz_id}/questions/{question_id}', '\Pond\QuizController:getQuestionHandler');
$app->put('/api/lessons/{lesson_id}/quizzes/{quiz_id}/questions/{question_id}', '\Pond\QuizController:postQuestionHandler');
$app->delete('/api/lessons/{lesson_id}/quizzes/{quiz_id}/questions/{question_id}', '\Pond\QuizController:deleteQuestionHandler');
