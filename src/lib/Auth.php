<?php

namespace Pond;

use Slim\Http\Request;
use Slim\Http\Response;

use Respect\Validation\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Exception;
use \RuntimeException;
use \InvalidArgumentException;

use Slim\Container;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Hmac\Sha256 as Signer;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;


class Auth {

    private $container;
    private $logger;

    public $cachedUser;

    function __construct(Container $c) {
        $this->container = $c;
        $this->logger = $this->container->get('logger');
    }

    public function loginHandler(Request $req, Response $res): Response {
        $form = $req->getParsedBody();

        if(! $token = self::authenticate(@$form['email'],@$form['password']) ) {
            return self::badAuthResponse($res);
        } else {
            return self::tokenResponse($res,$token,$this->cachedUser);
        }

    } // loginHandler

    public function isRequestAuthorized(Request $req, $forUID = null): bool {

        // Step 1. check for Authorization: Bearer [token]
        // header

        try {
            $tokenString = $this->getJWTFromHeader($req);
        } catch(Exception $e) {
            $this->logger->info("getJWTFromHeader threw exception: ".$e->getMessage());
            return false;
        }

        // Step 2. parse JWT

        $this->logger->info("Attempting to parse JWT.");
        $parser = new Parser();

        try {
            $token = $parser->parse($tokenString);
        } catch(Exception $e) {
            // Token parsing failed, bad token assumed.
            $this->logger->info("Could not parse JWT: Malformed or missing.");
            return false;
        }

        // Step 3. Validate the assertions in the JWT
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

        // Step 4. Verify signature
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

    // Returns user id or throws exception
    public function getAuthorizedUserID(Request $req): int {
        if(!$this->isRequestAuthorized($req)) {
            throw new RuntimeException("There is no authorized user.",1);
        }

        $tokenString = $this->getJWTFromHeader($req);

        $this->logger->info("Attempting to parse JWT.");
        $parser = new Parser();

        try {
            $token = $parser->parse($tokenString);
        } catch(Exception $e) {
            // Token parsing failed, bad token assumed.
            $this->logger->info("Could not parse JWT: Malformed or missing.");
            return false;
        }

        $this->logger->info("UID claim read: " . $token->getClaim('uid'));
        return $token->getClaim('uid');
    }

    private function authenticate($email, $password) {
        // Email format validation
        try {
            \Pond\Validate::get('email')->check( $email );
        } catch(ValidationException $e) {
            $this->logger->info('User email validation fail');
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
            $reqUser = User::where('email', $email)->firstOrFail();
            $this->cachedUser = $reqUser;
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

    private function getJWTFromHeader(Request $req): string {
        try {
            $tokenHeader = $req->getHeader('Authorization')[0]; // Bearer xxxxx.yyyyy.zzzzz
            $tokenHeaderValue = explode(' ', $tokenHeader); // ['Bearer', 'xxxxx.yyyyy.zzzzz']
            if(isset($tokenHeaderValue[1])) {
                $this->logger->info("Parsed Authorization header.");
                return $tokenHeaderValue[1]; // xxxxx.yyyyy.zzzzz
            } else {
                $this->logger->info("Malformed Authorization header. Unauthorized.");
                throw new InvalidArgumentException("Authorization header malformed.", 1);
            }
        } catch(Exception $e) {
            $this->logger->info("Failed to parse Authorization header.");
            throw new InvalidArgumentException("Could not parse Authorization header.", 1);
        }
    }

    static function badAuthResponse(Response $res): Response {
        $stat = new \Pond\StatusContainer();
        $stat->error('BadAuth');
        $stat->message('Check your email and password.');

        $res = $res->withStatus(401); // Unauthorized
        return $res->withJson($stat);
    }

    static function tokenResponse(Response $res, Token $t, User $u): Response {
        $stat = new \Pond\StatusContainer( ['token' => (string)$t, 'user' => $u] );
        $stat->success();
        $stat->message('Successfully logged in.');

        $res = $res->withStatus(201); // Created
        return $res->withJson($stat);
    }

} // Auth
