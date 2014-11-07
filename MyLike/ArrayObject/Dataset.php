<?php


class MyLike__ArrayObject__Dataset extends MyLike__ArrayObject__Core{

	protected $_data = array();
	private $_nullvalue;

	public function setNullValue($value){
		$this -> _nullvalue = $value;
		return $this;
	}

	public function getNullValue(){
		return $this -> _nullvalue;
	}

	public function set(){
		$indexes = func_get_args();
		$new_value = array_pop($indexes);
		$data = $this -> _data;
		$data = &$buffer;
		foreach($indexes as $index){
			$data = &$data[$index];
		}
		$data = $new_value;
		$this->_data = $buffer;
		return $this;
	}

	public function get(){
		$arguments = func_get_args();
		$data = $this->_data;
		foreach($arguments as $argument){
			if(is_array($data) && array_key_exists($argument, $data)){
				$data = $data[$argument];
			}else {
				$data = $this -> getNullValue();
				break;
			}
		}
		return $data;
	}

	public function remove(){
		$arguments = func_get_args();
		$lastkey = array_pop($arguments);
		$data = $this->_data;
		$buffer = &$data;
		foreach($arguments as $argument){
			if(is_array($data) && array_key_exists($argument, $buffer))
			$buffer = &$buffer[$argument];
			else return;
		}
		unset($sess[$lastkey]);
		$this->_data = $data;
		return $this;
	}

	public function reset(){
		$this->_data = array();
		return $this;
	}
	
}