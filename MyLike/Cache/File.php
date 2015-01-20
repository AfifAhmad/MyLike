<?php

class MyLike__Cache__File {

	private $root;
	private $newcontent;
	private $file;
	protected static $instance;
	private $default_path = true;

	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	protected function __construct(){
	}
	
	
	public function useDefaultPath(){
		$arguments = func_get_args();
		if(array_key_exists(0, $arguments)){
			$this -> default_path = $arguments[0];
			return $this;
		} else {
			return $this -> default_path;
		}
	}
	
	public function set($file, $use_default_path = true){
		$this -> useDefaultPath($use_default_path);
		$file = preg_replace('#[\\\\/]+#', DS, $file);
		$this -> file = $file;
		return $this;
	}

	public function get(){
		if($this -> file){
			if($this -> useDefaultPath()){
				return MyLike__Autoload__Path::getVarsDir()
					. 'temp' . DS . $this -> file;
			} else {
				return $this -> file;
			}
		}
		return null;
	}

	public function save($string){
		if($this -> validateDirectory()){
			file_put_contents($this -> get(),$string);
			return $this;
		}
	}
	
	public function saveAppend($string){
		if($this -> validateDirectory()){
			file_put_contents($this -> get(),$string, FILE_APPEND);
			return $this;
		}
	}

	public function savePrepend($string){
		if($this -> validateDirectory()){
			$handle = fopen($this -> get(),'a+');
			fclose($handle);
			$handle = fopen($this -> get(),'r+');
			$len = strlen($string);
			$final_len = filesize($this -> get()) + $len;
			$cache_old = fread($handle, $len);
			rewind($handle);
			$i = 1;
			while (ftell($handle) < $final_len) {
				fwrite($handle, $string);
				$string = $cache_old;
				$cache_old = fread($handle, $len);
				fseek($handle, $i * $len);
				$i++;
			}
			fclose($handle);
		}
		return $this;
	}

	public function exists(){
		$args = func_get_args();
		if(!$args){
			return file_exists($this -> get());
		} else {
			return file_exists($args[0]);
		}
	}

	protected function validateDirectory(){
		$args = func_get_args();
		if(!$args){
			$directory = dirname ($this -> get());
		} else {
			$directory = dirname ($args[0]);
		}
		if(!is_dir($directory)){
			if(file_exists($directory)){
				return false;
			}
			$this -> validateDirectory($directory);
			if(is_writable(dirname($directory)) ){
				mkdir($directory);
			} else {
				return false;
			}
		}
		return true;
	}
	
	public function render(){
		if($this -> exists()){
			return file_get_contents($this -> get());
		}
		return false;
	}
	
	public function modified(){
		if($this -> exists()){
			return filemtime($this -> get());
		}
		return false;
	}
	
	public function getAge(){
		if(($mod = $this -> modified())){
			return MyLike__Network__Server::getObject() -> getServer("REQUEST_TIME") - $mod;
		}
		return false;
	}
}