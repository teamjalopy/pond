<?php
// Routes
use Illuminate\Database\Eloquent\ModelNotFoundException;

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


$app->get('/api/users/{user_id}', function($req, $res, $args) {
    $users = Pond\User::find($args['user_id']);
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


$app->get('/api/lessons/{lesson_id}', function($req, $res, $args) {
    try{
        $lessons = Pond\Lesson::findOrFail($args['lesson_id']);
        $stat = new \Pond\StatusContainer($lessons);
        $stat->success();
        $stat->message("Here is the requested lesson");
        $res = $res->withStatus(200);
        return $res->withJson($stat);
    }
    catch(ModelNotFoundException $e){
        $stat = new \Pond\StatusContainer($lessons);
        $stat->error("Lesson Not Found");
        $stat->message('Lesson not found.');
        $res = $res->withStatus(404);
        return $res->withJson($stat);
    }
});

$app->put('/api/lessons/{lesson_id}', function($req, $res, $args) {
    //$auth = new \Pond\Auth($this);
    try{
        $lessons = Pond\Lesson::findOrFail($args['lesson_id']);
        //$creator_id = $lessons->creator_id;
        //$isAuth = $auth->isRequestAuthorized($req,$creator_id);
        //if(!$isAuth) {
        //    $res->withStatus(401); // Unauthorized
        //} else {
        $form = $req->getParsedBody();
        $lesson_name = @$form['lesson_name'];
        $published = @$form['published'];
        if(isset($lesson_name)){
            $lessons->lesson_name = @$form['lesson_name'];
            $lessons->save();
        }
        if(isset($published)){
            if($published == '1' or $published == '0'){
                $lessons->published = @$form['published'];
                $lessons->save();
            }
        }

            $stat = new \Pond\StatusContainer($lessons);
            $stat->success();
            $stat->message("The lesson has been updated.");
            $res = $res->withStatus(200);
            return $res->withJson($stat);
        //}

    }
    catch(ModelNotFoundException $e){
        $stat = new \Pond\StatusContainer($lessons);
        $stat->error("Lesson Not Found");
        $stat->message('Lesson not found.');
        $res = $res->withStatus(404);
        return $res->withJson($stat);
    }
});

$app->delete('/api/lessons/{lesson_id}', function($req, $res, $args) {
    //$auth = new \Pond\Auth($this);
    try{
        $lessons = Pond\Lesson::findOrFail($args['lesson_id']);
        //$creator_id = $lessons->creator_id;
        //$isAuth = $auth->isRequestAuthorized($req,$creator_id);
        //if(!$isAuth) {
        //    $res->withStatus(401); // Unauthorized
        //} else {
            $stat = new \Pond\StatusContainer($lessons);
            $stat->success();
            $lessons->delete();
            $stat->message("The lesson has been deleted");
            return $res->withJson($stat);
        //}

    }
    catch(ModelNotFoundException $e){
        $stat = new \Pond\StatusContainer($lessons);
        $stat->error("Lesson Not Found");
        $stat->message('Lesson not found.');
        $res = $res->withStatus(404);
        return $res->withJson($stat);
    }
});

$app->get('/api/lessons', function($req, $res, $args) {

  $lessonObj = [];
  $lessons = \Pond\Lesson::all();

  foreach($lessons as $lesson){
    array_push($lessonObj, $lesson->toArray());
  }

  $stat = new \Pond\StatusContainer($lessonObj);
  $stat->success();
  $stat->message("Here are the lessons");
  $res = $res->withStatus(200);
  return $res->withJson($stat);
});

$app->post('/api/lessons', function($req, $res, $args) {
  $lesson = new \Pond\Lesson();
  $form = $req->getParsedBody();
  $lesson_name = @$form['lesson_name'];
  $creator_id = @$form['creator_id'];
  $published = @$form['published'];

  $users = \Pond\User::all();
  $userObj = [];

  foreach($users as $user){
      if($user->type == 'TEACHER')
        array_push($userObj,$user->user_id);
  }

  if(isset($creator_id) and isset($lesson_name) and in_array($creator_id,$userObj)){
      $lesson->creator_id = $creator_id;
      $lesson->lesson_name = $lesson_name;
      $lesson->save();
      if(isset($published) and ($published == '1' or $published == '0')){
          $lesson->published = $published;
          $lesson->save();
      }
      $stat = new \Pond\StatusContainer($lesson);
      $stat->success();
      $stat->message("Lesson created");
      $res = $res->withStatus(200);
      return $res->withJson($stat);

  } else{
      $stat = new \Pond\StatusContainer($lesson);
      $stat->error("Lesson not created.");
      $stat->message("Lesson not created. Fill out the fields.");
      $res = $res->withStatus(400);
      return $res->withJson($stat);
  }


});
