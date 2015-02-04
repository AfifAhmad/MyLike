<?php


abstract class MyLike__Core__Module extends MyLike__Core__Logic{

	
	protected $default_view_class = "MyLike__View__Module";
	protected $default_view_directory = "Module";
	protected static $models = array();
	protected static $list = array();

	public function __construct(){
		$this["content_directory"] = $this -> getModuleName();
		$this -> setContent(str_replace('__',DIRECTORY_SEPARATOR, $this -> getLogicName()));
		$this -> execute();
	}
	
	public static function resolve($module, $logic){
		$logic_index = preg_replace('#\\\\/#','__', $logic);
		if(empty(self::$list[$module]) || !array_key_exists($logic_index,self::$list[$module])){
			$class = MyLike__Core__Core::getAppNamespace() . '__Module__' . $module . '__Logic__' . 
				$logic_index;
			if(class_exists($class)){
				self::$list[$module][$logic_index] = new $class;
			} else {
				self::$list[$module][$logic_index] = false;
			}
		} 
		return self::$list[$module][$logic_index];
	}

	public function turn(){
		$arguments = func_get_args();
		array_unshift($arguments, $this -> getModuleName());
		return call_user_func_array(array($this, 'getModule'), $arguments);
	}

	public function getModuleName(){
		preg_match('#^'.$this -> getAppNamespace().'__Module__([a-z0-1]+)__#i', get_class($this), $result);
		return $result[1];
	}

	public function getLogicName(){
		preg_match('#^'.$this -> getAppNamespace().'__Module__(?:[a-z0-1]+)__Logic__(.+)$#i',
			get_class($this), $result);
		return $result[1];
	}

	protected function getModuleConfig(){
		$arguments = func_get_args();
		array_unshift($arguments, 'module/'.$this -> getModuleName());
		return $this -> getConfig($arguments);
	}

	public function getDetailConfig(){
		preg_match('#^'.$this -> getAppNamespace().'__Module__([a-z0-1]+)__Logic__(.*)#i', 
			get_class($this), 
			$result);
		$arguments = func_get_args();
		array_unshift($arguments, 'module/'.$result[1].'/'.preg_replace('#__#', '/', $result[2]));
		return $this -> getConfig($arguments);
	}
	
	protected function execute(){
	}

	public function moduleModel($model){
		$db_engine = $this["db_engine"] ? 
			$this["db_engine"] : MyLike__Config__Config::getPluginData("core","db_engine");
		if(!$db_engine){
			$db_engine = MyLike__Core__Logic::DEFAULT_DB_ENGINE;
		}
		$arguments = func_get_args();
		$arguments[0] = $db_engine. "/".$arguments[0];
		return call_user_func_array(
				array($this, 'moduleGetModel'), $arguments
			);
	}

	public function moduleGetModel(){
		$arguments = func_get_args();
		$arguments = $this -> setDbConfig($arguments);
		array_unshift($arguments, $this -> getModuleName());
		return call_user_func_array(
				array('MyLike__Datasource__Datasource', 'getModuleModel'), $arguments
			);
	}

	protected function setViewClass($class){
		$arguments = func_get_args();
		if(array_key_exists(1, $arguments)) {
			$this["view_class"] = $arguments[0] . "__View__" . preg_replace('#[/\\\\]+#','__',$arguments[1]);
		} else {
			$class = preg_replace('#[\\\\/]#','__',$class);
			$this["view_object"] = null;
			if(!$this["view_class"]){
				$this["view_class"] = null;
			} 
			
			$app_namespace = $this -> getAppNamespace();
			
			$vclass = $app_namespace . "__Module__".$this -> getModuleName() . "__View__" . $class;

			if(class_exists($vclass)){
				$this["view_class"] = $vclass;
			} else {
				$vclass = $app_namespace . "__View__" . $class;
				if(class_exists($vclass)){
					$this["view_class"] = $vclass;
				}else{
					$vclass = "MyLike__View__".$class;
					if(class_exists($vclass)){
						$this["view_class"] = $vclass;
					} else {
						$this -> getViewClass();
					}
				}
			}
		}
		return $this;
	}

}