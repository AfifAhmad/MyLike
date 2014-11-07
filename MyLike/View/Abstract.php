<?php

abstract class MyLike__View__Abstract extends MyLike__Core__MVC{


	protected $parent;
	const DEFAULT_LAYOUT = 'default';
	const DEFAULT_TEMPLATE = 'default';
	const LAYOUT_DIRECTORY = "Layout";
	const ELEMENT_DIRECTORY = "Element";

	private static $config;
	
	public function __construct(){
		$this -> init();
	}
	
	protected function init(){
	}
	
	protected function getParent(){
		return $this -> parent;
	}

	public function setParent($parent){
		if(is_null($this -> parent)){
			$this -> parent = $parent;
		}
		return $this;
	}
	
	protected function getVar(){
		if(is_null($this["var"])){
			$this["var"] = array();
		}
		return $this["var"];
	}

	private static function loadConfig(){
		if(is_null(self::$config)){
			$default_config = array(
				'template'		 => self::DEFAULT_TEMPLATE,
				'view_extension' => '.phtml',
				'auto_render'	 => true
			);
			$config = MyLike__Config__Config::getPluginData('core');
			foreach($default_config as $key => $value){
				if($config[$key] || $config -> isLastFound()) {
					self::$config[$key] = $config[$key];
				} else {
					self::$config[$key] = $value;
				}
			}
			self::$config = new MyLike__ArrayObject__Magic(self::$config);
		}
	}

	
	protected function getExtension($explode = false){
		if($this["extension"]){
			$ext = $this["extension"];
		} else {
			$ext = self::getDisplay("view_extension");
		}
		$ext = MyLike__Autoload__Path::getNewExtension($ext);
		if($explode){
			return explode(',', $ext);
		} else {
			return $ext;
		}
	}
	
	protected function getLayoutDirectory(){
		$layout_directory = $this['layout_directory'];
		if(!$layout_directory){
			$layout_directory = self::LAYOUT_DIRECTORY;
		}
		return $layout_directory;
	}
	
	protected function getElementDirectory(){
		$element_directory = $this['element_directory'];
		if(!$element_directory){
			$element_directory = self::ELEMENT_DIRECTORY;
		}
		return $element_directory;
	}

	protected function getLayoutExtension($explode = false){
		if($this["layout_extension"]){
			$ext = $this["layout_extension"];
		}elseif($this["extension"]){
			$ext = $this["extension"];
		} else {
			$ext = self::getDisplay("view_extension");
		}
		$ext = MyLike__Autoload__Path::getNewExtension($ext);
		if($explode){
			return explode(',', $ext);
		} else {
			return $ext;
		}
	}

	public function getLayoutPackage(){
		if($this['layout_package']){
			$package = $this['layout_package'];
		} else {
			$package = $this -> getPluginId();
		}
		return preg_replace('#[\\\\/]+#', '_', $package);
	}
	
	public static function getDisplay($key){
		self::loadConfig();
		return self::$config[$key];
	}


}	