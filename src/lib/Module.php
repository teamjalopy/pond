<?php
    namespace Pond;

    use \Illuminate\Database\Eloquent\Model;

    class Module extends Model {

        function content() {
            return DB::table( $this->content_type )->where('id', $this->content_id );
        }

        function lesson() {
            return $this->belongsTo('Pond\Lesson','lesson_id');
        }

        function getContentAttribute() {
            return $this->content();
        }

        protected $appends = ['content'];

    }
