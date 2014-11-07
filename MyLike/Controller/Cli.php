<?php 

class MyLike__Controller__Cli extends MyLike__Controller__Controller{
	
	public function execute(){
	}
	
	public function after_execute(){
		$this -> autoRender(false);
	}
	
	public function process(){
		$this -> execute();
		$this -> after_execute();
	}
}