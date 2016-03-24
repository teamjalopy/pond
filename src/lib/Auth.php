<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Auth {

    static public function loginHandler(Request $req, Response $res): Response {
        $form = $req->getParsedBody();

        if(! $token = self::authenticate($form['username'],$form['password']) ) {
            return self::badAuthResponse($res);
        }


        return $res;
    } // loginHandler

    private static function authenticate($user, $password) {
        // Username format validation
        try {
            \Pond\Validate::get('username')->check( $form['username'] ?? null );
        } catch(ValidationException $e) {
            return null;
        }

        // Password format validation
        try {
            \Pond\Validate::get('password')->check( $form['password'] ?? null );
        } catch(ValidationException $e) {
            return null;
        }

        // Try to get the requested User or throw an exception
        try {
            $reqUser = User::where('username', $form['username'])->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return null;
        }

        // Check the password
        $knownP = $reqUser->password;
        $givenP = new Crypto($form['password'], $reqUser->salt);

        if(!Crypto::compare($knownP,$givenP)) {
            return null;
        }
    }

    static function badAuthResponse(Response $res): Response {
        $stat = new \Pond\StatusContainer();
        $stat->error('BadAuth');
        $stat->message('Check your username and password.');

        $res = $res->withStatus(401); // Unauthorized
        return $res->withJson($stat);
    }

    static function tokenResponse(Response $res, Token $t): Response {
        $stat = new \Pond\StatusContainer( ['token' => (string)$t] );
        $stat->success();
        $stat->message('Successfully logged in.');

        $res = $res->withStatus(201); // Created
        return $res->withJson($stat);
    }

} // Auth
