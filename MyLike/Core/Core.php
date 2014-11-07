<?php

class MyLike__Core__Core{


	private static $app_namesapace;
	
	
	public static function getAppNamespace(){
		return self::$app_namesapace;
	}
	

	public static function dispatch($app_namespace){
		self::$app_namesapace = $app_namespace;
		$route_config = MyLike__Config__Config::getData('routes');
		$script_name = $_SERVER["SCRIPT_NAME"];
		
		$plugin_id = null;
		$route_class = null;
		
		
		if(!empty($route_config['list'][$script_name])){
			if(!empty($route_config['list'][$script_name])){
				$plugin_id = $route_config['list'][$script_name];
				if(!empty($route_config['plugins'][$plugin_id]['routing'])){
					$route_class  = $route_config['plugins'][$plugin_id]['routing'];
				}
			}
		}
		if(is_null($plugin_id)){
			$plugin_id = preg_replace(array('#(\.[a-z0-9]+)+$#', '#^[\\\\/]#', '#\\\\#', '#/#'),array('', '', '/', '_'), $script_name);
		}
		if(is_null($route_class)){
			if(PHP_SAPI == 'cli'){
				$route_class = 'Cli';
			} else {
				$route_class = 'Uri';
			}
		}
	
		$timezone = MyLike__Config__Config::getPluginData('core', 'timezone');
		if(!$timezone){
			$timezone = "UTC";
		}
		date_default_timezone_set($timezone);

		if(array_key_exists('lang', $_COOKIE))
		MyLike__Lang__Lang::setCustom($_COOKIE['lang']);
		
		MyLike__Singleton__Singleton::setPluginId($plugin_id);
		
		return self::getRouting($route_class) -> run();
	}

	public static function getRouting($option){
		$class = self::$app_namesapace . "__Routing__" . $option;
		if(class_exists($class)){
			return new $class();
		} else {
			$class = "MyLike__Routing__".$option;
			return new $class();
		}
	}


}