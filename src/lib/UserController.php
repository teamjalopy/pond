<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Validator as v;

use Respect\Validation\Exceptions\ValidationException;
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

    $email = @$form['email'];
    $password = @$form['password'];
    $crypto = new Crypto($password);

    if($this->validation($email, $password)){
      $stat->success();
      $stat->message('User sucessfully created.');
      $res = $res->withStatus(200);

      $user->user_id = @$form['user_id'];
      $user->email = $email;
      $user->name = @$form['name'];
      $user->type = @$form['type'];
      $user->password = $crypto->getHash();
      $user->salt = $crypto->getSalt();
      $user->save();
    }

    else{
      $stat->error('Invalid Entry');
      $stat->message('Check your email and password.');
      $res = $res->withStatus(401);
    }

    return $res->withJson($stat);

  }


  private function validation($email, $password) {

       // Email format validation
       try {
           \Pond\Validate::get('email')->check($email);
       } catch(ValidationException $e){
            $this->logger->info('User email validation fail');
            return false;
       }

       // Password format validation
       try {
           \Pond\Validate::get('password')->check( $password );
       } catch(ValidationException $e) {
           $this->logger->info('Password validation fail');
           return false;
       }

       // Try to get the requested User or throw an exception
       try{
           $reqUser = User::where('email', $email)->firstOrFail();
           $this->logger->info("email already in use");
       } catch(ModelNotFoundException $e){
           return true;
       }

   }


}
