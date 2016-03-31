<?php
    namespace Pond;

    class User extends \Illuminate\Database\Eloquent\Model {
        public $primaryKey = 'user_id';

        public function getPasswordAttribute($value) {
            return Crypto::withHash($value, $this->salt);
        }
        return $this->hasOne('Pond\Lesson','foreign_key');
    }
?>
