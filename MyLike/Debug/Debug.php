<?php 


class MyLike__Debug__Debug{
	
	private static $_as_string = true;
	private static $_to_die = false;
	
	public static function trace(){
		$backtrace = debug_backtrace();
		
		$arguments = func_get_args();
		
		if(!empty($arguments)){
			ob_start();
			print_r($arguments);
			$buffer = ob_get_clean();
		} else {
			$buffer = "";
		}
		
		if(!self::$_as_string) $buffer = array($buffer);
		
		foreach($backtrace as $data){
			if(array_key_exists('file', $data) && array_key_exists('line', $data)){
				$line = "File: ".$data["file"]." line: ".$data["line"]."\r\n";
				if(self::$_as_string){
					$buffer .= $line;
				} else {
					$buffer[] = $line;
				}
			}
		}
		if(self::$_to_die){
			print_r($buffer);die;
		} else	return $buffer;
	}
	
	public static function memoryUsage() {
		$memory_usage = memory_get_usage();
		$unit=array('b','kb','mb','gb','tb','pb');
		return @round($memory_usage/pow(1024,($i=floor(log($memory_usage,1024)))),2).' '.$unit[$i];
	}
	
	public static function toDie($conf){
		self::$_to_die = $conf;
	}
	
	public static function asString(){
		$arguments = func_get_args();
		if(array_key_exists(0, $arguments)){
			self::$_as_string = $arguments[0];
		} else {
			return self::$_as_string;
		}
	}
}