<?php 

class MyLike__Controller__Api extends MyLike__Controller__Controller{

	
	public function process(){	
		$act = $this -> getRoute('action');
		if(strlen($act) > 0 ) {
			$action_function = "action_".$this -> getRoute('action');
			$this -> setExtendedContent($this -> getRoute('action'));
		}else{
			$action_function = "execute";
		}
		$args = $this->getRoute('args');
		
		$router = $this->getRoute();
		
		$this -> setContent($router['content']);
		
		if(!$this -> skipAction()){
			if(is_array($args) && !empty($args))
			call_user_func_array(array($this, $action_function),$args);
			else 
			$this -> $action_function();
		}
		$this -> after_execute();
	}

}