<?php

class MyLike__Config__Config extends MyLike__ArrayObject__Magic{

	protected static $cache = array();
	protected static $exists = array();
	protected static $last_exists;

    public function offsetUnset($offset) {
		
	}
	
    public function offsetSet($offset, $value) {
	
    }

	public function add($new_value){
		if(is_null($this -> data)){
			return parent::add($new_value);
		}
		return $this;
	}

	public static function getData($path){
		$arguments = func_get_args();
		$arguments = parent::processArguments($arguments);
		$path = array_shift($arguments);
		$path = preg_replace("#[\////]+#",DS, $path);
		if(!array_key_exists($path, self::$cache)){
			$vars = MyLike__Autoload__Autoload::load(MyLike__Autoload__Path::getConfigDir() . $path, false);
			self::$cache[$path] = new self($vars);
			self::$exists[$path] = MyLike__Autoload__Autoload::getLastExists();
		} 
		self::$last_exists = self::$exists[$path];
		if(array_key_exists(0,$arguments)){
			return self::$cache[$path] -> get($arguments);
		} else {
			return self::$cache[$path];
		}
	}
	
	public static function getPluginData($path){
		$arguments = func_get_args();
		$arguments = parent::processArguments($arguments);
		if($plugin_id = MyLike__Singleton__Singleton::getPluginId()){
			$path = array_shift($arguments);
			$object = self::getData("plugins" . DS . $plugin_id . DS . $path);
			if($arguments){
				$value = $object -> get($arguments);
				if($object -> isLastFound()){
					return $value;
				} 
			} elseif(self::$last_exists){
				return $object;
			}
			array_unshift($arguments, $path);
		} 
		return self::getData($arguments);
	}


}