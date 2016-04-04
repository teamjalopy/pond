<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Validator as v;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \RuntimeException;

use Pond\User;
use Pond\Auth;

use Slim\Container;


class LessonController {

    private $container;
    private $logger;

    function __construct(Container $c){
        $this->container = $c;
        $this->logger = $this->container->get('logger');
    }

    function getLessonHandler(Request $req, Response $res): Response {
        try{
            $lessons = Lesson::findOrFail($args['lesson_id']);
            $stat = new StatusContainer($lessons);
            $stat->success();
            $stat->message("Here is the requested lesson");
            $res = $res->withStatus(200);
            return $res->withJson($stat);
        }
        catch(ModelNotFoundException $e){
            $stat = new StatusContainer($lessons);
            $stat->error("Lesson Not Found");
            $stat->message('Lesson not found.');
            $res = $res->withStatus(404);
            return $res->withJson($stat);
        }
    } // getLessonHandler


    function putLessonHandler(Request $req, Response $res): Response {
        $auth = new Auth($this);
        try{
            $lessons = Lesson::findOrFail($args['lesson_id']);
            $creator_id = $lessons->creator_id;
            $isAuth = $auth->isRequestAuthorized($req,$creator_id);
            if(!$isAuth) {
                $res->withStatus(401); // Unauthorized
            } else {
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

                $stat = new StatusContainer($lessons);
                $stat->success();
                $stat->message("The lesson has been updated.");
                return $res->withJson($stat);
            }

        }
        catch(ModelNotFoundException $e){
            $stat = new StatusContainer($lessons);
            $stat->error("Lesson Not Found");
            $stat->message('Lesson not found.');
            $res = $res->withStatus(404);
            return $res->withJson($stat);
        }
    } // putLessonHandler


    function deleteLessonHandler(Request $req, Response $res): Response {
        $auth = new Auth($this);
        try{
            $lessons = Lesson::findOrFail($args['lesson_id']);
            $creator_id = $lessons->creator_id;
            if(!$auth->isRequestAuthorized($req,$creator_id)) {
                return $res->withStatus(401);
            }

            $lessons->delete();

            $stat = new StatusContainer($lessons);
            $stat->success();
            $stat->message("The lesson has been deleted");
            return $res->withJson($stat);
        }
        catch(ModelNotFoundException $e){
            $stat = new StatusContainer($lessons);
            $stat->error("Lesson Not Found");
            $stat->message('Lesson not found.');
            $res = $res->withStatus(404);
            return $res->withJson($stat);
        }
    } // deleteLessonHandler


    function getLessonCollectionHandler(Request $req, Response $res): Response {
        $lessonObj = [];
        $lessons = Lesson::all();

        foreach($lessons as $lesson){
            array_push($lessonObj, $lesson->toArray());
        }

        $stat = new \Pond\StatusContainer($lessonObj);
        $stat->success();
        $stat->message("Here are the lessons");
        $res = $res->withStatus(200);
        return $res->withJson($stat);
    } // getLessonCollectionHandler


    function postLessonHandler(Request $req, Response $res): Response {
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
            return $res->withJson($stat);
        } else {
            $stat = new \Pond\StatusContainer($lesson);
            $stat->error("LessonInfoError");
            $stat->message("Lesson not created. Fill out the fields.");
            $res = $res->withStatus(400);
            return $res->withJson($stat);
        }
    } // postLessonHandler

}
