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
        $auth = new Auth($this->container);
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
            return $this->postQuizHandler($req,$res);
        }
    }

    function postQuizHandler(Request $req, Response $res): Response{
        //Creates an empty post and returns the quiz id
        $this->logger->info("POST /api/postQuizHandler");

        //makes sure user is authenticated
        try {
            $creator_id = $this->auth->getAuthorizedUserID($req);
        } catch(RuntimeException $e) {
            $this->logger->info("postQuizHandler: User was unauthorized");
            return $res->withStatus(401); // Unauthorized
        }

        //makes sure lesson exists
        try {
            $lesson = Lesson::findOrFail($req->getAttribute("lesson_id"));
        } catch(ModelNotFoundException $e) {
            $this->logger->info("postQuizHandler: could not find lesson.");
            return $res->withStatus(404); // Not Found
        }

        $quiz = new \Pond\Quiz();
        $quiz->save();

        $stat = new StatusContainer();
        $stat->success();
        $stat->data($quiz->getQuizId());
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

    // function getQuizHandler(Request $req, Response $res): Response{
    //
    // }
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
