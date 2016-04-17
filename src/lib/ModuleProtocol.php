<?php
    namespace Pond;

    use \Illuminate\Database\Eloquent\Model;

    abstract class ModuleProtocol extends Model {

        abstract protected function getTypeAttribute();

        function lessons() {
            return $this->morphToMany('Pond\Lesson','module');
        }

        protected $appends = ['type'];

    }
