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

Class QuizController{
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
        // /api/lesson/{lesson_id}/quizzes/[{quiz_id}]
        // /api/lesson/{lesson_id}/quizzes/questions/[{question_id}]
        //
        // with the items in the square brackets being optional

        if(null !== $req->getAttribute('questions')){
            return $this->questionHandler($req, $res);
        }
        else{
            return $this->quizCollectionHandler($req, $res);
        }


    }

    function quizCollectionHandler(Request $req, Response $res): Response{

        if(null !== $req->getAttribute('quiz_id')){
            return $this->individualQuizHandler($req, $res);
        }
        else {
            switch ($req->getMethod()) {
            case 'POST':
                return $this->postQuizHandler($req,$res);
            default:
                return $res->withStatus(405);
            }
        }
    }

    function postQuizHandler(Request $req, Response $res): Response{
        //Creates an empty post and returns the quiz id
        $this->logger->info("POST /api/lessons/{lesson_id}/quizzes");


        //makes sure lesson exists
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        } catch(ModelNotFoundException $e) {
            $this->logger->info("postQuizHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }

        //make sure user is authorized
        try {
            $user_id = $this->auth->getAuthorizedUserID($req);
        } catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }

        $quiz = new \Pond\Quiz();
        $quiz->save();

        $stat = new StatusContainer($quiz);
        $stat->success();
        $stat->message('Quiz successfully created.');
        $res = $res->withStatus(200);

        return $res->withJson($stat);
    }

    function individualQuizHandler(Request $req, Response $res): Response{

        switch ($req->getMethod()) {
        case 'GET':
            return $this->getQuizHandler($req,$res);
        case 'PUT':
            return $this->putQuizHandler($req,$res);
        case 'DELETE':
            return $this->deleteQuizHandler($req, $res);
        default:
            return $res->withStatus(405); // Method Not Allowed

        }
    }

    function getQuizHandler(Request $req, Response $res): Response{

        $this->logger->info("GET /api/lessons/{lesson_id}/quizzes/{quiz_id}");

        //make sure lesson exists
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        } catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }

        //make sure user is authorized to get quiz
        try {
            $user_id = $this->auth->getAuthorizedUserID($req);
        } catch(RuntimeException $e) {
            return $res->withStatus(401); // Unauthorized
        }

        //make sure quiz exists
        try {
            $quiz = Quiz::findorFail($req->getAttribute("quiz_id"));
        } catch(ModelNotFoundException $e) {
            $this->logger->info("getQuizHandler: could not find quiz.");
            return $res->withStatus(404); // Not Found
        }

        $quiz = Quiz::find($req->getAttribute("quiz_id"));

        $stat = new StatusContainer($quiz);
        $stat->success();
        $stat->message("Here is the requested quiz.");

        return $res->withJson($stat);

    }
    //
    // function putQuizHandler(Request $req, Response $res): Response{
    //
    // }
    //
    // function deleteQuizHandler(Request $req, Response $res): Response{
    //
    // }
    //
    //
    // function questionHandler(Request $req, Response $res): Response{
    //
    // }


}
