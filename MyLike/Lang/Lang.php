<?php

abstract class MyLike__Lang__Lang{

	private static $custom;
	
	const DEFAULT_LANG = "en_US";
	
	public static function getCurrent(){
		$lang = null;
		if(!is_null(self::$custom)){
			$lang = self::$custom;
		} else {
			$lang = MyLike__Config__Config::getPluginData("core", "lang");
			if(is_null($lang)){
				$lang = self::DEFAULT_LANG;
			}
		}
		return $lang;
	}

	public static function getFlag(){
		return MyLike__Config__Config::getPluginData("core", "flags", self::getCurrent());
	}
	
	public static function setCustom($custom){
		$flags = MyLike__Config__Config::getPluginData("core", "flags");
		if(MyLike__ArrayObject__Magic::indexable($flags) && $flags[$custom]){
			self::$custom = $custom;
		}
	}
}