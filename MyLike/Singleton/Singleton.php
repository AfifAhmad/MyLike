<?php 

class MyLike__Singleton__Singleton{

	private static $_objects = array();
	private static $_register;

	public static function getInstance($path){
		$arguments = func_get_args();
		$arguments = MyLike__ArrayObject__Magic::processArguments($arguments);
		$class = array_shift($arguments);
		$class = preg_replace("#[\\\\/]#", "__", $class);
		if(!array_key_exists($class,self::$_objects)){
			$loaded_class = MyLike__Core__Core::getAppNamespace() . '__' . $class;
			if(!class_exists($loaded_class)){
				$loaded_class = 'MyLike__' . $class;
			}
			if($arguments){
				$c = new ReflectionClass($loaded_class);
				self::$_objects[$class] = $c->newInstanceArgs($arguments);
			}else{
				self::$_objects[$class] = new $loaded_class();
			}
		}elseif($arguments && method_exists(self::$_objects[$class], '__construct')	){
			call_user_func_array(array(self::$_objects[$class], '__construct'),$arguments);
		}
		return self::$_objects[$class];
	}


	public static function register(){
		$arguments = func_get_args();
		$arguments = MyLike__ArrayObject__Magic::processArguments($arguments);
		if(count($arguments)>1){
			self::$_register[$arguments[0]] = $arguments[1];
		} else{
			if(is_null(self::$_register)){
				self::$_register = array();
			}
			if(array_key_exists($arguments[0], self::$_register)) {
				return self::$_register[$arguments[0]];
			}
		}
	}
	
	public static function setPluginId($plugin_id){
		self::register("plugin_id", $plugin_id);
		$app_name = MyLike__Config__Config::getPluginData("core") -> get("app_name");
		$default_app_name = MyLike__Config__Config::getData('routes', 'plugins', $plugin_id);
		if(MyLike__ArrayObject__Magic::indexable($default_app_name)&& !empty($default_app_name['name'])){
			$default_app_name = $default_app_name['name'];
		}
		self::register("plugin_name", $app_name ? $app_name : $default_app_name);
	}
	
	public static function getPluginId(){
		return self::register("plugin_id");
	}
	
	public static function getPluginName(){
		return self::register("plugin_name");
	}
}