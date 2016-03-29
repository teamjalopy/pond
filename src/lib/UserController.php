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

  public function registrationHandler(Request $request, Response $response): Response {
    $stat = new \Pond\StatusContainer();
    $form = $request->getParsedBody();

    $email = @$form['email'];
    $password = @$form['password'];
    $crypto = new Pond\Crypto($password);

    $stat->error('Email or password invalid.');
    $stat->message('Check your email and password.');
    $response = $response->withStatus(401);

    // if(validation($email, $password)){
    //   $stat->success();
    //   $stat->message('User sucessfully created.');
    //   $res = $res->withStatus(200);
    //
    //   $user->user_id = @$form['user_id'];
    //   $user->email = $email;
    //   $user->name = @$form['name'];
    //   $user->type = @$form['type'];
    //   $user->password = $crypto->getHash();
    //   $user->salt = $crypto->getSalt();
    //   $user->save();
    // }
    //
    // else{
    //   $stat->error('Email or password invalid.');
    //   $stat->message('Check your email and password.');
    //   $response = $response->withStatus(401);
    // }

    return $response->withJson($stat);

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
       $reqUser = User::where('email', $email)->firstOrFail();
       if($reqUser != null){
           $this->logger->info('Email is already in use');
           return false;
       }

       return true;

   }


}
