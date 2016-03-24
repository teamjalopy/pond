<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \RuntimeException;

use Slim\Container;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Hmac\Sha256 as Signer;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;


class Auth {

    private $container;
    private $logger;

    function __construct(Container $c) {
        $this->container = $c;
        $this->logger = $this->container->get('logger');
    }

    public function loginHandler(Request $req, Response $res): Response {
        $form = $req->getParsedBody();

        if(! $token = self::authenticate(@$form['username'],@$form['password']) ) {
            return self::badAuthResponse($res);
        } else {
            return self::tokenResponse($res,$token);
        }

    } // loginHandler

    public function isRequestAuthorized(Request $req, $forUID = null): bool {
        // First, check for Authorization: Bearer [token]
        // header

        if(!$req->hasHeader('Authorization')) {
            return false;
        }

        $tokenHeader = $req->getHeader('Authorization')[0]; // Bearer xxxxx.yyyyy.zzzzz
        $tokenString = explode(' ', $tokenHeader)[1]; // xxxxx.yyyyy.zzzzz

        try {
            $parser = new Parser();
            $token = $parser->parse($tokenString);
        } catch(RuntimeException $e) {
            // Token parsing failed, bad token assumed.
            return false;
        }

        // Step 1: Validate the assertions in the JWT

        $tokenSettings = $this->container->get('settings')['token'];

        $issuer   = $tokenSettings['iss'];
        $audience = $tokenSettings['aud'];
        $lifetime = $tokenSettings['lifetime'];

        $data = new ValidationData();
        $data->setIssuer($issuer);
        $data->setAudience($audience);

        // If this token is being tested for user-specific auth
        if(isset($forUID)) {
            if( !($claimUID = $token->getClaim('uid')) ) {
                return false;
            } else {
                if($forUID != $claimUID) {
                    return false;
                }
            }
        }

        // Compare the Token with ValidationData
        if(!$token->validate($data)) {
            $this->logger->info("Invalid token claims.");
            return false;
        }

        // Step 2. Verify signature
        // [NOTA BENE] don't let JWT assert algorithm.
        // Use predetermined algorithm and key for verification.
        $signer = new Signer();
        $signKey  = $tokenSettings['key'];
        if($token->verify($signer, $signKey)) {
            return true;
        } else {
            $this->logger->info("Could not verify token signature.");
            return false;
        }
    }

    private function authenticate($username, $password) {
        // Username format validation
        try {
            \Pond\Validate::get('username')->check( $username );
        } catch(ValidationException $e) {
            $this->logger->info('Username validation fail');
            return null;
        }

        // Password format validation
        try {
            \Pond\Validate::get('password')->check( $password );
        } catch(ValidationException $e) {
            $this->logger->info('Password validation fail');
            return null;
        }

        // Try to get the requested User or throw an exception
        try {
            $reqUser = User::where('username', $username)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            $this->logger->info('User model retrieval fail');
            return null;
        }

        // Check the password
        $knownP = $reqUser->password;
        $givenP = new Crypto($password, $reqUser->salt);

        if(!Crypto::compare($knownP,$givenP)) {
            $this->logger->info('Crypto authentication fail');
            return null;
        } else {
            $tokenBuilder = new \Lcobucci\JWT\Builder();
            $signer = new Signer();

            $tokenSettings = $this->container->get('settings')['token'];
            $signKey  = $tokenSettings['key'];

            $issuer   = $tokenSettings['iss'];
            $audience = $tokenSettings['aud'];
            $lifetime = $tokenSettings['lifetime'];

            $token = $tokenBuilder->setIssuer($issuer)
                                 ->setAudience($audience)
                                 ->setIssuedAt(time())
                                 ->setExpiration(time() + $lifetime)
                                 ->set('uid', $reqUser->getKey())
                                 ->sign($signer, $signKey)
                                 ->getToken();
            $this->logger->info('Building token');
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
