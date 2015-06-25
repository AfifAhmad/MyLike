<?php 


class MyLike__Model__Model extends MyLike__Core__MVC{

	protected $inputted_table_name;
	protected $table_name;
	protected $default_table_name;
	protected $table_alias;
	protected $table_prefix;
	protected $config;
	protected $db_conf;
	protected $validation = array();
	protected $insert_validation = array();
	protected $update_validation = array();
	protected $field_id = "id";
	
	protected $use_validator = true;
	
	public function __construct($db_conf, $table_name){
		$this -> config = $db_conf;
		$this -> inputted_table_name = $table_name;
		if($this -> table_name === null){
			$this -> table_name = $table_name;
		}
		$this -> default_table_name = $this -> table_name;
		$this -> init();
	}

	protected function init(){
	}
	
	protected function getInputtedTableName(){
		return $this -> inputted_table_name;
	}
	
	public function useValidator(){
		$arguments = func_get_args();
		if(array_key_exists(0, $arguments)){
			$this -> use_validator = $arguments[0];
			return $this;
		} else {
			return $this -> use_validator;
		}
	}
	
	public function validate($values, $method){
		$values = $this -> mappingField($values);
		if($this -> useValidator()){
			$output = array();
			$error = array();
			$method = $method."_validation";
			$validation = array_merge($this -> validation, $this -> $method);
			foreach($validation as $field => $callback){
				if(array_key_exists($field, $values)
					&&!is_object($values[$field])
					&&!is_array($values[$field])){
					$values[$field] = trim((string)$values[$field]);
					if(is_null($callback)||is_bool($callback)){
						$output[$field] = $values[$field];
					} else{
						if(is_array($callback)){
							$args = array($values[$field]);
							if(is_array($callback) && count($callback) > 2){
								$args = array_merge($args, array_slice($callback, 2));
								$callback = array_slice($callback, 0, 2);
							}
							if(array_key_exists(1,$callback)&&is_null($callback[1])){
								$callback = $callback[0];
							}
						} else {
							$args = array($values[$field]);
						}
						if(!call_user_func_array($callback, $args)){
							$error[] = $field;
						} else {
							$output[$field] = $values[$field];
						}
					}
				} else {
					$error[] = $field;
				}
			}
			if(empty($error)){
				$this["values_to_save"] = $output;
				return true;
			} else{
				$this["error_fields"] = $error;
				return false;
			}
		} else {
			$this["values_to_save"] = $values;
			return true;
		}
	}

	protected function valuesToSave(){
		$fields = $this["values_to_save"];
		unset($this["values_to_save"]);
		return $fields;
	}

	protected function mappingField($values){
		$rewrite = $this -> getModelConfig("rewrite");
		if($rewrite){
			$rewrite = $rewrite -> toArray();
			$newValues = array();
			foreach($values as $key => $value){
				if(array_key_exists($key,$rewrite))
				$newValues[$rewrite[$key]] = $value;
				else $newValues[$key] = $value;
			}
		} else {
			$newValues = $values;
		}
		return $newValues;
	}

	public function getErrorField($humanize = false){
		$error_fields = array();
		if(!is_null($this['error_fields'])){
			$rewrite = $this -> getModelConfig("rewrite");
			if(!is_null($rewrite)){
				$rewrite = array_flip($rewrite -> toArray());
			} else {
				$rewrite = array();
			}
			foreach($this['error_fields'] as $field){
				if(array_key_exists($field, $rewrite)){
					$error_fields[] = $rewrite[$field];
				} else {
					$error_fields[] = $field;
				}
			}
			if(!$humanize){
				return $error_fields; 
			} elseif($humanize==1) {
				$error_readable = array();
				foreach($error_fields as $field){
					$newcaption = $this -> getModelConfig(
							'humanize', 
							MyLike__Lang__Lang::getCurrent(),
							$field
						);
					if($newcaption){
						$error_readable[] = $newcaption;
					}
				}
				$last_field = array_pop($error_readable);
				$return = implode(', ', $error_readable);
				if($error_readable){
					$return .= ', '. $this -> localize('caption','and') .' ' . $last_field;
				} else {
					$return = $last_field;
				}
				return $return;
			} elseif($humanize==2) {
				$error_readable = array();
				foreach($error_fields as $field){
					$newcaption = $this -> getModelConfig(
							'humanize_sentence', 
							MyLike__Lang__Lang::getCurrent(),
							$field
						);
					if($newcaption){
						$error_readable[] = $newcaption;
					} else {
						$error_readable[] = $field;
					}
				}
				return $error_readable;
			}
		} else {
			if(!$humanize || $humanize==2){
				return $error_fields; 
			} elseif($humanize==1) {
				return "";
			}
		}
	}

	public function getErrorMessage(){
		$message = $this["error_message"];
		unset($this["error_message"]);
		return $message;
	}
	
	protected function setErrorMessage($message){
		$this["error_message"] = $message;
		return $this;
	}
	
	public function getModelConfig(){
		$arguments = func_get_args();
		array_unshift($arguments, "model/". $this -> getDbEngine()."/".$this -> inputted_table_name);
		return call_user_func_array(array($this, 'getConfig'), $arguments);
	}
	

	public function setTablePrefix($prefix){
		$this -> table_prefix = $prefix;
		return $this;
	}

	public function getTablePrefix(){
		if(!is_null($this -> table_prefix)) return $this -> table_prefix;
		else return $this -> getDatasource() -> getTablePrefix();
	}

	public function getTableName(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			return $this -> model($args[0]) -> getTableName();
		} else {
			return $this -> getTablePrefix() . $this -> table_name;
		}
	}
	
	protected function setTableName($table_name){
		if($table_name){
			$this -> table_name = $table_name;
		} else {
			$this -> table_name = $this -> default_table_name;
		}
		return $this;
	}

	public function setTableAlias($table_alias){
		$this -> table_alias = $table_alias;
		return $this;
	}

	public function getTableAlias(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			return $this -> model($args[0]) -> getTableAlias();
		} else {
			if(is_null($this -> table_alias)){
				return $this -> table_name;
			}
			return $this -> table_alias;
		}
	}

	public function getDatasource(){
		return MyLike__Datasource__Datasource::get($this -> getDbEngine(), $this -> config);
	}

	public function model($alias){
		$arguments = func_get_args();
		$arguments = $this -> setDbConfig($arguments);
		$arguments[0] = $this -> getDbEngine()."\\".$arguments[0];
		return $this -> getModel($arguments[0], $arguments[1]);
	}
	
	public function setDbConfig($arguments){
		if(!array_key_exists(1, $arguments)){
			$arguments[1] = $this -> config;
		}
		return $arguments;
	}
	
	public function getDbEngine(){
		preg_match('#^(?:MyLike|'.$this -> getAppNamespace().')__(?:Module__[a-z0-9A-Z]+__)?Model__([a-z0-9A-Z]+)#',
			get_class($this),$rslt);
		return $rslt[1];
	}
	
}