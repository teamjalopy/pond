<?php
    namespace Pond;

    use \Illuminate\Database\Eloquent\Model;

    class Quiz extends Model {

        function questions() {
            return $this->hasMany('Pond\Question');
        }

    }
