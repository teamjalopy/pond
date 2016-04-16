<?php
    namespace Pond;


    class Lesson extends \Illuminate\Database\Eloquent\Model {

        protected $casts = [
            'published' => 'boolean',
        ];

        // Relations
        public function creator() {
            return $this->belongsTo('Pond\User', 'creator_id');
        }

        public function modules() {
            return $this->hasMany('Pond\Module')->orderBy('order');
        }

        public function students() {
            return $this->belongsToMany('Pond\User','enrollment','lesson_id','student_id');
        }

        // Generated Attributes
        public function getModuleCountAttribute() {
            return $this->modules()->count();
        }

        public function getCreatorAttribute() {
            return $this->creator()->get()->first();
        }

        protected $appends = ['creator', 'module_count'];
        protected $hidden = ['creator_id'];
    }
?>
