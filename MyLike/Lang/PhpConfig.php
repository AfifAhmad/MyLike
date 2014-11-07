<?php

class MyLike__Lang__PhpConfig extends MyLike__Lang__Lang{
	
	public static function get($args0, $args1){
		return MyLike__Config__Config::getPluginData(
					'locale/'.$args0, 
					MyLike__Lang__Lang::getCurrent(), 
					$args1
				);
	}

}