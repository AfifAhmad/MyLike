<?php

class MyLike__Autoload__Path{

	
	public final static function getVarsDir(){
		$app_dir = self::getAppDir();
		if(!preg_match('#[\\\\/]$#', $app_dir)){
			return $app_dir . "_vars" . DS;
		} else {
			return $app_dir . "vars" . DS;
		}
	}

	public final static function getDesignDir(){
		$app_dir = self::getAppDir();
		if(!preg_match('#[\\\\/]$#', $app_dir)){
			return $app_dir . "_design" . DS;
		} else {
			return $app_dir . "design" . DS;
		}
	}

	public final static function getConfigDir(){
		$app_dir = self::getAppDir();
		if(!preg_match('#[\\\\/]$#', $app_dir)){
			return $app_dir . "_config" . DS;
		} else {
			return $app_dir . "config" . DS;
		}
	}

	public final static function getSessionDir(){
		return self::getVarsDir() . 'session' . DS;
	}
	
	public final static function getNewExtension($ext){
		$ext = preg_replace('#\.php(,)|\.php$#', $ext.'\1',
			MyLike__Autoload__Autoload::defaultFileExtension());
		return $ext;
	}

	public final static function getDataDir(){
		return self::getVarsDir() . 'data' . DS;
	}
	
	public final static function getAppDir(){
		$app_namespace = MyLike__Core__Core::getAppNamespace();
		$path = MyLike__Autoload__Psr4::getAppNamespacePath($app_namespace  . '__');
		if($path){
			return $path . DIRECTORY_SEPARATOR;
		} else {
			return MyLike__Autoload__Autoload::getVendorPath($app_namespace) . DS .	$app_namespace;
		}
	}

	public final static function getCoreDir(){
		return dirname( dirname( dirname(__FILE__)) ) . DS;
	}
	
	public final static function getDesignDirs($directory){
		$template = MyLike__View__Abstract::getDisplay("template");
		$design_dir = self::getDesignDir();
		if(is_string($template)){
			if($template != MyLike__View__Abstract::DEFAULT_TEMPLATE){
				$include_paths = array($design_dir . $template . DS . $directory );
			} else {
				$include_paths = array() ;
			}
			$include_paths[] = $design_dir . MyLike__View__Abstract::DEFAULT_TEMPLATE . DS . $directory;
			return $include_paths;
		} elseif(MyLike__ArrayObject__Magic::indexable($template)){
			if(is_object($template)){
				$templates = $template -> toArray();
			} else {
				$templates = $template;
			}
			if(!in_array(MyLike__View__Abstract::DEFAULT_TEMPLATE, $templates)){
				$templates[] = MyLike__View__Abstract::DEFAULT_TEMPLATE;
			}
			$template = array_shift($templates);
			$include_path = array($design_dir . $template . DS . $directory);
			foreach($templates as $template){
				$include_path[] = $design_dir . $template . DS . $directory ;
			}
			return $include_path;
		} else {
			die('Error template config');
		}
	}
	
}