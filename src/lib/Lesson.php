<?php
    namespace Pond;


    class Lesson extends \Illuminate\Database\Eloquent\Model {

        protected $casts = [
            'published' => 'boolean',
        ];

        public function creator() {
            return $this->belongsTo('Pond\User', 'creator_id');
        }

        public function getCreatorAttribute() {
            return $this->creator()->get()->first();
        }

        public function students() {
            return $this->belongsToMany('Pond\User','enrollment','lesson_id','student_id');
        }

        protected $appends = ['creator'];
        protected $hidden = ['creator_id'];
    }
?>
