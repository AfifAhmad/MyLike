<?php 


class MyLike__ORM__MySQL__Update extends MyLike__ORM__MySQL{

	protected $_new = array();
	protected $_first;

	public function limit(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			if($args[0] == 0){
				$this -> _limit = '';
			}else{
				$this -> _limit = " LIMIT ".$args[0];
			}
			return $this;
		} else {
			return $this -> _limit;
		}
	}
	
	public function newValue($field, $value, $escaped = true){
		$this -> _new[] = array( $field, $value, $escaped);
		return $this;
	}

	public function __toString(){
		return "UPDATE {$this -> getTableFrom()} SET ".$this -> buildNewValues()
					.' '.$this -> conditions().$this -> limit();
	}
	
	protected function buildNewValues(){
		$fields = "";
		foreach($this -> _new as $ke => $values){
			$coma  = ($ke) ? ',' : '';
			$fields .= $coma.$this -> process_field($values[0])." = ".
				($values[2] ? $this -> escape($values[1]) : $values[1]);
		}
		return $fields;
	}

	public function query(){
		$qy = $this -> getParent() -> getDatasource() -> query((string) $this);
		return $qy;
	}
}