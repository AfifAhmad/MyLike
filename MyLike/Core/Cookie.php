<?php

abstract class MyLike__Core__Cookie{

	const LANGUAGE_AGE =  31622400;//one year
	const LANGUAGE_INDEX = 'lang';
	
	public static function set($index, $value){
		$arguments = func_get_args();
		$index = array_shift($arguments);
		$value = array_shift($arguments);
		$expire   = MyLike__Config__Config::getPluginData('cookie', 'age');
		$path  = MyLike__Config__Config::getPluginData('cookie', 'path');
		if(array_key_exists(0, $arguments)){
			$expire = $arguments[0];
			if(array_key_exists(1, $arguments)){
				$path = $arguments[1];
			}
		}
		if(!$expire){
			$expire = 0;
		} else {
			$expire = time() + $expire;
		}
		if(is_null($path)){
			$path = '/';
		}
		setcookie($index, $value, $expire, $path);
	}
	
	public static function setLanguage($language){
		if(MyLike__Config__Config::getPluginData("core", "langs", $language)){
			$expire = MyLike__Config__Config::getPluginData('cookie', 'language_expire');
			
			if(is_null($expire)){
				$expire = self::LANGUAGE_AGE;
			}
			$name = MyLike__Config__Config::getPluginData('cookie', 'language_index');
			if(!is_string($name)&& strlen($name)==0){
				$name = self::LANGUAGE_INDEX;
			}
			self::set($name, $language, $expire);
		}
	}
	
}
