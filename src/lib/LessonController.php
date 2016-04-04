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

        $this->logger->info("GET /api/lessons/{lesson_id} Handler");

        try {
            $lessons = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e) {
            return self::lessonNotFoundError($res);
        }

        $stat = new StatusContainer($lesson);
        $stat->success();
        $stat->message("Here is the requested lesson");
        return $res->withJson($stat);
    } // getLessonHandler


    function putLessonHandler(Request $req, Response $res): Response {
        $this->logger->info("PUT /api/lessons/{lesson_id} Handler");

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

        $this->logger->info("DELETE /api/lessons/{lesson_id} Handler");

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

        $this->logger->info("GET /api/lessons/ Handler");

        $lessons = Lesson::where("published",true)->get();
        $lessonObj = $lessons->toArray();

        $stat = new StatusContainer($lessonObj);
        $stat->success();
        $stat->message("Here are the lessons");

        return $res->withJson($stat);
    } // getLessonCollectionHandler


    function postLessonHandler(Request $req, Response $res): Response {

        $this->logger->info("POST /api/lessons/ Handler");

        $form = $req->getParsedBody();

        // Get the currently authenticated user, return failure state (401)
        // if unauth.
        try {
            $creator_id = $this->auth->getAuthorizedUserID($req);
        } catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }

        // Get the corresponding user model or fail
        try {
            $creator = \Pond\User::findOrFail($creator_id);
        } catch(ModelNotFoundException $e) {
            $this->logger->info("postLessonHandler: user with ID '$creator_id' not found.");
            return $res->withStatus(400); // Bad Request
        }

        // Check that the user is a teacher
        if($creator->type != "TEACHER") {
            $this->logger->info("postLessonHandler: bad creator type '$creator->type'.");
            return $res->withStatus(400); // Bad Request
        }

        // Require the lesson name at a minimum
        if(!isset($form['lesson_name'])) {
            return self::lessonInfoErrorStatus($res);
        }

        // Create a Lesson model
        $lesson = new Lesson();

        $lesson->lesson_name = $form['lesson_name'];
        $lesson->published = false;

        if(isset($form['published'])) {
            $lesson->published = (bool)$form['published'];
        }

        $creator->lessons()->save($lesson);

        $stat = new \Pond\StatusContainer($lesson);
        $stat->success();
        $stat->message("Lesson created");
        return $res->withJson($stat);
    } // postLessonHandler


    static function lessonNotFoundErrorStatus(Response $res): Response {
        $stat = new StatusContainer();
        $stat->error("LessonNotFoundError");
        $stat->message('Lesson not found.');

        $res = $res->withStatus(404);
        return $res->withJson($stat);
    } // lessonNotFoundError


    static function lessonInfoErrorStatus(Response $res): Response {
        $stat = new StatusContainer();
        $stat->error("LessonInfoError");
        $stat->message("Please provide all required fields.");

        $res = $res->withStatus(400);
        return $res->withJson($stat);
    } // lessonInfoError


    static function lessonUpdatedStatus(Response $res): Response {
        $stat = new StatusContainer();
        $stat->success();
        $stat->message("The lesson has been updated.");
        return $res->withJson($stat);
    } // lessonUpdated

}
