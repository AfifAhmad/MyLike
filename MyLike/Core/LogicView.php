<?php

abstract class MyLike__Core__LogicView extends MyLike__Core__MVC {

	
	const DEFAULT_DB_ENGINE = "MySQL";
	
	protected function model(){
		$db_engine = $this["db_engine"] ? $this["db_engine"] : MyLike__Config__Config::getPluginData("core","db_engine");
		if(!$db_engine){
			$db_engine = self::DEFAULT_DB_ENGINE;
		}
		$arguments = func_get_args();
		$arguments[0] = $db_engine. "\\".$arguments[0];
		return $this -> getModel($arguments);
	}

	public function setDbConfig($arguments){
		if(is_string($arguments)){
			$this['db_config'] = $arguments;
			return $this;
		}
		if(!array_key_exists(1, $arguments) && $this["db_config"]){
			$arguments[1] = $this["db_config"];
		}
		return $arguments;
	}
}