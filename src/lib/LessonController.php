<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Validator as v;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \RuntimeException;
use \Exception;

use Slim\Container;


class LessonController {

    private $container;
    private $logger;
    private $auth;

    function __construct(Container $c){
        $this->container = $c;
        $this->logger = $this->container->get('logger');
        $this->auth = new Auth($this->container);
    }


    function getLessonHandler(Request $req, Response $res): Response {
        try{
            $lessons = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e){
            return self::lessonNotFoundError($res);
        }


        $stat = new StatusContainer($lesson);
        $stat->success();
        $stat->message("Here is the requested lesson");
        return $res->withJson($stat);
    } // getLessonHandler


    function putLessonHandler(Request $req, Response $res): Response {
        try {
            $lessons = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e) {
            return self::lessonNotFoundError($req);
        }

        if(!$this->auth->isRequestAuthorized($req,$creator_id)) {
            $res->withStatus(401); // Unauthorized
        }

        $form = $req->getParsedBody();

        if( isset($form['lesson_name']) ) {
            $lessons->lesson_name = $form['lesson_name'];
        }

        if( isset($form['published']) ) {
            $lessons->published = $form['published'];
        }

        $lessons->save();

        return self::lessonUpdatedStatus($res);
    } // putLessonHandler


    function deleteLessonHandler(Request $req, Response $res): Response {
        if(!$this->auth->isRequestAuthorized($req,$lesson->creator_id)) {
            return $res->withStatus(401);
        }

        try {
            $lesson = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e){
            return self::lessonNotFoundError($res);
        }

        $lesson->delete();

        $stat = new StatusContainer();
        $stat->success();
        $stat->message("The lesson has been deleted");

        return $res->withJson($stat);
    } // deleteLessonHandler


    function getLessonCollectionHandler(Request $req, Response $res): Response {
        $lessons = Lesson::where("published",true)->get();
        $lessonObj = $lessons->toArray();

        $stat = new StatusContainer($lessonObj);
        $stat->success();
        $stat->message("Here are the lessons");

        return $res->withJson($stat);
    } // getLessonCollectionHandler


    function postLessonHandler(Request $req, Response $res): Response {
        $lesson = new Lesson();
        $form = $req->getParsedBody();
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
            return self::lessonInfoErrorStatus($res);
        }
    } // postLessonHandler


    static function lessonNotFoundErrorStatus(Response $res): Response {
        $stat = new StatusContainer($lessons);
        $stat->error("LessonNotFoundError");
        $stat->message('Lesson not found.');

        $res = $res->withStatus(404);
        return $res->withJson($stat);
    } // lessonNotFoundError


    static function lessonInfoErrorStatus(Response $res): Response {
        $stat = new StatusContainer($lesson);
        $stat->error("LessonInfoError");
        $stat->message("Please provide all required fields.");

        $res = $res->withStatus(400);
        return $res->withJson($stat);
    } // lessonInfoError


    static function lessonUpdatedStatus(Response $res): Response {
        $stat = new StatusContainer($lessons);
        $stat->success();
        $stat->message("The lesson has been updated.");
        return $res->withJson($stat);
    } // lessonUpdated

}
