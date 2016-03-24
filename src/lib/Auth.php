<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Hmac\Sha512 as Signer;

class Auth {

    static public function loginHandler(Request $req, Response $res): Response {
        $form = $req->getParsedBody();

        if(! $token = self::authenticate($form['username'],$form['password']) ) {
            return self::badAuthResponse($res);
        } else {
            return self::tokenResponse($res,$token);
        }

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
        } else {
            $tokenBuilder = new Lcobucci\JWT\Builder();
            $signer = new Signer();
            $token = $tokenBuilder->setIssuer('http://pondedu.me')
                                 ->setAudience('http://pondedu.me')
                                 ->setIssuedAt(time())
                                 ->setExpiration(time()+ 1 * 7 * 24 * 60 * 60) // 1 week
                                 ->set('uid', $reqUser->getKey())
                                 ->sign($signer, $settings['token']['key'])
                                 ->getToken();
            return $token;
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
