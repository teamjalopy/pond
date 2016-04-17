<?php
    namespace Pond;

    use \Illuminate\Database\Eloquent\Model;

    class Lesson extends Model {

        protected $casts = [
            'published' => 'boolean',
        ];

        // Module relations (polymorphic)
        public function modules() {
            return $this->hasMany('Pond\Module','lesson_id');
        }

        public function quizzes() {
            return $this->morphedByMany('Pond\Quiz','module');
        }

        // Relations
        public function creator() {
            return $this->belongsTo('Pond\User', 'creator_id');
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

        protected $appends = ['creator'];
        protected $hidden = ['creator_id'];
    }
?>
