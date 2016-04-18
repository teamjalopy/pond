<?php
    namespace Pond;

    use \Illuminate\Database\Eloquent\Model;

    class Quiz extends Model {

        function questions() {
            return $this->hasMany('Pond\Question');
        }

        protected $hidden = ['created_at','updated_at'];

    }
