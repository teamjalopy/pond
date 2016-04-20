<?php
    namespace Pond;

    use \Illuminate\Database\Eloquent\Model;
    use \Illuminate\Database\Capsule\Manager as Capsule;

    class Module extends Model {

        function content() {

            switch($this->content_type) {
            case 'quiz':
                return Quiz::find($this->content_id);
            case 'article':
                return null;
            case 'video':
                return null;
            }

        }

        function lesson() {
            return $this->belongsTo('Pond\Lesson','lesson_id');
        }

        function getContentAttribute() {
            return $this->content();
        }

        function getTypeAttribute() {
            return $this->content_type;
        }

        protected $appends = ['content','type'];
        protected $hidden = ['lesson','content_id','content_type'];

    }
