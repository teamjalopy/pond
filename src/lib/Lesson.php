<?php
    namespace Pond;
	
    class Lesson extends \Illuminate\Database\Eloquent\Model {
        public $primaryKey = 'lesson_id';
		
		
		public function creator(){
			return $this->hasOne('Pond\User');
		}
		
    }
?>