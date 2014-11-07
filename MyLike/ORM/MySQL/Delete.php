<?php 

class MyLike__ORM__MySQL__Delete extends MyLike__ORM__MySQL{
	
	public function __toString(){
		return "DELETE `{$this -> getParent() -> getTableAlias()}` FROM {$this -> getTableFrom()} {$this -> conditions()}{$this -> limit()}";
	}
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
	public function query(){
		$query = $this -> getParent() -> getDatasource() -> query((string) $this);
		return $query;
	}
}