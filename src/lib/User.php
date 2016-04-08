<?php
    namespace Pond;

    class User extends \Illuminate\Database\Eloquent\Model {

        public function getPasswordAttribute($value) {
            return Crypto::withHash($value, $this->salt);
        }

        public function lessons() {
            return $this->hasMany('Pond\Lesson','creator_id');
        }

        protected $casts = [
            'validated' => 'boolean',
        ];

        protected $hidden = ['password','salt','created_at','updated_at','validation_token','type'];

        public function getIsTeacherAttribute() {
            return $this->attributes['type'] == 'TEACHER';
        }

        public function getIsStudentAttribute() {
            return $this->attributes['type'] == 'STUDENT';
        }

        protected $appends = ['is_teacher', 'is_student'];
    }
?>
