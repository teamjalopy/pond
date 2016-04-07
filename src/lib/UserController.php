<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Validator as v;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \RuntimeException;

use Slim\Container;


class UserController
{
    private $container;
    private $logger;

    function __construct(Container $c){
        $this->container = $c;
        $this->logger = $this->container->get('logger');
    }


    function __invoke(Request $req, Response $res): Response {
        // Accepts all endpoints of the form
        //
        //  api/users[/{user_id}]
        //
        // where the part in square brackets is
        // optional.
        if( null !== $req->getAttribute('user_id') ) {
            // api/users/{user_id}
            return $this->userHandler($req,$res);
        } else {
            // api/users
            return $this->userCollectionHandler($req,$res);
        }
    }

    function userHandler(Request $req, Response $res): Response {
        switch ($req->getMethod()) {
        case 'GET':
            return $this->getUserHandler($req,$res);
        default:
            return $res->withStatus(405); // Method Not Allowed
        }
    }

    function userCollectionHandler(Request $req, Response $res): Response {
        switch ($req->getMethod()) {
        case 'POST':
            return $this->postUserCollectionHandler($req,$res);
        default:
            return $res->withStatus(405); // Method Not Allowed
        }
    }

    public function getUserHandler(Request $req, Response $res): Response {
        try {
            $user = User::findOrFail( self::getUID($req, new Auth($this->container)) );
        } catch(ModelNotFoundException $e) { // user not found
            return $res->withStatus(404);
        } catch(RuntimeException $e) { // there is no authorized user
            return $res->withStatus(401);
        }

        $stat = new StatusContainer($user);
        $stat->success();
        $stat->message("Here is requested user");
        return $res->withJson($stat);
    }

    public function postUserCollectionHandler(Request $req, Response $res): Response {

        $this->logger->info("POST /api/users Handler");

        $user = new \Pond\User();
        $form = $req->getParsedBody();

        $email = $form['email'];
        $password = $form['password'];
        $crypto = new Crypto($password);
        $type = $form['type'];
        $captcha = $form['captcha'];

        $errRes = $this->validation($email, $password, $type, $captcha, function($e) use($res) {
            return self::badRegistration($res, $e);
        });

        if(isset($errRes)) {
            return $errRes;
        };

        $user->email = $email;

        //changes type to match ENUM in users table
        if($type == 'teacher'){
            $user->type = 'TEACHER';
        }

        else{
            $user->type = 'STUDENT';
        }

        $this->logger->info($user->type);
        $user->password = $crypto->getHash();
        $user->salt = $crypto->getSalt();
        $user->save();

        $stat = new StatusContainer();
        $stat->success();
        $stat->message('User successfully created.');
        $res = $res->withStatus(200);
        return $res->withJson($stat);
    }


    private function validation($email, $password, $type, $captcha, callable $errorCallback) {
        $text = "";
        // Email format validation
        try {
            \Pond\Validate::get('email')->check($email);
        } catch(ValidationException $e){
            $this->logger->info('User email validation fail');
            $text = "Email is invalid";
            return call_user_func($errorCallback, $text);
        }

        // Password format validation
        try {
            \Pond\Validate::get('password')->check( $password );
        } catch(ValidationException $e) {
            $this->logger->info('Password validation fail');
            $text = "Please make sure you password is at least 8 characters long.";
            return call_user_func($errorCallback, $text);
        }

        if($reqUser = User::where('email', $email)->first()) {
            $this->logger->info("Email already in use");
            $text = "Email is already in use";
            return call_user_func($errorCallback, $text);
        }

        //based on example from codexworld.com
        if( !isset($captcha) || empty($captcha) ){
            $text = "Captcha verification failed";
            return call_user_func($errorCallback, $text);
        }

        $secret = '6LfBLBwTAAAAAMAdOpVYySINYpzC0VaaarUsfggD';
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$captcha);
        $responseData = json_decode($verifyResponse);

        if($responseData->success){
            return null;
        } else {
            $text = "Captcha verification failed";
            return call_user_func($errorCallback, $text);
        } // end of code flow
    }


    static function badRegistration(Response $res, string $message): Response{
        $stat = new \Pond\StatusContainer();
        $stat->error('BadRegistration');
        $stat->message($message);

        $res = $res->withStatus(400); // Bad Request
        return $res->withJson($stat);
    }


    static function getUID(Request $req, Auth $auth): int {
        // Either get the {user_id} attribute, or if it's
        // 'me', get the current user ID.
        $uid = $req->getAttribute('user_id');
        if($uid == 'me') {
            $uid = $auth->getAuthorizedUserID($req);
        }
        return $uid;
    }

}
