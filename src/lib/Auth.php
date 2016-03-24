<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Auth {

    static public function loginHandler(Request $req, Response $res): Response {
        $form = $req->getParsedBody();

        // Username format validation
        try {
            \Pond\Validate::get('username')->check( $form['username'] ?? null );
        } catch(ValidationException $e) {
            return badAuthResponse($res);
        }

        // Password format validation
        try {
            \Pond\Validate::get('password')->check( $form['password'] ?? null );
        } catch(ValidationException $e) {
            return badAuthResponse($res);
        }

        // Try to get the requested User or throw an exception
        try {
            $reqUser = User::where('username', $form['username'])->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return badAuthResponse($res);
        }

        // Check the password
        return $res;
    } // loginHandler

    static function badAuthResponse(Response $res): Response {
        $stat = new \Pond\StatusContainer();
        $stat->error('BadAuth');
        $stat->message('Check your username and password.');

        $res = $res->withStatus(401); // Unauthorized
        return $res->withJson($stat);
    }

} // Auth
