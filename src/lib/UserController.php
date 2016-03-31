<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Validator as v;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \RuntimeException;

use Pond\User;
use Pond\Auth;

use Slim\Container;


class UserController
{
  private $container;
  private $logger;

  function __construct(Container $c){
    $this->container = $c;
    $this->logger = $this->container->get('logger');
  }

  public function registrationHandler(Request $req, Response $res): Response {

    $stat = new \Pond\StatusContainer();
    $user = new \Pond\User();
    $form = $req->getParsedBody();

    $email = $form['email'];
    $password = $form['password'];
    $crypto = new Crypto($password);
    $captcha = $form['captcha'];

    if($message = self::validation($email, $password, $captcha)) {
        return self::badRegistration($res, $message);
    }

    $stat->success();
    $stat->message('User sucessfully created.');
    $res = $res->withStatus(200);

    $user->user_id = $form['user_id'];
    $user->email = $email;
    $user->name = $form['name'];
    $user->type = $form['type'];
    $user->password = $crypto->getHash();
    $user->salt = $crypto->getSalt();
    $user->save();

    return $res->withJson($stat);
  }


  private function validation($email, $password, $captcha, callable $errorCallback) : string {
      $text = "";
       // Email format validation
       try {
           \Pond\Validate::get('email')->check($email);
       } catch(ValidationException $e){
            $this->logger->info('User email validation fail');
            $text = "Email is invalid";
            return $text;
       }


       // Password format validation
       try {
           \Pond\Validate::get('password')->check( $password );
       } catch(ValidationException $e) {
           $this->logger->info('Password validation fail');
           $text = "Please make sure you password is at least 8 characters long.";
           call_user_func($errorCallback, $text);
           return false;
       }

       // Try to get the requested User or throw an exception
       try{
           $reqUser = User::where('email', $email)->firstOrFail();
           $this->logger->info("Email already in use");
           $text = "Email is alread in use";
           call_user_func($errorCallback, $text);
           return false;
       } catch(ModelNotFoundException $e){
           // continue
       }

       //based on example from codexworld.com
       if( !isset($captcha) || empty($captcha) ){
           $text = "Captcha verification failed";
           call_user_func($errorCallback, $text);
           return false;
       }

       $secret = '6LfBLBwTAAAAAMAdOpVYySINYpzC0VaaarUsfggD';
       $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$captcha);
       $responseData = json_decode($verifyResponse);

       if($responseData->success){
           return true;
        } else {
            $text = "Captcha verification failed";
            call_user_func($errorCallback, $text);
            return false;
        }
   }

   static function badRegistration(Response $res, string $message): Response{

       $stat = new \Pond\StatusContainer();
       $stat->error('BadRegistration');
       $stat->message($message);
       $res = $res->withStatus(400); // Unauthorized
       return $res->withJson($stat);

   }


}
