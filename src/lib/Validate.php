<?php

namespace Pond;

use Respect\Validation\Validator as v;

class Validate {

    public static function get(string $name) {
        switch ($name) {
            case 'email':
                return v::notEmpty()->email()->noWhitespace()->length(3,254);
                break;

            case 'password':
                return v::notEmpty()->noWhitespace()->length(8,128);
                break;

            default:
                throw new Exception("Validator `$name` does not exist", 1);
                break;
        }
    }

}
