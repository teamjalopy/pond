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
  $lessonObj = [];
  $lessons = \Pond\Lesson::all();
 
  foreach($lessons as $lesson){
    array_push($lessonObj,[
      "lesson_id" => $lesson->lesson_id,
      "lesson_name" =>$lesson->lesson_name,
      "creator_id" => $lesson->creator_id
    ]);
  }

  return $res->withJson($lessonObj);
});

$app->post('/api/lessons', function($req, $res, $args) {
  $lesson = new \Pond\Lesson();
  $form = $req->getParsedBody();
  //$lesson->lesson_id = @$form['lesson_id'];
  $lesson->creator_id = @$form['creator_id'];
  $lesson->lesson_name = @$form['lesson_name'];
//  $lesson->published = @$form['published'];
  $lesson->save();
	$array = array("lesson"=>$lesson_name,"creator"=>$creator);
	$jsonResponse = $res->withHeader("Content-type","application/json");
	return $jsonResponse->write(json_encode($array));


});
