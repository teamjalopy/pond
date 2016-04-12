<?php
    namespace Pond;

    class Enrollment extends \Illuminate\Database\Eloquent\Model {

        protected $table = "enrollment";

        protected $casts = [
            'complete' => 'boolean',
        ];

        public function student() {
            return $this->belongsTo('Pond\User','student_id');
        }

        public function lesson() {
            return $this->hasOne('Pond\Lesson');
        }

        public function currentModule() {
            return $this->hasOne('Pond\Module','current_module');
        }

    }
?>
