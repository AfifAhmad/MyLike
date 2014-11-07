<?php 

class MyLike__Core__MVC extends MyLike__ArrayObject__Magic{

	public function getSession(){
		return MyLike__Core__Session::getInstance();
	}

	public function getSingleton(){
		$arguments = func_get_args();
		$arguments = self::processArguments($arguments);
		return MyLike__Singleton__Singleton::getInstance($arguments);
	}
	
	public function getRequest(){
		return MyLike__Network__Request::getInstance();
	}

	public function getPluginData($path){
		$arguments = func_get_args();
		$arguments = self::processArguments($arguments);
		return MyLike__Config__Config::getPluginData($arguments);
	}

	public function localize($args1, $args2){
		return MyLike__Lang__PhpConfig::get( $args1, $args2);
	}
	
	public function attributes($args1, $args2, $file = 'attributes'){
		return MyLike__Config__Config::getPluginData($file, $args1, $args2);
	}

	public function getConfig($path){
		$arguments = func_get_args();
		$arguments = self::processArguments($arguments);
		return MyLike__Config__Config::getData($arguments);
	}

	public function register(){
		$arguments = func_get_args();
		return MyLike__Singleton__Singleton::register($arguments);
	}

	public function getModel(){
		$arguments = func_get_args();
		$arguments = MyLike__ArrayObject__Magic::processArguments($arguments);
		$arguments = $this -> setDbConfig($arguments);
		return MyLike__Datasource__Datasource::getModel($arguments);
	}

	public function getRouter(){
		return MyLike__Singleton__Singleton::register("routing");
	}

	
	public function getAppNamespace(){
		return MyLike__Core__Core::getAppNamespace();
	}
	
	public function getPluginName(){
		return MyLike__Singleton__Singleton::getPluginName();
	}
	
	public function getPluginId(){
		return MyLike__Singleton__Singleton::getPluginId();
	}
	
	
	public function getCSVPluginData(){
		$arguments = func_get_args();
		return MyLike__Config__CSV::getPluginData($arguments);
	}

	public function getCSVConfig(){
		$arguments = func_get_args();
		return MyLike__Config__CSV::getData($arguments);
	}
	
	public function getModule($module_name){
		$arguments = func_get_args();
		$module_name = $arguments[0];
		if(preg_match('#^[a-z][a-z0-9]+$#i', $module_name)){
			if(array_key_exists(1, $arguments)){
				$class = $arguments[1];
			} else {
				$class = $module_name;
			}
			return MyLike__Core__Module::resolve($module_name, $class);
		}
	}
	
	public function getCookie($name){
		return $this -> getRequest() -> getCookie($name);
	}

	public function setCookie(){
		$arguments = func_get_args();
		call_user_func_array(array('MyLike__Core__Cookie','set'), $arguments);
		return $this;
	}
	
	public function setLanguageCookie($lang){
		MyLike__Core__Cookie::setLanguage($lang);
		return $this;
	}
}