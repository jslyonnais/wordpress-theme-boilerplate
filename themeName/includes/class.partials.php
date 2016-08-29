<?php
if(!class_exists('Partials')){
	class Partials{
		public function __construct(){

		}

        public function example($options=""){
			$defaults = array(
				'extraClass' => ''
			);
			$options = self::buildOptions($defaults,$options);
			include('partials/example.php');
		}



		private function buildOptions($defaults, $args, $output = "OBJECT"){
			$out = array();
			foreach($defaults as $k => $v){
				if(isset($args[$k])){
					$out[$k] = $args[$k];
				}else{
					$out[$k] = $v;
				}
			}

			if($output === "OBJECT"){
				return (object)$out;
			}else{
				return $out;
			}

		}
	}
}
?>
