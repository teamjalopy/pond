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

    function __invoke(Request $req, Response $res): Response {
        // Accepts all endpoints of the form
        //
        //  api/lessons[/{lesson_id}]
        //  api/users/{user_id}/lessons
        //
        // where the part in square brackets is
        // optional.

        if( null !== $req->getAttribute('lesson_id') ) {
            // api/lessons/{lesson_id}
            return $this->lessonHandler($req,$res);
        } else {
            // api/lessons
            return $this->lessonCollectionHandler($req,$res);
        }
    }

    function lessonHandler(Request $req, Response $res): Response {
        switch ($req->getMethod()) {
        case 'GET':
            return $this->getLessonHandler($req,$res);
        case 'PUT':
            return $this->putLessonHandler($req,$res);
        case 'DELETE':
            return $this->deleteLessonHandler($req,$res);
        default:
            return $res->withStatus(405); // Method Not Allowed
        }
    }

    function lessonCollectionHandler(Request $req, Response $res): Response {
        switch ($req->getMethod()) {
        case 'GET':
            return $this->getLessonCollectionHandler($req,$res);
        case 'POST':
            return $this->postLessonCollectionHandler($req,$res);
        default:
            return $res->withStatus(405); // Method Not Allowed
        }
    }

    function getUserLessonsHandler(Request $req, Response $res): Response {
        $this->logger->info("GET /api/users/{user_id}/lessons Handler");

        try {
            $uid = UserController::getUID($req,$this->auth);
        } catch(RuntimeException $e) {
            // User ID was 'me' but not authenticated
            return $res->withStatus(401); // Unauthorized
        }

        // If you said 'me' as the user id, show unpublished lessons too.
        if($req->getAttribute('user_id') == 'me') {
            $lessons = Lesson::where("creator_id",$uid)->get();
        } else {
            $lessons = Lesson::where("creator_id",$uid)::where("published",true)->get();
        }

        $lessonObj = $lessons->toArray();

        $stat = new StatusContainer($lessonObj);
        $stat->success();
        $stat->message("Here are the lessons");

        return $res->withJson($stat);
    }

    function getEnrolledLessonsHandler(Request $req, Response $res): Response {
        $this->logger->info("GET /api/users/{user_id}/enrolled Handler");

        try {
            $uid = UserController::getUID($req,$this->auth);
        } catch(RuntimeException $e) {
            // User ID was 'me' but not authenticated
            return $res->withStatus(401); // Unauthorized
        }

        $enrolled = User::find($uid)->enrolledLessons()->get();

        $stat = new StatusContainer( $enrolled->toArray() );
        $stat->success();
        $stat->message("Here are this user's enrolled lessons");

        return $res->withJson($stat);
    }

    function postLessonStudentsHandler(Request $req, Response $res): Response {
        $this->logger->info("GET /api/lessons/{lesson_id}/students Handler");

        // The provided data is an object with key `emails`, where the value is an
        // array of email addresses (one or more).

        $form = $req->getParsedBody();

        // Get the currently authenticated user, return failure state (401)
        // if unauth.
        try {
            $creator_id = $this->auth->getAuthorizedUserID($req);
        } catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }

        // Get the corresponding creator user model or fail
        try {
            $creator = \Pond\User::findOrFail($creator_id);
        } catch(ModelNotFoundException $e) {
            $this->logger->info("postLessonStudentsHandler: user with ID '$creator_id' not found.");
            return $res->withStatus(400); // Bad Request
        }

        $stat = new StatusContainer( $enrolled->toArray() );
        $stat->success();
        $stat->message("Dummy response from enrollment handler");

        return $res->withJson($stat);
    }

    function getLessonHandler(Request $req, Response $res): Response {

        $this->logger->info("GET /api/lessons/{lesson_id} Handler");

        // Retrieve the lesson by ID #
        try {
            $lesson = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e) {
            return self::LessonNotFoundErrorStatus($res);
        }

        // If the lesson is not published, it must be owned by the requester,
        // otherwise, behave as if it does not exist (like GitHub with private repos)
        try {
            $authUID = $this->auth->getAuthorizedUserID($req);
        } catch(Exception $e) {
            $authUID = -1;
        }

        $uidMismatch = ($lesson->creator_id != $authUID);

        if(!$lesson->published && $uidMismatch) {
            $this->logger->info("getLessonHandler: The lesson is not published, and the creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");

            return self::lessonNotFoundErrorStatus($res);
        }

        $stat = new StatusContainer($lesson);
        $stat->success();
        $stat->message("Here is the requested lesson");
        return $res->withJson($stat);
    } // getLessonHandler


    function putLessonHandler(Request $req, Response $res): Response {
        $this->logger->info("PUT /api/lessons/{lesson_id} Handler");

        try {
            $lesson = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e) {
            return self::LessonNotFoundErrorStatus($req);
        }

        try {
            $authUID = $this->auth->getAuthorizedUserID($req);
        } catch(Exception $e) {
            $authUID = -1;
        }

        $uidMismatch = ($lesson->creator_id != $authUID);

        if($uidMismatch) {
            $this->logger->info("putLessonHandler: This lesson's creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");

            return $res->withStatus(401); // Unauthorized
        }

        $form = $req->getParsedBody();

        if( isset($form['name']) ) {
            $lesson->name = $form['name'];
        }

        if( isset($form['published']) ) {
            $lesson->published = $form['published'];
        }

        $lesson->save();

        return self::lessonUpdatedStatus($res, $lesson);
    } // putLessonHandler


    function deleteLessonHandler(Request $req, Response $res): Response {

        $this->logger->info("DELETE /api/lessons/{lesson_id} Handler");

        try {
            $lesson = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e){
            return self::LessonNotFoundErrorStatus($res);
        }

        if(!$this->auth->isRequestAuthorized($req,$lesson->creator_id)) {
            return $res->withStatus(401);
        }

        $lesson->delete();

        $stat = new StatusContainer();
        $stat->success();
        $stat->message("The lesson has been deleted");

        return $res->withJson($stat);
    } // deleteLessonHandler


    function getLessonCollectionHandler(Request $req, Response $res): Response {

        $this->logger->info("GET /api/lessons/ Handler");

        // TODO: also include unpublished, owned lessons
        $lessons = Lesson::where("published",true)->get();
        $lessonObj = $lessons->toArray();

        $stat = new StatusContainer($lessonObj);
        $stat->success();
        $stat->message("Here are the lessons");

        return $res->withJson($stat);
    } // getLessonCollectionHandler


    function postLessonCollectionHandler(Request $req, Response $res): Response {

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
        if(!isset($form['name'])) {
            return self::lessonInfoErrorStatus($res);
        }

        // Create a Lesson model
        $lesson = new Lesson();

        $lesson->name = $form['name'];
        $lesson->published = false;
        $lesson->creator_id = $creator_id;

        if(isset($form['published'])) {
            $lesson->published = (bool)$form['published'];
        }

        $lesson->save();

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


    static function lessonUpdatedStatus(Response $res, Lesson $lesson): Response {
        $stat = new StatusContainer($lesson);
        $stat->success();
        $stat->message("The lesson has been updated.");
        return $res->withJson($stat);
    } // lessonUpdated

}
