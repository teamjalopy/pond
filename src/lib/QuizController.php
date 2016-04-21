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

Class QuizController {
    private $container;
    private $logger;
    private $auth;

    public function __construct(Container $c){
        $this->container = $c;
        $this->logger = $this->container->get('logger');
        $this->auth = new Auth($this->container);
    }

    function __invoke(Request $req, Response $res): Response{

        //Handles all of the incomding endpoints
        //
        // /api/lesson/{lesson_id}/quizzes/[{module_id}]
        // /api/lesson/{lesson_id}/quizzes/questions/[{question_id}]
        //
        // with the items in the square brackets being optional

        if(null !== $req->getAttribute('questions')){
            return $this->questionCollectionHandler($req, $res);
        }

    }

    function questionCollectionHandler(Request $req, Response $res): Response{
        if(null !== $req->getAttribute('question_id')){
            return $this->individualQuestionHandler($req, $res);
        }
        else {
            switch ($req->getMethod()) {
            case 'POST':
                return $this->postQuestionHandler($req,$res);
            case 'GET':
                return $this->getQuestionsHandler($req,$res);
            default:
                return $res->withStatus(405);
            }
        }
    }
    function getQuestionsHandler(Request $req, Response $res): Response{
        //getting the questions
        $this->logger->info("GET /api/lessons/{lesson_id}/quizzes/{module_id}/questions");
        // make sure lesson exists
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuestionsHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $authUID = $this->auth->getAuthorizedUserID($req);
        }
        catch(Exception $e) {
            $authUID = -1;
        }
        $uidMismatch = ($lesson->creator_id != $authUID);

        if($uidMismatch) {
            $this->logger->info("getQuestionsHandler: This lesson's creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");
            return $res->withStatus(401); // Unauthorized
        }
        //make sure quiz exists
        try {
            $module = Module::findOrFail($req->getAttribute("module_id"));
            $quiz_id = $module->content()['id'];
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuestionshandler: could not find module");
            return $res->withStatus(404); // Not Found
        }
        // find quiz and get its questions
        try {
            $quiz = Quiz::findOrFail($quiz_id);
            $questions = $quiz->questions()->get();
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuestionsHandler: could not find quiz");
            return $res->withStatus(404); // Not Found
        }

        // return questions
        $stat = new StatusContainer($questions);
        $stat->success();
        $stat->message('Here are the questions.');
        $res = $res->withStatus(200);

        return $res->withJson($stat);
    }

    function postQuestionHandler(Request $req, Response $res): Response{
        //Creates an empty post and returns the quiz id
        $this->logger->info("POST /api/lessons/{lesson_id}/quizzes/{module_id}/questions");
        $form = $req->getParsedBody();

        if (!isset($form['text'])){
            $this->logger->info("postQuestionHandler: Please fill out the text field.");
            return $res->withStatus(400); // Bad Request
        }
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("postQuestionHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $authUID = $this->auth->getAuthorizedUserID($req);
        }
        catch(Exception $e) {
            $authUID = -1;
        }
        $uidMismatch = ($lesson->creator_id != $authUID);

        if($uidMismatch) {
            $this->logger->info("postQuestionHandler: This lesson's creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");
            return $res->withStatus(401); // Unauthorized
        }
        //make sure module exists
        try {
            $module = Module::findOrFail($req->getAttribute("module_id"));
            $quiz_id = $module->content()['id'];
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("postQuestionhandler: could not find module");
            return $res->withStatus(404); // Not Found
        }
        // find quiz
        try {
            $quiz = Quiz::findOrFail($quiz_id);
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("postQuestionHandler: could not find quiz");
            return $res->withStatus(404); // Not Found
        }
        // create new question
        $question = new Question;
        $question->text = $form['text'];
        $question->quiz_id = $quiz->id;
        $question->save();

        $stat = new StatusContainer($question);
        $stat->success();
        $stat->message('Question successfully created.');
        $res = $res->withStatus(200);

        return $res->withJson($stat);
    }


    function individualQuestionHandler(Request $req, Response $res): Response{
        $this->logger->info("individualQuestionHandler");
        switch ($req->getMethod()) {
        case 'GET':
            return $this->getQuestionHandler($req,$res);
        case 'PUT':
            return $this->putQuestionHandler($req,$res);
        case 'DELETE':
            return $this->deleteQuestionHandler($req, $res);
        default:
            return $res->withStatus(405); // Method Not Allowed

        }
    }

    function getQuestionHandler(Request $req, Response $res): Response{
        //retrieve an individual question
        $this->logger->info("GET /api/lessons/{lesson_id}/quizzes/{module_id}/questions/{question_id}");
        // make sure lesson exists
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuestionHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $authUID = $this->auth->getAuthorizedUserID($req);
        }
        catch(Exception $e) {
            $authUID = -1;
        }
        $uidMismatch = ($lesson->creator_id != $authUID);

        if($uidMismatch) {
            $this->logger->info("getQuestionHandler: This lesson's creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");
            return $res->withStatus(401); // Unauthorized
        }
        //make sure module exists
        try {
            $module = Module::findOrFail($req->getAttribute("module_id"));
            $quiz_id = $module->content()['id']; // retrieve the quiz_id
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuestionhandler: could not find module");
            return $res->withStatus(404); // Not Found
        }
        // find quiz
        try {
            $quiz = Quiz::findOrFail($quiz_id);
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuestionHandler: could not find quiz");
            return $res->withStatus(404); // Not Found
        }
        // find question
        try {
            $question = Question::findOrFail($req->getAttribute("question_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuestionHandler: could not find question");
            return $res->withStatus(404); // Not Found
        }
        // return the question
        $stat = new StatusContainer($question);
        $stat->success();
        $stat->message('Here is the requested question.');
        $res = $res->withStatus(200);

        return $res->withJson($stat);

    }

    function putQuestionHandler(Request $req, Response $res): Response{
        //updating a question
        $this->logger->info("PUT /api/lessons/{lesson_id}/quizzes/{module_id}/questions/{question_id}");
        $form = $req->getParsedBody();
        // make sure text is set
        if (!isset($form['text'])){
            $this->logger->info("putQuestionHandler: Please fill out the text field.");
            return $res->withStatus(400); // Bad Request
        }
        // make sure lesson exists
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("putQuestionHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $authUID = $this->auth->getAuthorizedUserID($req);
        }
        catch(Exception $e) {
            $authUID = -1;
        }
        $uidMismatch = ($lesson->creator_id != $authUID);

        if($uidMismatch) {
            $this->logger->info("putQuestionHandler: This lesson's creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");
            return $res->withStatus(401); // Unauthorized
        }
        //make sure module exists
        try {
            $module = Module::findOrFail($req->getAttribute("module_id"));
            $quiz_id = $module->content()['id']; // retrieve quiz_id
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("putQuestionhandler: could not find module");
            return $res->withStatus(404); // Not Found
        }
        // find quiz
        try {
            $quiz = Quiz::findOrFail($quiz_id);
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("putQuestionHandler: could not find quiz");
            return $res->withStatus(404); // Not Found
        }
        // find question
        try {
            $question = Question::findOrFail($req->getAttribute("question_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("putQuestionHandler: could not find question");
            return $res->withStatus(404); // Not Found
        }
        // insert question
        $question->text = $form['text'];
        $question->save();

        $stat = new StatusContainer($question);
        $stat->success();
        $stat->message('Question successfully updated.');
        $res = $res->withStatus(200);

        return $res->withJson($stat);
    }

    function deleteQuestionHandler(Request $req, Response $res): Response{
        //delete a question
        $this->logger->info("DELETE /api/lessons/{lesson_id}/quizzes/{module_id}/questions/{question_id}");
        // make sure lesson exists
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("deleteQuestionHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $authUID = $this->auth->getAuthorizedUserID($req);
        }
        catch(Exception $e) {
            $authUID = -1;
        }
        $uidMismatch = ($lesson->creator_id != $authUID);

        if($uidMismatch) {
            $this->logger->info("deleteQuestionHandler: This lesson's creator ID (#"
            .$lesson->creator_id.") does not match the current user (#". $authUID .")");
            return $res->withStatus(401); // Unauthorized
        }
        //make sure quiz exists
        try {
            $module = Module::findOrFail($req->getAttribute("module_id"));
            $quiz_id = $module->content()['id'];
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("deleteQuestionhandler: could not find module");
            return $res->withStatus(404); // Not Found
        }
        // find quiz
        try {
            $quiz = Quiz::findOrFail($quiz_id);
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("deleteQuestionHandler: could not find quiz");
            return $res->withStatus(404); // Not Found
        }
        // find question
        try {
            $question = Question::findOrFail($req->getAttribute("question_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("deleteQuestionHandler: could not find question");
            return $res->withStatus(404); // Not Found
        }
        $question->delete(); // remove question

        $stat = new StatusContainer();
        $stat->success();
        $stat->message('Question successfully deleted.');
        $res = $res->withStatus(200);

        return $res->withJson($stat);
    }



}
