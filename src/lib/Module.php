<?php
    namespace Pond;

    use \Illuminate\Database\Eloquent\Model;

    class Module extends Model {

        public function getModuleTypeAttribute($value) {
            switch($value) {
                case 'Pond\\Quiz':
                    return 'quiz';
                case 'Pond\\Article':
                    return 'article';
                case 'Pond\\Video':
                    return 'video';
                default:
                    return null;
            }
        }

    }
