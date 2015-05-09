<?php 

class MyLike__Autoload__Autoload{

	private static  $_reg_class = array();
	private static  $_config_error = array();
	private static  $_temporary_include_path;
	private static  $_default_include_path;
	private static  $_default_file_extension;
	private static  $_temp_file_extension;
	private static  $_last_exists;

	private static $config = array();

	public static function handler($class){
		$to_normal_ext_path = false;
		$loaded = false;
		if(array_key_exists($class, self::$_reg_class)){
			if(is_string(self::$_reg_class[$class])){
				$class_file = self::$_reg_class[$class];
			}elseif(is_array(self::$_reg_class[$class])){
				if(array_key_exists("ext",self::$_reg_class[$class])){
					$to_normal_ext_path = true;
					self::tempFileExtension(self::$_reg_class[$class]["ext"]);
				}
				if(array_key_exists("file",self::$_reg_class[$class])){
					$class_file = self::$_reg_class[$class]["file"];
				} else {
					$class_file = $class;
				}
				if(array_key_exists("path",self::$_reg_class[$class])){
					$class_file = self::implodePath(self::$_reg_class[$class]["path"],
								$class_file);
				}
			}
		} else {
			if(class_exists('MyLike__Autoload__Psr4', false)){
				$classfile = MyLike__Autoload__Psr4::test($class);
				self::load($classfile, false);
			}
			if(!self::getLastExists()){
				preg_match('#^[a-z0-9]+#i', $class, $match);
				
				if(!array_key_exists($match[0], self::$config)){
					if($match[0] == 'MyLike'){
						$autoload_config = array(
								'path' => dirname(dirname(dirname(__FILE__))),
								'autoload_standard' => 'mylike',
							);
					} else{
						$autoload_config = array(
								'path' => '',
								'autoload_standard' => 'psr-0',
							);
					}
					self::$config[$match[0]] = $autoload_config;
				} else {
					$autoload_config = self::$config[$match[0]];
				}
				switch ($autoload_config['autoload_standard']) {
					case 'mylike':
						$classfile = preg_replace("#__#", DIRECTORY_SEPARATOR , $class);
						break;
					case 'psr-0':
						$classfile = preg_replace("#_|\\\\#", DIRECTORY_SEPARATOR , $class);
						break;
					case 'adodb':
						$classfile = preg_replace("#_#", '-' , $class.'.inc');
						break;
				}
				if(!empty($autoload_config['ext'])){
					$to_normal_ext_path = true;
					self::tempFileExtension($autoload_config['ext']);
				}
				$class_file = self::implodePath( $autoload_config['path'],	$classfile);
			} else {
				$loaded = true;
			}
		}
		if(!$loaded)
		self::load($class_file, true);
		
		if($to_normal_ext_path){
			self::defaultFileExtension(true);
		}
	}
	
	public static function getVendorPath($vendor){
		if(array_key_exists($vendor, self::$config)){
			return self::$config[$vendor]['path'];
		} else {
			return '';
		}
	}
	
	public static function implodePath($path, $file_name){
		$path = preg_replace('#[\\\\/]+$#','', $path);
		return (strlen($path) > 0 ? $path . DIRECTORY_SEPARATOR : "") .  
			preg_replace('#^[\\\\/]+#','', $file_name);
	}

	public static function setConfig($vendor_name, $path, $autoload_standard = 'psr-0', $ext = false){
		self::$config[$vendor_name] = array(
											'path' => $path,
											'autoload_standard' => $autoload_standard,
											'ext' => $ext,
										);
	}

	public static function load($file, $use_include_path = false){
		$vars = array();
		foreach(self::fileExtension() as $ext){	
            if(file_exists($file . $ext)){
				$vars = self::_load($file . $ext);
				self::setLastExist(true);
				break;
			} else {
				self::setLastExist(false);
			}
		}
		if((!self::getLastExists()) && $use_include_path){
			$paths = explode(PATH_SEPARATOR, get_include_path());
			foreach($paths as $path){
				foreach(self::fileExtension() as $ext){		
					$full_file_path = self::implodePath($path, $file . $ext);
					if(file_exists($full_file_path)){
						$vars = self::_load($full_file_path);
						self::setLastExist(true);
						break;
					} else {
						self::setLastExist(false);
					}
				}
			}
		}
		return $vars;
	}
	
	private static function _load($file){
		require_once $file;
		return get_defined_vars();
	}
	
	public static function buffering($file, $paths, $assigned_vars = array()){
		if(!is_array($paths)){
			$paths = array($paths);
		}
		$output = '';
		foreach($paths as $path){
			foreach(self::fileExtension() as $ext){		
				$full_file_path = self::implodePath($path, $file . $ext);
				if(file_exists($full_file_path)){
					$output = self::_buffering($full_file_path, $assigned_vars);
					self::setLastExist(true);
					break;
				} else {
					self::setLastExist(false);
				}
			}
		}
		return $output;
	}
	
	private static function _buffering($file, $assigned_vars = array()){
		unset($assigned_vars['file']);
		extract($assigned_vars);
		ob_start();
		include $file;
		return ob_get_clean();
	}

	public static function fileExtension(){
		$fileExt = self::tempFileExtension();
		if(strlen($fileExt)==0){
			$fileExt = self::defaultFileExtension();
		} else {
			self::defaultFileExtension(true);
		}
		return preg_split('#,\s*#',$fileExt);
	}

	public static function tempFileExtension(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			self::$_temp_file_extension = $args[0];
		} else {
			return self::$_temp_file_extension;
		}
	}

	public static function defaultFileExtension($toDefault = false){
		if(is_null(self::$_default_file_extension)){
			$exts =	spl_autoload_extensions();
			if(strlen($exts)==0){
				$exts = ".php";
			}
			self::$_default_file_extension = $exts;
		}
		if($toDefault === true){
			spl_autoload_extensions(self::$_default_file_extension);
			self::$_temp_file_extension = null;
		}
		return self::$_default_file_extension;
	}


	protected static function setLastExist($value){
		self::$_last_exists = (boolean) $value;
	}
	
	public static function getLastExists(){
		return self::$_last_exists;
	}
	
	public static function register ($class, $file){
		self::$_reg_class[$class] = $file;
	}

	public static function unregister ($class){
		$_reg_class = self::$_reg_class[$class];
		unset($_reg_class[$class]);
		self::$_reg_class = $_reg_class;
	}
}