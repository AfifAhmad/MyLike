<?php 

class MyLike__Controller__Cli extends MyLike__Controller__Controller{
	
	public function execute(){
	}
	
	public function actionAfter(){
		$this -> autoRender(false);
	}
	
	public function process(){
		$this -> execute();
		$this -> actionAfter();
	}
}