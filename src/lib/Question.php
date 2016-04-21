<?php
    namespace Pond;


    class Question extends \Illuminate\Database\Eloquent\Model {
        function quiz() {
            return $this->belongsTo('Pond\Quiz','quiz_id');
        }
    }
