<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Validator as v;
use \Illuminate\Database\Capsule\Manager as Capsule;

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

    // BEGIN GENERAL HANDLERS

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

    // END GENERAL HANDLERS

    // BEGIN MODULE HANDLERS

    function postModuleCollectionHandler(Request $req, Response $res): Response {
        $this->logger->info("POST /api/lessons/{lesson_id}/modules");

        $form = $req->getParsedBody();

        if(!isset($form['type'])) {
            $this->logger->info("postModulesHandler: expected `type` field.");
            return $res->withStatus(400); // Bad Request
        }

        if(!isset($form['name'])) {
            $this->logger->info("postModulesHandler: expected `name` field.");
            return $res->withStatus(400); // Bad Request
        }

        // Makes sure Lesson exists

        try {
            $lesson = Lesson::findOrFail( $req->getAttribute("lesson_id") );
        } catch(ModelNotFoundException $e) {
            $this->logger->info("postModulesHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }

        // Check User authorization for Lesson

        try {
            $authUID = $this->auth->getAuthorizedUserID($req);
        } catch(Exception $e) {
            $authUID = -1;
        }

        $uidMismatch = ($lesson->creator_id != $authUID);

        if($uidMismatch) {
            $this->logger->info("postModulesHandler: This lesson's creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");

            return $res->withStatus(401); // Unauthorized
        }

        switch($form['type']) {
            case 'quiz':
                return $this->createQuiz($lesson, $form['name'], $res);
                break;
            default:
                return $res->withStatus(400); // Bad Request
                break;
        }
    }

    private function createQuiz(Lesson $lesson, string $name, Response $res): Response {
        $this->logger->info("Quiz creation subhandler");

        $module = new \Pond\Module();

        try {
            Capsule::transaction(function() use($lesson,$name,$module)
            {
                $quiz = new \Pond\Quiz();
                $quiz->name = $name;
                $quiz->save();

                $module->lesson()->associate($lesson);
                $module->content_type = 'quiz';
                $module->content_id = $quiz->id;
                $module->save();
            });
        } catch(Exception $e) {
            $stat = new StatusContainer();
            $stat->error('FailedTransactionError');
            $stat->message('Something went wrong during the module creation transaction. It was rolled back.');
        }

        $stat = new StatusContainer($module);
        $stat->success();
        $stat->message('Quiz successfully created.');

        $res = $res->withStatus(201);
        return $res->withJson($stat);
    }

    function getModuleCollectionHandler(Request $req, Response $res): Response {
        $this->logger->info("GET /api/lessons/{lesson_id}/modules Handler");

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
            $this->logger->info("getModulesHandler: The lesson is not published, and the creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");

            return self::lessonNotFoundErrorStatus($res);
        }

        $stat = new StatusContainer($lesson->modules()->get());
        $stat->success();
        $stat->message("Here are the modules for the lesson");
        return $res->withJson($stat);
    }

    function getModuleHandler(Request $req, Response $res): Response {
        $this->logger->info("GET /api/lessons/{lesson_id}/modules/{module_id} Handler");

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
            $this->logger->info("getModulesHandler: The lesson is not published, and the creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");

            return self::lessonNotFoundErrorStatus($res);
        }

        try {
            $module = $lesson->modules()->findOrFail( $req->getAttribute('module_id') );
        } catch(ModelNotFoundException $e) {
            return $res->withStatus(404);
        }

        $stat = new StatusContainer($module);
        $stat->success();
        $stat->message("Here is the requested module");
        return $res->withJson($stat);
    }

    function deleteModuleHandler(Request $req, Response $res): Response {
        $this->logger->info("DELETE /api/lessons/{lesson_id}/modules/{module_id} Handler");

        // Get the lesson
        try {
            $lesson = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e){
            return self::LessonNotFoundErrorStatus($res);
        }

        // Get the module
        try {
            $module = $lesson->modules()->findOrFail( $req->getAttribute('module_id') );
        } catch(ModelNotFoundException $e) {
            return $res->withStatus(404);
        }

        // Require authorship [on lesson] to delete [the module]
        if(!$this->auth->isRequestAuthorized($req,$lesson->creator_id)) {
            return $res->withStatus(401); // Unauthorized
        }

        // Transactionally delete the content entry first, then the module.
        try {
            $this->deleteModule($module);
        } catch(Exception $e) {
            $stat = new StatusContainer();
            $stat->error('FailedTransactionError');
            $stat->message('Something went wrong during the module deletion transaction. It was rolled back.');
        }

        $stat = new StatusContainer();
        $stat->success();
        $stat->message("The module and corresponding content has been deleted");

        return $res->withJson($stat);
    }

    private function deleteModule(Module $module) {
        Capsule::transaction(function() use($module)
        {
            // delete the associated content first
            $content = $module->content()->delete();
            // then delete the module record itself.
            $module->delete();
        });
    }

    // END MODULE HANDLERS

    // BEGIN ENROLLMENT HANDLERS

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

    function getLessonStudentsHandler(Request $req, Response $res): Response {
        $this->logger->info("GET /api/lessons/{lesson_id}/students Handler");

        // Get the currently authenticated user, return failure state (401)
        // if unauth.
        try {
            $creator_id = $this->auth->getAuthorizedUserID($req);
        } catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }

        // Get the lesson and its students
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
            $students = $lesson->students()->get();
        } catch(ModelNotFoundException $e) {
            $this->logger->info("getLessonStudentsHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }

        $stat = new StatusContainer($students);
        $stat->success();
        $stat->message("Here are the students for the given lesson.");

        return $res->withJson($stat);
    }

    function postLessonStudentsHandler(Request $req, Response $res): Response {
        $this->logger->info("POST /api/lessons/{lesson_id}/students Handler");

        // The provided data is an object with key `emails`, where the value is an
        // array of email addresses (one or more).

        $form = $req->getParsedBody();

        if(!isset($form['emails']) || count($form['emails']) == 0) {
            $this->logger->info("postLessonStudentsHandler: expected one or more emails in `emails` field.");
            return $res->withStatus(400); // Bad Request
        }

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

        // Get the emails array and all their corresponding users
        $students = User::whereIn('email', $form['emails'])->get();

        if($students->count() < count($form['emails'])) {
            $this->logger->info("postLessonStudentsHandler: one or more students could not be found.");
            return $res->withStatus(404); // Not Found
        }

        // Get the lesson and add all the students
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        } catch(ModelNotFoundException $e) {
            $this->logger->info("postLessonStudentsHandler: could not find lesson with ID " . $req->getAttribute("lesson_id") . ".");
            return $res->withStatus(404); // Not Found
        }

        $lesson->students()->saveMany($students);

        $stat = new StatusContainer();
        $stat->success();
        $stat->message("Successfully enrolled student(s)");

        return $res->withJson($stat);
    }

    function deleteLessonStudentsHandler(Request $req, Response $res): Response {
        $this->logger->info("DELETE /api/lessons/{lesson_id}/students/{user_id} Handler");

        // Get the lesson or fail
        try {
            $lesson = Lesson::findOrFail( $req->getAttribute('lesson_id') );
        } catch(ModelNotFoundException $e){
            return self::LessonNotFoundErrorStatus($res);
        }

        // Authorization (general)
        if(!$this->auth->isRequestAuthorized($req)) {
            return $res->withStatus(401);
        }

        // Authorization (specific: is user the student or the authoring teacher?)
        try {
            $uid = UserController::getUID($req,$this->auth);
            $student = User::findOrFail($uid);
            $creator = $lesson->creator();
        } catch(ModelNotFoundException $e){
            return $res->withStatus(404);
        }

        $lesson->students()->detach($student);

        $stat = new StatusContainer();
        $stat->success();
        $stat->message("The enrollment record has been detached");

        return $res->withJson($stat);
    }

    // END ENROLLMENT HANDLERS

    // BEGIN LESSON HANDLERS

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

    // END LESSON HANDLERS

    // BEGIN STATUS RESPONSES

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

    // END STATUS RESPONSES
}
