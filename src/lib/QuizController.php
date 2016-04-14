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

    public function __construct(Container $c){
        $this->container = $c;
        $this->logger = $this->container->get('logger');
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
            return $this->quizHandler($req, $res);
        }


    }

    function quizHandler(Request $req, Response $res): Response{

        if(null !== $req->getAttribute('quiz_id')){
            return $this->individualQuizHandler($req, $res);
        }
        else {
            switch ($req->getMethod()) {
            case 'POST':
                return $this->postQuizHandler($req,$res);
            default:
                return $res->withStatus(405); // Method Not Allowed
            }
        }

    }

    function postQuizHandler(Request $req, Response $res): Response{
        
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

    }

    function putQuizHandler(Request $req, Response $res): Response{

    }

    function deleteQuizHandler(Request $req, Response $res): Response{

    }


    function questionHandler(Request $req, Response $res): Response{

    }


}
