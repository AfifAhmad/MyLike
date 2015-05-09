<?php

abstract class MyLike__Core__Logic extends MyLike__Core__LogicView {

	protected $content = false;
	protected $default_view_class;
	protected $default_view_directory;

	public function autoRender(){
		$args = func_get_args();
		if(is_null($this["autorender"])){
			$this["autorender"] = true;
		}
		if(array_key_exists(0, $args)){
			$this["autorender"] = $args[0];
		}else{
			return $this["autorender"];
		}
	}

	public function setViewVar($var = null, $content = null){
		if(parent::foreachable($var)){
			$view_object = $this -> getViewObject();
			foreach($var as $index => $value){
				$view_object -> setVar($index, $value);
			}
		} else {
			$this -> getViewObject() -> setVar($var, $content);
		}
		return $this;
	}

	public function removeViewVar($var){
		$this -> getViewObject() -> removeVar($var);
		return $this;
	}
	
	public function unsetViewVar($var){
		return $this -> removeViewVar($var);
	}

	public function getViewVar(){
		return $this -> getViewObject() -> getVar();
	}
	
	protected function addExtendedContent($name){
		if($this["extended_content"])
		$this["extended_content"] .= $this -> getExtendedContentSeparator() .$name;
		else $this["extended_content"] = $name;
		return $this;
	}
	
	public function getExtendedContentSeparator(){
		return ( !is_null($this["extended_content_separator"]) ? 
			$this["extended_content_separator"] : ".");
	}

	public function getContentPath(){
		$extended_content = $this["extended_content"];
		$content = $this -> getContent();
		if($content && $extended_content){
			$extended_content = (strlen($content)>0?$this -> getExtendedContentSeparator():"").$extended_content;
		}
		$content .= $extended_content;
		return  ((strlen($this["content_directory"])>0) ? 
			($this["content_directory"].((strlen($content)>0) ? DIRECTORY_SEPARATOR : "")) : "") . $content;
	}


	protected function setViewExtension($extension){
		$this -> getViewObject() -> setExtension($extension);
		return $this;
	}

	public function getViewObject(){
		if(!$this["view_object"]){
			$view_class = $this -> getViewClass();
			$this["view_object"] =  new $view_class();
			$this["view_object"] -> setParent($this) -> setDirectory($this -> getViewDirectory());
			if(!is_null($this['layout_directory'])){
				$this["view_object"] -> setLayoutDirectory($this['layout_directory']) ;
			}
			if(!is_null($this['layout_package'])){
				$this["view_object"] -> setLayoutDirectory($this['layout_package']) ;
			}
		}
		return $this["view_object"];
	}
	
	public function setViewDirectory($view_directory){
		$this['view_directory'] = $view_directory;
		if($this["view_object"]){
			$this["view_object"] -> setDirectory($view_directory);
		}
		return $this;
	}
	
	public function setLayoutDirectory($directory){
		$this['layout_directory'] = $directory;
		if($this['view_object']){
			return $this['view_object'] -> setLayoutDirectory($this['layout_directory']) ;
		}
		return $this;
	}
	
	public function setLayoutPackage($package){
		$this['layout_package'] = $package;
		if($this['view_object']){
			return $this['view_object'] -> setLayoutPackage($this['layout_package']) ;
		}
		return $this;
	}

	public function getViewDirectory(){
		if(!$this['view_directory']){
			return $this -> default_view_directory;
		}
		return $this['view_directory'];
	}

	public function getViewClass(){
		if(is_null($this["view_class"])){
			$this["view_class"] = $this -> default_view_class;
		} 
		return $this["view_class"];
	}
	
	public function __toString(){
		return $this -> toHtml();
	}
	
	public function toHtml(){
		if($this -> autoRender())
		return $this -> getViewObject() -> execute();
		else
		return "";
	}

	public function setLayout($layout){
		$this -> getViewObject() -> setLayout($layout);
		return $this;
	}

	public function setLayoutExtension($ext){
		$this -> getViewObject() -> setLayoutExtension($ext);
		return $this;
	}
}