<?php 

class MyLike__Controller__Notfound  extends MyLike__Controller__Web{
	
	public function execute(){
	}
	
	public function before_execute(){
		$this -> autoRender(false);
	}

	public function after_execute(){
		$this -> getViewObject() -> setRespondCode(404);
	}
	
	public function process(){
		$this -> execute();
		$this -> after_execute();
	}
}