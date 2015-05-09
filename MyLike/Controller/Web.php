<?php


class MyLike__Controller__Web extends MyLike__Controller__Controller{

	
	
	protected function isAjax(){
		return $this -> getRequest() -> isAjax();
	}

	public function redirect(){
		$args = func_get_args();
		if(preg_match('#^(?:/|^http(?:s)?:)#', $args[0])){
			$url = $args[0];
		} else {
			$url = $this -> getRequest() -> base();
			if(array_key_exists(0, $args)){
				$url .= $args[0];
			}
		}
		header("Location: ".$url);
		die;
	}
	
	public function process(){
		$act = $this -> getRoute('action');
		if(strlen($act) > 0 ) {
			$action_function = $this -> getRoute('action')."Action";
			$this -> setExtendedContent($this -> getRoute('action'));
		}else{
			$action_function = "action";
		}
		$args = $this->getRoute('args');
		
		$this -> setContent($this->getRoute('content'));
		
		if(!$this -> skipAction()){
			if(is_array($args) && !empty($args))
			call_user_func_array(array($this, $action_function),$args);
			else 
			$this -> $action_function();
		}
		
		$this -> actionAfter();
		
		return $this;
	}

	public function setHTMLCode($code){
		$this -> getViewObject() -> setRespondCode($code);
		return $this;
	}

	public function redirectToReferrer($alternate = ''){
		if($this -> getRequest() -> isReferrerInside()){
			$location = substr($_SERVER['HTTP_REFERER'], 
				strlen($this -> getRequest() -> getProtocolHostBase()));
			$this -> redirect($location);
		}else{
			$this -> redirect($alternate);
		}
	}


}