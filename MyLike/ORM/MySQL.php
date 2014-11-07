<?php 


abstract class MyLike__ORM__MySQL{

	protected $_parent;
	protected $_where = array();
	protected $_limit;
	protected $_tables = array();
	protected $_table;
	protected $table_alias;
	protected $_order = array();
	protected $_join = array();

	public function __construct($parent){
		$this -> _parent = $parent;
	}
	
	public function getParent(){
		return $this -> _parent;
	}
	
	public function setTableAlias($table){
		$this -> table_alias = $table;
		return $this;
	}
	

	public function getTableAlias(){
		if($this -> table_alias){
			return $this -> table_alias;
		} else {
			return $this -> getParent() -> getTableAlias();
		}
	}

	public function setMainTable($table){
		$this -> _table = $table;
		return $this;
	}

	public function addTable($arg){
		foreach(func_get_args() as $arg)
		$this -> _tables[] = $arg;
		return $this;
	}

	public function addOrder($arg){
		foreach(func_get_args() as $arg)
		$this -> _order[] = $arg;
		return $this;
	}

	public function displayValue($value){
		if(is_string($value)){
			return $this -> escape($value);
		} elseif(is_bool($value)){
			return ($value) ? "true" : "false";
		} elseif(is_null($value)){
			return "null";
		} elseif(is_numeric($value)){
			return $value;
		} elseif(is_array($value)){
			if(!empty($value['escaped'])){
				if($value['escaped'] === true){
					return $this -> escape($value);
				} else {
					return $value[0];
				}
			} elseif(count($value)>0) {
				$i = 0;
				$v = "";
				foreach($value as $val){
					$v .= ($i++>0?",":"").$this -> escape($val);
				}
				return  '('.$v.')';
			}
		}
	}
	
	public function order(){
		if(!empty($this -> _order)){
			$output = "";
			foreach($this -> _order as $order){
				if(preg_match("#(.*)((?:\s+(?:ASC|DESC))?)$#i",$order, $match)){
					$output .= ",".$this -> process_field($match[1]).$match[2];
				}
			}
			return " ORDER BY ".preg_replace("#^,#","",$output);
		} else {
			return "";
		}
	}

	public function addJoin(){
		foreach(func_get_args() as $arg)
		$this -> _join[] = $arg;
		return $this;
	}

	public function join(){
		$_join = "";
		foreach($this -> _join as $join){
			$_join .= (!empty($join['type'])) ? " ".$join['type']." " : " ";
			if(is_string($join['model']))
			$_join .= "JOIN ".$this -> getParent() -> getTableFrom($join['model']);
			elseif(is_object($join['model'])) {
				$_join .= "JOIN (".$join['model'].") AS `".$join['model'] -> getTableAlias()."`";
			}
			if(!empty($join['on'])){
				$whereDefault = $this -> _where;
				$this -> _where = array();
				if(is_string($join['on'])){
					$this -> _where[] = $join['on'];
				} elseif(is_array($join['on'])) {
					foreach($join['on'] as $on){
						if(is_array($on)){
							call_user_func_array(array($this,'where'), $on);
						}elseif(is_string($on)) 
						$this -> _where[] = $on;
					}
				}
				$_join .= " ".$this -> conditions(true);
				$this -> _where = $whereDefault;
			}
		}
		return $_join;
	}

	protected function escape($value){
		return $this -> getParent() -> qstr($value);
	}

	public function where( $left, $right = "", $condition = "=", $separator = "AND"){
		$arguments = func_get_args();
		if(!array_key_exists(1, $arguments)){
			$this -> _where[] = array(
					'left' => $left, 
					'right' => '', 
					'condition' => '', 
					'separator' => 'AND' 
				);
		} else {
			$this -> _where[] = array(
					'left' => $left, 
					'right' => $right, 
					'condition' => $condition, 
					'separator' => $separator 
				);
		}
		return $this;
	}
	
	
	public function conditions($join = false){
		$str = "";
		$encapsulated = count($this -> _where) > 1;
		foreach($this -> _where as $ke => $where){
			if(is_string($where)){
				$coma  = ($ke) ? " AND " : '';
				$add_where = $where;
			} else {
				$coma  = ($ke) ? " ".(!empty($where['separator'])? $where['separator'] : "AND")." " : '';
				$add_where = " FALSE ";
				$new_condition = $this -> process_field($where['left']);
				if(array_key_exists('condition', $where) && strlen($where['condition'])){
					if( array_key_exists('right', $where)){
						$value = $this -> displayValue($where['right']);
						if(strlen($value)){
							$add_where = $new_condition . ' '.$where['condition'].' '.$value;
						}
					} else {
						$add_where = $new_condition;
					}
				} else {
					$add_where = $new_condition;
				}
			}
			if($encapsulated){
				$add_where = "(".$add_where.")";
			}
			$str .= $coma.$add_where;
		}
		if(strlen($str) > 0){
			$str = ($join?"":" WHERE ").$str;
			if(strlen($join)>0){
				$str = " ON (".$str.")";
			}
		}
		return $str;
	}
	
	protected function process_field($field){
		if(preg_match('#^(?:(?:(?P<segment1>(?:[a-z0-9_]+))|(?P<segment2>(?:`[a-z0-9_]+`)))\.)?(?:(?P<segment3>(?:[a-z0-9_]+))|(?P<segment4>(?:`[a-z0-9_]+`)))$#six', $field, $matches)){
			if($matches['segment1']){
				$table = "`".$matches['segment1']."`";
			} elseif($matches['segment2']){
				$table = $matches['segment2'];
			} else {
				$table = "`".$this -> getTableAlias()."`";
			}
			if($matches['segment3']){
				$field = "`".$matches['segment3']."`";
			} elseif($matches['segment4']){
				$field = $matches['segment4'];
			} 
			return $table.".".$field;
		} else {
			return $field;
		}
	}

	public function getTable(){
		return "`".$this -> getParent() -> getTableName()."` AS `". $this -> getTableAlias()."` ".$this -> join();
	}
	
	public function getTableFrom(){
		if(is_null($this -> _table)||$this -> _table === null){
			$main = $this -> getParent() -> getTableFrom();
		}elseif (is_string($this -> _table)){
			if(!$this -> table_alias)
			$main = $this -> getParent() -> getTableFrom($this -> _table);
			else {
				$model = $this -> getParent() -> model($this -> _table);
				$main = "`".$model -> getTableName()."` AS `". $this -> table_alias."` ";
			}
		}elseif($this -> _table instanceof MyLike__ORM__MySQL__Select){
			$main = " (".$this -> _table .") AS `".$this -> _table -> getTableAlias()."`";
		}
		$main .= " ".$this -> join();
		foreach($this -> _tables as $table){
			if(is_string($table)){
				$main .= ", ".$this -> getParent() -> getTableFrom($table);
			} elseif(is_object($table)) {
				if($table -> asSubquery()){
					$main .= ", (".$table.") AS `".$table -> getTableAlias()."` ";
				} else {
					$main .= ", ".$table -> getTableFrom();
				}
			} 
		}
		return $main;
	}
}