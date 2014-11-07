<?php 

abstract class MyLike__Routing__Routing extends MyLike__ArrayObject__Magic{
	
	public function run(){
		MyLike__Singleton__Singleton::register("routing", $this);

		$controller = MyLike__Core__Core::getAppNamespace() . "__Controller__" . $this -> route('controller');
		if(class_exists($controller)){
			$controller_object = new $controller();
		} else {
			if(PHP_SAPI=='cli'){
				$controller = 'MyLike__Controller__Cli';
			} else {
				$controller = 'MyLike__Controller__Notfound';
			}
			$controller_object = new $controller();
		}
		$controller_object -> process();
		$html = $controller_object -> toHtml();
		return $this -> respond($html);
	}

	protected function generate(){
	}

	public function respond($string){
		return $string;
	}

	public function getControllerPrefix(){
		
		$controller_prefix = MyLike__Config__Config::getPluginData("core", "controller_prefix");
		if(!$controller_prefix){
			$controller_prefix = preg_replace('#[\\\\/]#', '__', 
				MyLike__Singleton__Singleton::getPluginId());
			
		}
		return str_replace('#[\\\\/]+#i', '__', $controller_prefix);
	}

	public function route(){
		if(is_null($this['data'])){
			$this -> generate();
		}
		$arguments = func_get_args();
		$args = MyLike__ArrayObject__Magic::processArguments($arguments);
		if(!$args){
			return $this['data'];
		}else{
			return $this['data'] -> get($args);
		}
	}
}