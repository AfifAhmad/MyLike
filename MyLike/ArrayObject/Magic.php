<?php

class MyLike__ArrayObject__Magic extends MyLike__ArrayObject__Core 
	implements ArrayAccess, IteratorAggregate, Countable{

	private $_is_last_found;
	private $_serialize_as_json = false;
	protected $data;

	public function __construct(){
		$args = func_get_args();
		if(!empty($args))
		$this -> add($args[0]);
	}

	public function isLastFound(){
		return $this -> _is_last_found;
	}

	public function isEmptyData(){
		return is_null($this -> data);
	}

	public function add($new_value){
		if(self::foreachable($new_value)){
			if(is_null($this -> data)){
				$this -> data = array();
			}
			foreach($new_value as $key => $arg){
				if(is_array($arg)){
					$this->data[$key] = new self($arg);
				}else{ 
					$this->data[$key] = $arg;
				}
			}
		} else {
			$this->data[] = $new_value;
		}
		return $this;
	}

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return (is_null($this -> data)) ? null : array_key_exists($offset, $this -> data);
    }

    public function offsetUnset($offset) {
		$this -> remove($offset);
    }


    public function offsetGet($offset) {
		if(is_null($this -> data) || (!array_key_exists($offset, $this -> data))){
			$this -> _is_last_found = false;
			return null;
		} else{
			$this -> _is_last_found = true;
			return $this -> data[$offset];
		}
    }

    public function getIterator() {
		if(self::foreachable($this -> data))
        return new ArrayIterator($this -> data);
		else return new ArrayIterator(array());
    }

	public function count(){
		return count($this -> data);
	}
	
	public function set(){
		$arguments = func_get_args();
		$indexes = self::processArguments($arguments);
		$new_value = array_pop($indexes);
		$buffer = $this -> data;
		$data = &$buffer;
		foreach($indexes as $index){
			$data = &$data[$index];
		}
		$data = $new_value;
		$this -> data = $buffer;
		return $this;
	}

	public function get(){
		$arguments = func_get_args();
		$indexes = self::processArguments($arguments);
		$data = $this -> data;
		$this -> _is_last_found = true;
		foreach($indexes as $index){
			if(is_array($data)){
				if( array_key_exists($index, $data)){
					$data = $data[$index];
				} else {
					$this -> _is_last_found = false;
					$data  = null;
				}
			} elseif(!is_object($data)) {
				$data = null;
				$this -> _is_last_found = false;
				break;
			} else {
				if(self::indexable($data)) {
					$data = $data[$index];
				} else {
					$data = $data -> getNullValue();
					$this -> _is_last_found = false;
					break;
				}
			}
		}
		return $data;
	}

	public function remove(){
		$arguments = func_get_args();
		$parameters = self::processArguments($arguments);
		$last_key = array_pop($parameters);
		if(empty($parameters)){
			self::removeIndex($this, $last_key);
		} else {
			$next = true;
			$buffer = $this -> data;
			$pointer = &$buffer;
			foreach($parameters as $key){
				if(is_array($pointer) ){
					if(array_key_exists($key,$pointer)){
						$pointer = &$pointer[$key];
					} else {
						$next = false;
						break;
					}
				} elseif(self::indexable($pointer)) {
					$pointer = &$pointer[$key];
				} else {
					$next = false;
					break;
				}
			}
			if($next && self::indexable($pointer)){
				unset($pointer[$last_key]);
				$this -> data = $buffer;
			}
		}
		return $this;
	}
	
	public function exchangeArray($newArray){
		if(is_array($newArray)){
			$this -> data = $newArray;
		} elseif(is_object($newArray)){
			if($newArray instanceof ArrayObject){
				$this -> data = $newArray -> getArrayCopy();
			} elseif($newArray instanceof IteratorAggregate){
				foreach($newArray as $key => $value){
					$this -> data[$key] = $value;
				}
			}
		} else {
			$this -> data = (array) $newArray;
		}
		return $this;
	}
	
	public function serializeAsJSON(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$this -> _serialize_as_json = (boolean) $args[0];
			return $this;
		} else {
			return $this -> _serialize_as_json;
		}
	}
	
	public function __call($method, $value){
		if(preg_match("#^(?P<function>get|remove|set)(?P<property>(?:[A-Z][a-z0-9]*)+)$#",$method,$match)){
			preg_match_all ( "#[A-Z][a-z0-9]*#" , $match["property"], $matches);
			$str = "";
			foreach( $matches[0] as $var){
				$str .= "_".strtolower($var);
			}
			$str = preg_replace("#^_#","",$str);
			array_unshift($value, $str);
			return call_user_func_array(array($this, $match["function"]), $value);
		} else {
			throw new BadMethodCallException ($method . ' of class ' . get_class($this) . " is not exists");
		}
	}
	
	public function __toString(){
		if(is_null($this -> data)){
			return "";
		} else {
			if($this -> serializeAsJSON()){
				return json_encode( $this -> getArrayCopy() );
			} else {
				return serialize( $this -> getArrayCopy() );
			}
		}
	}

	public static function removeIndex($array, $index){
		$buffer = array();
		if(self::foreachable($buffer)){
			foreach($array as $key => $data){
				if($key !== $index)
				$buffer[$key] = $data;
			}
			if(is_array($array)){
				$array = $buffer;
			} else {
				$array -> exchangeArray($buffer);
			}
		}
		return $array;
	}
	
	public function callback($callback){
		switch(gettype($callback)){
			case "closure":
			case "object":
			case "string":
				$this -> data = $callback($this -> data);
				break;
			case "array":
				$this -> data = call_user_func_array($callback, array($this -> data));
		}
		return $this;
	}

	public static function foreachable($array){
		return (is_array($array)||(is_object($array)&&($array instanceof IteratorAggregate)));
	}

	public static function countable($array){
		return (is_array($array)) or (is_object($array) && ($array instanceof Countable));
	}
	
	public static function indexable($array){
		if(is_array($array)){
			return true;
		} elseif(is_object($array)) {
			if($array instanceof self){
				if($array -> isEmptyData()){
					return false;
				} else {
					return true;
				}
			} elseif ($array instanceof ArrayAccess) {
				return true;
			}
		} else {
			return false;
		}
	}
	
	public static function processArguments($arguments){
		if(is_array($arguments) && array_key_exists(0, $arguments) && is_array($arguments[0])){
			$new_arguments = array();
			foreach($arguments[0] as $argument){
				$new_arguments[] = $argument;
			}
			return $new_arguments;
		} else {
			return $arguments;
		}
	}
	
	public static function createNullObject(){
		$object = new self();
		$object -> setNullValue($object);
		return $object;
	}
}