<?php

class MyLike__ArrayObject__Core{

	protected $data;
	
	private $_nullvalue;

	public function setNullValue($value){
		$this -> _nullvalue = $value;
		return $this;
	}

	public function getNullValue(){
		return $this -> _nullvalue;
	}
	

	public function toArray(){
		$data = array();
		if(MyLike__ArrayObject__Magic::foreachable($this -> data))
		foreach($this -> data as $key => $content){
			if(is_object($content)){
				if($content instanceof Arrayobj){
					$data[$key] = $content -> toArray();
				} elseif($content instanceof ArrayObject) {
					$data[$key] = $content -> getArrayCopy();
				} elseif(method_exists($content,"toArray")) {
					$data[$key] = $content -> toArray();
				} elseif(method_exists($content,"getArrayCopy")) {
					$data[$key] = $content -> getArrayCopy();
				}
			}else $data[$key] = $content;
		}
		return $data;
	}
	
	public function getArrayCopy(){
		return $this -> toArray();
	}
	
	public function reset(){
		$this -> data = null;
		return $this;
	}

}