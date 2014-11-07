<?php 

class MyLike__ORM__MySQL__Select extends MyLike__ORM__MySQL{

	protected $_first;
	protected $_group = array();
	protected $_as_subquery;
	protected $_fields;
	protected $use_table_default;
	protected $callback;
	protected $distinct;
	protected $use_index_table = true;

	public function useIndexTable(){
		$arguments = func_get_args();
		if(array_key_exists(0, $arguments)){
			$this -> use_index_table = $arguments[0];
			return $this;
		} else {
			return $this -> use_index_table;
		}
	}
	
	public function fetchFirstValue(){
		$row = $this -> first(true) -> fetch();
		if(!empty($row)){
			$value = reset($row);
			if(is_array($value)){
				$value = reset($value);
			}
			return $value;
		} else {
			return null;
		}
	}
	
	
	public function setCallback($callback){
		$this -> callback = $callback;
		return $this;
	}
	
	public function removeCallback(){
		$this -> callback = null;
		return $this;
	}

	public function asSubquery(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$this -> _as_subquery = $args[0];
			return $this;
		} else {
			return $this -> _as_subquery;
		}
	}
	
	public function distinct(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$this -> distinct = $args[0];
			return $this;
		} else {
			return ($this -> distinct) ? " DISTINCT " : " ";
		}
	}

	public function useDefaultTable($arg){
		$this -> use_table_default = $arg;
		return $this;
	}

	public function first(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$this -> _first = $args[0];
			return $this -> limit($args[0]);
		} else {
			return $this -> _first;
		}
	}

	public function addGroup($grup){
		$this -> _group[] = $grup;
		return $this;
	}
	
	public function group(){
		$fields = "";
		foreach($this -> _group as $ke => $field){
			$coma  = ($ke) ? ',' : '';
			$fields .= $coma.$this -> process_field($field);
		}
		if(strlen($fields) > 0) $fields = " GROUP BY ".$fields." ";
		return $fields;
	}

	public function buildFields(){
		if(empty($this -> _fields)){
			return '*';
		}
		$fields = "";
		foreach($this -> _fields as $ke => $field){
			$coma  = ($ke) ? ',' : '';
			$fields .= $coma.$this -> process_field($field);
		}
		return $fields;
	}

	public function limit(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			if($args[0] == 0){
				$this -> _limit = '';
			}elseif(array_key_exists(1, $args) && ($args[1] > 0)){
				$this -> _limit = " LIMIT ".(($args[0] - 1) * $args[1])." , ".$args[1];
			} else {
				$this -> _limit = " LIMIT ".$args[0];
			}
			return $this;
		} else {
			return $this -> _limit;
		}
	}

	public function pagination(&$page, &$many, $num, &$start, &$pages){
		list($start, $page, $pages, $many) = $this -> getParent() -> getLimit($page, $many, $num);
		$this -> limit($page, $many);
		return $this;
	}

	public function addField($field){	
		foreach(func_get_args() as $field)
		$this -> _fields[] = $field;
		return $this;
	}

	public function __toString(){
		return 'SELECT '.$this -> distinct().$this -> buildFields() .' FROM '.$this -> getTableFrom().' '
			.$this -> conditions()
			.$this -> group()
			.$this -> order()
			.$this -> limit();
	}
	
	public function fetch(){
		$callback = $this -> callback;
		$to_callback = !is_null($callback);
		if($to_callback && is_array($callback) && count($callback)>2){
			$default_parameter = array_slice($callback,2);
			$callback = array_slice($callback, 0, 2);
		} else {
			$default_parameter = array();
		}
		$ds = $this -> getParent() -> getDatasource();
		$return = array();
		$fields = array();
		$buffer = array();
		$args = func_get_args();
		if(array_key_exists(0,$args))
		$source =  $ds->query($args[0]);
		else
		$source =  $ds->query((string)$this);
		if($source){
			$field_count = $source -> columnCount();
			for ($i = 0; $i < $field_count;$i++) {
				$finfo = $source->getColumnMeta($i);
				$fields[] = array('name' => $finfo['name'], 'table' => $finfo['table']);/*  */
			}

			$current_row = 0;
			while ($row = $source->fetch()) {
				$buffer = array();
				foreach($fields as $n => $info){
					if(!empty($info['table']) && $this -> useIndexTable()){
						$buffer[$info['table']][$info['name']] = $row [$n];
					} else {
						$buffer[$info['name']] = $row [$n];
					}
				}
				if($to_callback){
					$parameter = array_merge(array($buffer,$current_row++), $default_parameter);
					$buffer = call_user_func_array($callback, $parameter);
					if(!is_null($buffer)){
						$return[] = $buffer; 
					} else {
						$current_row--; 
					}
				} else {
					$return[] = $buffer;
				}
			}
		}
		$first = $this -> first();
		if($first && !empty($return)){
			return $return[0];
		} else {			
			return $return;
		}
	}
}