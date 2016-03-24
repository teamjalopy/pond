<?php
// Routes

$app->get('/', function ($request, $response, $args) {

    $this->logger->info("Pond '/' route");

    return $this->renderer->render($response, 'index.phtml', $args);
});


$app->post('/api/auth', function ($req, $res, $args) {
    $this->logger->info("[Pond] POST /api/auth");
    var_dump($args);
});
