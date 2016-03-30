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

$app->get('/api/lessons/{lesson_id}', function($req, $res, $args) {
    
});

$app->put('/api/lessons/{lesson_id}', function($req, $res, $args) {
    
});

$app->delete('/api/lessons/{lesson_id}', function($req, $res, $args) {
    
});

$app->get('/api/lessons', function($req, $res, $args) {
    $db = new \Pond\Lesson($this);
	$array = [];
	$jsonResponse = $response->withHeader("Content-type","application/json");
	
	foreach($db->query('SELECT * FROM lessons') as $row) {
    	array_push($array, [
			"lesson_id" => $row['lesson_id'],
			"lesson_name" => $row['lesson_name'],
			"creator_id" => $row['creator_id'],
			"published" => ($row['published'] == 1)
		]);
    }
	
	return $jsonResponse->write( json_encode($array) );
	
});

$app->post('/api/lessons', function($req, $res, $args) {
    $db = new \Pond\Lesson($this);
    $creator = $req->getAttribute('creator_id'); 
	$lesson = $req->getAttribute('lesson_name');
	$db->query("INSERT INTO lessons (creator_id,lesson_name) VALUES ('$creator','$lesson');");
});