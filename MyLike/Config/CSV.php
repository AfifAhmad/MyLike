<?php

class MyLike__Config__CSV  extends MyLike__ArrayObject__Magic{

	protected static $cache = array();

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
			$data_dir = MyLike__Autoload__Path::getDataDir();
			$new_extension = explode(',', MyLike__Autoload__Path::getNewExtension('.csv'));
			$found = false;
			foreach($new_extension as $ext){
				$file = $data_dir . $path . $ext;
				if(file_exists($file)){
					$found = true;
					break;
				}
			}
			if($found){
				$file = fopen($file, 'r');
				$flex = true;
				$array = array();
				$names = array();
				while (($line = fgetcsv($file)) !== FALSE) {
					if($line){
						$name = array_shift($line);
						if($flex){
							if(!$line){
								break;
							}
							$indexes = array();
							foreach($line as $index){
								if(!array_key_exists($index, $array)){
									$array[$index] = array();
									$indexes[] = $index;
								}
							}
							$flex = false;
						} else {
							if(!array_key_exists($name, $names)){
								foreach($indexes as $i => $index){
									if(array_key_exists($i, $line)){
										$array[$index][$name] = $line[$i];
									} else {
										$array[$index][$name] = null;
									}
								}
								$names[$name] = true;
							}
						}
					}
				}
			} else {
				$array = array();
			}
			self::$cache[$path] = new self($array);
		}
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
			} elseif(MyLike__Autoload__Autoload::getLastExists()){
				return $object;
			}
			array_unshift($arguments, $path);
		} 
		return self::getData($arguments);
	}


}