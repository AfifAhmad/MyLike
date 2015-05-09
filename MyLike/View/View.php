<?php

abstract class MyLike__View__View extends MyLike__View__Abstract{

	private $last_exists;
	
	public function execute(){
		if(is_null($this['base'])){
			$this -> setVar('base', $this -> getRequest() -> base());
		}
		if($this -> getParent() -> autoRender()){
			$content = $this -> load();
			if(strlen($this -> getLayout())){
				$layout = $this -> loadLayout($content);
				if($this -> last_exists){
					$content = $layout;
				}
			}
			return $content;
		} else {
			return '';
		}
	}
	
	public function loadElement($path, $variables = array()){
		MyLike__Autoload__Autoload::tempFileExtension($this -> getExtension());
		$vars = $this -> getVar();
		$vars = array_merge($vars, $variables);
		$buffered = $this -> buffering(
				$path,
				MyLike__Autoload__Path::getDesignDirs($this -> getElementDirectory()),
				$vars
			);
		return $buffered;
	}

	protected function loadLayout($content){
		$vars = $this -> getVar();
		$vars['content'] = $content;
		MyLike__Autoload__Autoload::tempFileExtension($this -> getLayoutExtension());
		$buffered = $this -> buffering(
				$this -> getLayout(),
				MyLike__Autoload__Path::getDesignDirs($this -> getLayoutDirectory()),
				$vars
			);
		return $buffered;
	}

	protected function load(){
		$vars = $this -> getVar();
		MyLike__Autoload__Autoload::tempFileExtension($this -> getExtension());
		$buffered = $this -> buffering(
				$this -> getParent() -> getContentPath(),
				MyLike__Autoload__Path::getDesignDirs($this -> getDirectory()),
				$this -> getVar()
			);
		return $buffered;
	}
	
	public function buffering($file, $paths, $assigned_vars = array()){
		if(!is_array($paths)){
			$paths = array($paths);
		}
		$output = '';
		foreach($paths as $path){
			foreach(MyLike__Autoload__Autoload::fileExtension() as $ext){		
				$full_file_path = MyLike__Autoload__Autoload::implodePath($path, $file . $ext);
				if(file_exists($full_file_path)){
					$output = $this -> _buffering($full_file_path, $assigned_vars);
					$this -> setLastExist(true);
					break;
				} else {
					$this -> setLastExist(false);
				}
			}
		}
		return $output;
	}
	
	private function _buffering($file, $assigned_vars = array()){
		unset($assigned_vars['file']);
		extract($assigned_vars);
		ob_start();
		include $file;
		return ob_get_clean();
	}

	private function setLastExist($param){
		$this -> last_exists = $param;
	}

	public function getBase(){
		return $this -> getRequest() -> base();
	}

}	