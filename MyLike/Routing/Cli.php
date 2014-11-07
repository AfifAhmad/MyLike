<?php

class MyLike__Routing__Cli extends MyLike__Routing__Routing{


	protected function generate(){
		$controller_prefix = $this -> getControllerPrefix();
		$argv = $_SERVER['argv'];
		array_shift($argv);
		$path = null;
		$param = array();
		foreach($argv as $index => $arg){
			if(is_null($path) && preg_match('#^[/\\\\a-z0-9_]+$#i', $arg) && $index == 0){
				$path = preg_replace("#^/+#","",$arg);
			} else {
				preg_match('#^(?P<key>[^=]+)(?:=(?P<value>.*))?$#', $arg, $match);
				if(array_key_exists('value', $match)){
					$param[$match['key']] = $match['value'];
				}
			}
		}
		$this -> data = new MyLike__ArrayObject__Magic(
				array(
					'param' => $param,
					'content_directory' => str_replace('__', DS, $controller_prefix),
					'controller' => $controller_prefix . (!$path ? 
						'__' . preg_replace('#[\\\\/]+#','__', $path) : ""),
					'argv' => $argv,
					'path' => $path
				)
			);
	}

}