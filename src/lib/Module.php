<?php
    namespace Pond;


    class Module extends \Illuminate\Database\Eloquent\Model {

        function lesson() {
            return $this->belongsTo('Pond\Lesson');
        }

    }
