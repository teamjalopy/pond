<?php
    namespace Pond;


    class Quiz extends \Illuminate\Database\Eloquent\Model {

        function questions() {
            return $this->hasMany('Pond\Question');
        }

    }
