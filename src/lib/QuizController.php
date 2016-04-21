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
                return $this->getQuestionHandler($req,$res);
            default:
                return $res->withStatus(405);
            }
        }
    }
    function getQuestionHandler(Request $req, Response $res): Response{
        //Creates an empty post and returns the quiz id
        $this->logger->info("GET /api/lessons/{lesson_id}/quizzes/{module_id}/questions");

        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $user_id = $this->auth->getAuthorizedUserID($req);
        }
        catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }
        //make sure quiz exists
        try {
            $quiz = Quiz::findorFail($req->getAttribute("module_id"));
            $questions = $quiz->questions->get();
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find quiz.");
            return $res->withStatus(404); // Not Found
        }


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

        if (!isset($form['text']){
            $this->logger->info("postLessonStudentsHandler: Please fill out the text field.");
            return $res->withStatus(400); // Bad Request
        }
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $user_id = $this->auth->getAuthorizedUserID($req);
        }
        catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }
        //make sure quiz exists
        try {
            $quiz = Quiz::findorFail($req->getAttribute("module_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find quiz.");
            return $res->withStatus(404); // Not Found
        }
        $question = new Question;
        $question->text = $form['text'];
        $question->save();



        $stat = new StatusContainer();
        $stat->success();
        $stat->message('Question successfully created.');
        $res = $res->withStatus(200);

        return $res->withJson($stat);
    }


    function individualQuestionHandler(Request $req, Response $res): Response{

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

        $this->logger->info("GET /api/lessons/{lesson_id}/quizzes/{module_id}/questions/{question_id}");

        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $user_id = $this->auth->getAuthorizedUserID($req);
        }
        catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }
        //make sure quiz exists
        try {
            $quiz = Quiz::findorFail($req->getAttribute("module_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find quiz.");
            return $res->withStatus(404); // Not Found
        }
        try {
            $question = Quiz::findorFail($req->getAttribute("question_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find question.");
            return $res->withStatus(404); // Not Found
        }


        $stat = new StatusContainer($question);
        $stat->success();
        $stat->message("Here is the requested question.");

        return $res->withJson($stat);

    }

    function putQuestionHandler(Request $req, Response $res): Response{
        $this->logger->info("PUT /api/lessons/{lesson_id}/quizzes/{module_id}/questions/{question_id}");

        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $user_id = $this->auth->getAuthorizedUserID($req);
        }
        catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }
        //make sure quiz exists
        try {
            $quiz = Quiz::findorFail($req->getAttribute("module_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find quiz.");
            return $res->withStatus(404); // Not Found
        }
        try {
            $question = Quiz::findorFail($req->getAttribute("question_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find question.");
            return $res->withStatus(404); // Not Found
        }

        $question->save();

        $stat = new StatusContainer();
        $stat->success();
        $stat->message("Question has been updated");

        return $res->withJson($stat);
    }

    function deleteQuestionHandler(Request $req, Response $res): Response{
        $this->logger->info("DELETE /api/lessons/{lesson_id}/quizzes/{module_id}/questions/{question_id}");
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }
        //make sure user is authorized to get quiz
        try {
            $user_id = $this->auth->getAuthorizedUserID($req);
        }
        catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }
        //make sure quiz exists
        try {
            $quiz = Quiz::findorFail($req->getAttribute("module_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find quiz.");
            return $res->withStatus(404); // Not Found
        }
        try {
            $question = Quiz::findorFail($req->getAttribute("question_id"));
        }
        catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find question.");
            return $res->withStatus(404); // Not Found
        }

        $question->delete();


        $stat = new StatusContainer();
        $stat->success();
        $stat->message("The question has been deleted");

        return $res->withJson($stat);
    }



}
