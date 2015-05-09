<?php 

class MyLike__Controller__Notfound  extends MyLike__Controller__Web{
	
	public function execute(){
	}
	
	public function actionBefore(){
		$this -> autoRender(false);
	}

	public function actionAfter(){
		$this -> getViewObject() -> setRespondCode(404);
	}
	
	public function process(){
		$this -> execute();
		$this -> actionAfter();
	}
}