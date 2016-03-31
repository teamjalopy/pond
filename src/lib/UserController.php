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

    if($message = self::validation($email, $password)){
        return self::badRegistration($res, $message);
    }

    else{
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
    }

    return $res->withJson($stat);

  }


  private function validation($email, $password) : string{
      $text;
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
           $text = "Password is invalid";
           return $text;
       }

       // Try to get the requested User or throw an exception
       try{
           $reqUser = User::where('email', $email)->firstOrFail();
           $this->logger->info("Email already in use");
           $text = "Email is alread in use";
           return $text;
       } catch(ModelNotFoundException $e){
           return null;
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
