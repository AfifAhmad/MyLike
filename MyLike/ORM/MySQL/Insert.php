<?php 

class MyLike__ORM__MySQL__Insert extends MyLike__ORM__MySQL{

	protected $_first;
	protected $_fields;
	protected $_values = array();
	protected $_replace = false;
	protected $on_duplicate;


	public function replace(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$this -> _replace = $args[0];
			return $this;
		} else {
			return ($this -> _replace) ? "REPLACE" : "INSERT";
		}
	}

	public function addValues($args){
		$this -> _values[] = $args;
		return $this;
	}

	public function onDuplicate(){
		$args = func_get_args();
		if(is_null($this -> on_duplicate)){
			$this -> on_duplicate = array();
		}
		if($args){
			foreach($args as $arg){
				$this -> on_duplicate[] = $arg;
			}
			return $this;
		} else {
			if($this -> on_duplicate)
			return " ON DUPLICATE KEY UPDATE ".implode(',',$this -> on_duplicate);
			else 
			return "";
		}
	}

	public function buildValues(){
		$output = "";
		foreach($this -> _values as $idx1 => $values){
			$coma1 = ($idx1>0) ? "," : "";
			$output1 = "";
			foreach($values as $idx2 => $value){
				$coma2 = ($idx2>0) ? "," : "";
				$output1 .= $coma2. ((is_array($value))? array_shift($value) : $this -> displayValue($value));
			}
			$output .= $coma1."(".$output1.")";
		}
		return $output;
	}

	public function buildFields(){
		$fields = "";
		foreach($this -> _fields as $ke => $field){
			$coma  = ($ke>0) ? ',' : '';
			$fields .= $coma.((preg_match('#^[a-z0-9_-]+$#', $field))?'`'.$field.'`':$field);
		}
		return $fields;
	}

	public function addField(){
		foreach(func_get_args() as $field)
		$this -> _fields[] = $field;
		return $this;
	}

	public function __toString(){
		return $this -> replace()." INTO `{$this -> getParent() -> getTableName()}` ({$this -> buildFields()}) VALUES {$this -> buildValues()}".$this -> onDuplicate();
	}
	
	public function queryOnce($array){
		$fields = array_keys($array);
		foreach($fields as $field){
			$this -> addField($field);
		}
		return $this -> addValues(array_values($array))
			-> query();
	}
	
	public function query(){
		$query = $this -> getParent() -> getDatasource() ->query((string)$this);
		if($query){
			return true;
		} else {
			return false;
		}
	}
}