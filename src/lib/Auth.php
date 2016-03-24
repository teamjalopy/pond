<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Auth {

    static public function loginHandler(Request $req, Response $res) {
        $form = $req->getParsedBody();

        // Username format validation
        try {
            \Pond\Validate::get('username')->check( $form['username'] );
        } catch(ValidationException $e) {
            $stat = new \Pond\StatusContainer();
            $stat->error('InvalidUsername');
            $stat->message('Please enter a valid username.');

            $res = $res->withStatus(400);
            return $res->withJson($stat);
        }

        // Password format validation
        try {
            \Pond\Validate::get('password')->check( $form['password'] );
        } catch(ValidationException $e) {
            $stat = new \Pond\StatusContainer();
            $stat->error('InvalidPassword');
            $stat->message('Please enter a valid password.');

            $res = $res->withStatus(400); // Bad Request
            return $res->withJson($stat);
        }

        // Try to get the requested User or throw an exception
        try {
            $reqUser = User::where('username', $form['username'])->firstOrFail();
        } catch(ModelNotFoundException $e) {
            $stat = new \Pond\StatusContainer();
            $stat->error('BadAuth');
            $stat->message('Check your username and password.');

            $res = $res->withStatus(401); // Unauthorized
            return $res->withJson($stat);
        }
        return $res;
    } // loginHandler

} // Auth
