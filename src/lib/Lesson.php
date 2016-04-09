<?php
    namespace Pond;


    class Lesson extends \Illuminate\Database\Eloquent\Model {
        public $primaryKey = 'lesson_id';

        protected $casts = [
            'published' => 'boolean',
        ];

        public function creator() {
            return $this->belongsTo('Pond\User', 'creator_id');
        }

        public function getCreatorAttribute() {
            return $this->creator()->get()->first();
        }

        protected $appends = ['creator'];
        protected $hidden = ['creator_id'];
    }
?>
