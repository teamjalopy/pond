<?php
    namespace Pond;

    class Quiz extends ModuleProtocol {

        protected function getTypeAttribute() {
            return 'quiz';
        }

        function questions() {
            return $this->hasMany('Pond\Question');
        }

    }
