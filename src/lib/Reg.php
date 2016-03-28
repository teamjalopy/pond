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

class UserController extends Controller
{
  private $container;
  private $logger;

  function __construct(Container $c ){
    $this->container = $c;
    $this->logger = $this->container->get('logger');
  }

  public function userHandler(Request $request, Response $response): Response{
    $user = new Pond\User();
    $form = $request->getParsedBody();
    $stat = new \Pond\StatusContainer();
    $username = @form['username'];
    $password = @form['password'];

    if(validation($username, $password)){
      $stat->success();
      $stat->message('User sucessfully created.');
      $res = $res->withStatus(200);

      $user->user_id = @form['user_id'];
      $user->username = $username;
      $user->name = @form['name'];
      $user->type = @form['type'];
      $user->password = $password;
      $crypto = new Pond\Crypto($user->password);
      $user->salt = $crypto;
      $user->save();
    }

    else{
      $stat->error('Email or password invalid.');
      $stat->message('Check your email and password.');
      $response = $response->withStatus(401);
    }

    return $response->withJson($stat);

  }


  private function validation($username, $password) {
       $valid = true;

       // Username format validation
       $v = "/[a-zA-Z0-9_-.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+/";
       if(! preg_match($v, $username)){
         $this->logger->info('Username validation fail');
           $valid = false;
       }

       // Password format validation
       try {
           \Pond\Validate::get('password')->check( $password );
       } catch(ValidationException $e) {
           $this->logger->info('Password validation fail');
           $valid = false;
       }

       // Try to get the requested User or throw an exception
       $reqUser = User::where('username', $username)->firstOrFail();
       if($reqUser != null){
         $this->logger->info('Email is already in use');
         $valid = false;
       }

   }


}
