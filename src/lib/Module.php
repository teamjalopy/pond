<?php
    namespace Pond;


    class Module extends \Illuminate\Database\Eloquent\Model {

        function lesson() {
            return $this->belongsTo('Pond\Lesson');
        }

        function content() {
            return $this->morphTo('contentable');
        }

        function getTypeAttribute() {
            return $this->attributes['contentable_type'];
        }

        function getContentAttribute() {
            return $this->content()->first()->get();
        }

        protected $hidden = ['contentable_id', 'contentable_type'];
        protected $appends = ['type','content'];

    }
