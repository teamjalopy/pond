<?php
// Routes

$app->get('/', function ($request, $response, $args) {

    $this->logger->info("Pond '/' route");

    return $this->renderer->render($response, 'index.phtml', $args);
});


$app->post('/api/auth', function ($req, $res, $args) {
    $auth = new \Pond\Auth($this);
    return $auth->loginHandler($req, $res);
});

// Authorization example
//
// $app->get('/test/isauth/{user_id}', function($req, $res, $args) {
//     $auth = new \Pond\Auth($this);
//     $uid = $req->getAttribute('user_id');
//     $isAuth = $auth->isRequestAuthorized($req,$uid);
//     return $isAuth ? $res->withStatus(200) : $res->withStatus(401);
// });
