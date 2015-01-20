<?php

abstract class MyLike__Autoload__Psr4{

	protected static $paths = array();

	public static function addApp($app_namespace, $path){
		self::addPath($app_namespace."__", $path . DIRECTORY_SEPARATOR . $app_namespace, $level = 0, $src = "package");
	}

	public static function addPath($app_namespace, $path, $level = 0, $src = 'src'){
		self::$paths[$app_namespace] = array(
				'path'	=> preg_replace(
						array('#[\\\\/]+#','#[\\\\/]+$#'),
						array(DIRECTORY_SEPARATOR,''), 
						$path),
				'src'	=> $src,
				'level'	=> $level,
			);
	}

	public static function test($class){
		$app_namespace_found = null;
		$path = null;
		foreach(self::$paths as $app_namespace => $config){
			$app_namespace_regex = preg_replace("#\\\\#", "\\\\\\\\", $app_namespace);
			if(preg_match('#'.$app_namespace_regex.'(?P<suffix>.*)#', $class, $match)
				&& ( is_null($app_namespace_found) || strlen($app_namespace_found) < strlen($app_namespace))){
				preg_match('#[^a-z0-9]+$#i', $app_namespace, $match0);
				$dirs = explode($match0[0], $match['suffix']);
				if($config['level'] > 0){
					$level = $config['level'];
					$path = $config['path'];
					while($level>0){
						$dir = array_shift($dirs);
						$path .= DIRECTORY_SEPARATOR . $dir;
						$level--;
					}
				} else {
					$path =  $config['path'];
				}
				if(strlen($config['src'])>0)
				$path .= DIRECTORY_SEPARATOR . $config['src'];
				if(!empty($dirs)){
					$path .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $dirs);
				}
			}
		}
		return $path;
	}

	public static function getAppNamespacePath($app_namespace){
		if(array_key_exists($app_namespace, self::$paths)){
			return self::$paths[$app_namespace]['path'];
		}
	}
}
