<?php

abstract class MyLike__Controller__Controller extends MyLike__Core__Logic{

	protected $default_view_class = "MyLike__View__Controller";
	protected $default_view_directory = "Content";
	protected $helpers = array();
	
	private static $controllers_object = '';
	protected $use_layout_when_ajax = false;

	public function __construct(){
		$content_directory = $this -> getRoute("content_directory");
		if(is_null($content_directory)){
			$content_directory =  $this -> getPluginId();
		}
		$this["content_directory"] = preg_replace("#[/\\\\]+#",'__',$content_directory);
		$this -> before_execute();
	}
	

	protected function skipAction(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$this["skip_action"] = $args[0];
			return $this;
		} else {
			return $this["skip_action"];
		}
	}

	protected function getRoute(){
		$args = func_get_args();
		return $this -> getRouter() -> route($args);
	}

	protected function getParam(){
		$args = func_get_args();
		return $this -> getRouter() -> route($args);
	}

	public function before_execute(){
	}

	public function process(){
		if(!$this -> skipAction()){
			$this -> execute();
		}
		$this -> after_execute();
	}

	public function after_execute(){
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
			$vclass = $this -> getAppNamespace() . "__View__" . $class;

			if(class_exists($vclass)){
				$this["view_class"] = $vclass;
			} else {
				$vclass = "MyLike__View__".$class;
				if(class_exists($vclass)){
					$this["view_class"] = $vclass;
				} else {
					$this -> getViewClass();
				}
			}
		}
		return $this;
	}
	
	public function useLayoutWhenAjax(){
		$arguments = func_get_args();
		if(array_key_exists(0, $arguments)){
			$this -> use_layout_when_ajax = $arguments[0];
			return $this;
		} else {
			return $this -> use_layout_when_ajax;
		}
	}

}