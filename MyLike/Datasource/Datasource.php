<?php 


class MyLike__Datasource__Datasource {
	
	const DEFAULT_DB_CONFIG = "DEFAULT";
	protected static $models;
	protected static $module_models;
	protected static $list;

	public static function getModuleModel($module_name, $model, $db_config = null){
		$model = preg_replace('#[\\\\/]+#','__', $model);
		$model = explode('__', $model);
		$db_engine = array_shift($model);
		if($model){
			if(!$db_config){
				$db_config = MyLike__Config__Config::getPluginData("core","db_config");
				if(!$db_config){
					$db_config = self::DEFAULT_DB_CONFIG;
				}
			} 
			$model = implode('__', $model);
			$app_namespace = MyLike__Core__Core::getAppNamespace();
			$model_class = $app_namespace . '__Module__' .  $module_name 
				. '__Model__' . $db_engine . '__' . $model;
			if(class_exists($model_class)){
				if(empty(self::$module_models[$module_name][$db_engine][$model][$db_config])){
					self::$module_models[$module_name][$db_engine][$model][$db_config] = new $model_class($db_config, $model);
				}
				return self::$module_models[$module_name][$db_engine][$model][$db_config];
			} else {
				return self::getModel($db_engine.'/'.$model, $db_config);
			}
		} else {
			return null;
		}
	}

	public static function getModel($model, $dbconf = null){
		if(is_null(self::$models)){
			self::$models = new MyLike__ArrayObject__Magic();
		}
		$arguments = func_get_args();
		$arguments = MyLike__ArrayObject__Magic::processArguments($arguments);
		$model = array_shift($arguments);
		$model = preg_replace("#[\\\\/]#", "__", $model);
		if(empty($arguments) || $arguments[0] === null){
			$dbconf = MyLike__Config__Config::getPluginData("core","db_config");
			if(!$dbconf){
				$dbconf = self::DEFAULT_DB_CONFIG;
			}
		} else {
			$dbconf = $arguments[0];
		}
		if(self::$models[$model][$dbconf] === false){
			return self::$models[$model][$dbconf];
		}elseif(is_null(self::$models[$model][$dbconf])){
			if(preg_match("#([^_]+)__(.+)$#",$model, $result)){
				$class = MyLike__Core__Core::getAppNamespace() . "__Model__". $result[1]. "__" . $result[2];
				if(class_exists($class)){
					self::$models -> set($model, $dbconf, new  $class( $dbconf, $result[2]));
				} else {
					$class = "MyLike__Model__".$result[1];
					if(class_exists($class)){
						self::$models -> set($model, $dbconf, new  $class( $dbconf, $result[2]));
					} else {
						self::$models[$model][$dbconf] = false;
					}
				}
			}
		}
		return self::$models[$model][$dbconf];
	}
	
	public static function get($db_driver, $config){
		if(is_null(self::$list)){
			self::$list = array();
		}
		if(!array_key_exists($db_driver, self::$list)
			||!array_key_exists($config, self::$list[$db_driver])){
			$class = MyLike__Core__Core::getAppNamespace() . "__Datasource__".$db_driver;
			if(!class_exists($class)){
				$class = "MyLike__Datasource__".$db_driver;
			}
			self::$list[$db_driver][$config] = new $class($config);
		}
		return self::$list[$db_driver][$config];
	}
}