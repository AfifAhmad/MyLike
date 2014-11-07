<?php

abstract class MyLike__View__View extends MyLike__View__Abstract{

	public function execute(){
		if(is_null($this['base'])){
			$this -> setVar('base', $this -> getRequest() -> base());
		}
		if($this -> getParent() -> autoRender()){
			$content = $this -> load();
			if(strlen($this -> getLayout())){
				$content = $this -> loadLayout($content);
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
		$vars['view'] = $this;
		$buffered = MyLike__Autoload__Autoload::buffering(
				$this -> getLayout(),
				MyLike__Autoload__Path::getDesignDirs($this -> getElementDirectory()),
				$vars
			);
		MyLike__Autoload__Autoload::defaultFileExtension(true);
		return $buffered;
	}

	protected function loadLayout($content){
		$vars = $this -> getVar();
		$vars['content'] = $content;
		$vars['view'] = $this;
		MyLike__Autoload__Autoload::tempFileExtension($this -> getLayoutExtension());
		$buffered = MyLike__Autoload__Autoload::buffering(
				$this -> getLayout(),
				MyLike__Autoload__Path::getDesignDirs($this -> getLayoutDirectory()),
				$vars
			);
		MyLike__Autoload__Autoload::defaultFileExtension(true);
		return $buffered;
	}

	protected function load(){
		$vars = $this -> getVar();
		$vars['view'] = $this;
		MyLike__Autoload__Autoload::tempFileExtension($this -> getExtension());
		$buffered = MyLike__Autoload__Autoload::buffering(
				$this -> getParent() -> getContentPath(),
				MyLike__Autoload__Path::getDesignDirs($this -> getDirectory()),
				$this -> getVar()
			);
		MyLike__Autoload__Autoload::defaultFileExtension(true);
		return $buffered;
	}
	

	public function getBase(){
		return $this -> getRequest() -> base();
	}

}	