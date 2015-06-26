<?php 

class MyLike__Model__MySQL extends MyLike__Model__Model{

	public function select(){
		return new MyLike__ORM__MySQL__Select($this);
	}
	
	public function update(){
		return new MyLike__ORM__MySQL__Update($this);
	}

	public function insert(){
		return new MyLike__ORM__MySQL__Insert($this);
	}
	
	public function replace(){
		$obj = new MyLike__ORM__MySQL__Insert($this);
		return $obj -> replace(true);
	}
	
	public function delete(){
		return new MyLike__ORM__MySQL__Delete($this);
	}

	public function qstr($string){
		return $this -> getDatasource() -> quote($string);
	}
	
	public function lastId(){
		return $this -> getDatasource() -> lastInsertId();
	}
	
	public function lastInsertId(){
		return $this -> getDatasource() -> lastInsertId();
	}
	
	public function getAffectedRows(){
		return $this -> getDatasource() -> getAffectedRow();
	}
	
	public function getById($id){
		return $this -> select() -> where($this -> field_id, $id)
			-> first(true) -> fetch();
	}
	
	public function insertArray($values){
		$isvalid = $this -> validate($values, "insert");
		if($isvalid){
			$obj = $this -> insert();
			$values = $this -> valuesToSave();
			foreach(array_keys($values) as $field){
				$obj -> addField($field);
			}
			$obj -> addValues(array_values($values));
			return $obj -> query();
		} else {
			return false;
		}
	}

	public function getTableFrom(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			return $this -> model($args[0]) -> getTableFrom();
		} else {
			return "`{$this -> getTableName()}` AS `{$this -> getTableAlias()}`";
		}
	}
	
	public function id(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$this -> reset();
			$this['id'] = $args[0];
			return $this;
		} else {
			if(!$this['id']) return 0;
			else return $this['id'];
		}
	}

	public function editArray($values){
		$isvalid = $this -> validate($values, "update");
		if($isvalid && $this -> id()>0){
			$obj = $this -> update();
			foreach($this -> valuesToSave() as $field => $value){
				$obj -> newValue($field, $value);
			}
			$obj -> where($this -> field_id, $this -> id())
				-> query();
			return true;
		} else {
			return false;
		}
	}

	public function getEscapedField($field){
		if(is_string($field) && 
			preg_match('#^(?:(?P<model>[a-z0-9_]+)\.)?(?P<field>[a-z0-9_]+)$#i', $field, $match)){
			if(empty($match['model'])){
				$table_alias = $this -> getTableAlias();
			} else {
				$table_alias = $this -> getTableAlias($match['model']);
			}
			return '`'.$table_alias.'`.`'.$match['field'].'`';
		} else {
			throw new MyLike__Exception__InvalidArgument('Invalid arguments for getEscapedField method, '
				.get_class($this) .' class, argument must be string and only contain alphanumeric and underscore.');
		}
	}
	
	public function getLimit($page, $many, $num){
		$many = (INT) $many;
		$page = (INT) $page;
		if(!$many) $many=20;
		if (!$page) $page=1;
		$pages = ceil($num/$many);
		if($page > $pages){
			$page = $pages;
		}
		$start=($page*$many)-$many;
		
		return array($start, $page, $pages, $many);
	}
}