<?php

class MyLike__Network__Uri {

	private $_uri;
	protected $url_array;
	private $_url_has_parsed;

	public function __construct($string_url){
		$this -> _url_has_parsed = false;
		$this -> url_array = array();
		$this -> _uri = $string_url;
	}

	protected function getUri(){
		return $this -> _uri;
	}

	public function toArray(){
		if(!$this -> _url_has_parsed){
			$this -> url_array = parse_url($this -> _uri);
			$path_position = strpos($this -> url_array['path'],'/');
			if($path_position>0 || $path_position===false){
				$this -> url_array['path'] = '/'.$this -> url_array['path'];
			}
			if(!empty($this -> url_array['query'])){
				$query = new MyLike__Network__Query($this -> url_array['query']);
				$this -> url_array['query'] = $query -> toArray();
			} else {
				$this -> url_array['query'] = array();
			}
			$this -> _url_has_parsed = true;
		}
		$arguments = func_get_args();
		$args = MyLike__ArrayObject__Magic::processArguments($arguments);
		if(!empty($args)){
			$url_array = $this -> url_array;
			foreach($args as $arg){
				if(!array_key_exists($arg, $url_array)){
					return null;
				} else {
					$url_array = $url_array[$arg];
				}
			}
			return $url_array;
		}else return $this -> url_array;
	}
}