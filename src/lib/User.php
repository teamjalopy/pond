<?php
    namespace Pond;

    class User extends \Illuminate\Database\Eloquent\Model {
        public $primaryKey = 'user_id';
        protected $fillable = array('id', 'username', 'name', 'type', 'password', 'salt', 'created_at', 'updated_at');
    }
?>
