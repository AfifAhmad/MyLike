<?php 

abstract class MyLike__Model__Module__MySQL extends MyLike__Model__MySQL{

	public function moduleModel($model){
		return $this -> moduleGetModel($this -> getDbEngine() . "/" . $model, $this -> config);
	}

	public function moduleGetModel(){
		$arguments = func_get_args();
		$arguments = $this -> setDbConfig($arguments);
		array_unshift($arguments, $this -> getModuleName());
		return call_user_func_array(
				array('MyLike__Datasource__Datasource', 'getModuleModel'), $arguments
			);
	}
	
	public function getDbEngine(){
		return 'MySQL';
	}
	
	public function setDbConfig($arguments){
		if(!array_key_exists(1, $arguments)){
			$arguments[1] = $this -> config;
		}
		return $arguments;
	}
	
	public function getModelConfig(){
		$arguments = func_get_args();
		array_unshift($arguments, "module/".$this -> getModuleName(), 'model', $this -> getDbEngine(), str_replace('__', '/', $this -> getInputtedTableName()));
		return $this -> getConfig($arguments);
	}
	
	public function getModuleName(){
		preg_match('#^'.$this -> getAppNamespace().'__Module__([a-z][0-9a-z]+)#i',
			get_class($this), $match);
		return $match[1];
	}
	
	public function getTableAlias(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			return $this -> moduleModel($args[0]) -> getTableAlias();
		} else {
			if(is_null($this -> table_alias)){
				return $this -> table_name;
			}
			return $this -> table_alias;
		}
	}
}