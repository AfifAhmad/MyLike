<?php

/*
justin was here :D
*/

class MyLike__Network__HttpFsockopen__Cookie{

	protected $raw;
	protected $header;
	protected $arr;

	public function __construct($raw){
		$this -> raw = $raw;
		$this -> header = null;
		$this -> arr = null;
	}
	public function asHeader(){
		if(is_null($this -> header)){
			if(!empty($this -> raw)){
				if(is_array($this -> raw)){
					$header_cookie = '';
					foreach($this -> raw as $cookie){
						$header_cookie .= $this -> processCookie($cookie);
					}
				}else{
					$header_cookie = $this -> processCookie($this -> raw);
				}
				$this -> header = $header_cookie;
			}else {
				$this -> header = '';
			}
		}
		return $this -> header;
	}
	
	public function combine($object){
		$current = $this -> asArray();
		$other = $object -> asArray();
		foreach($other as $key => $value ){
			$current[$key] = $value;
		}
		$new = array();
		foreach($current as $key => $value ){
			$current[] = $key."=".$value.";";
		}
		return new HttpFsockopen_Cookie($current);
	}
	
	public function asArray(){
		if(is_null($this -> arr)){
			$header = $this -> asHeader();
			if($header){
				$cookies = preg_split('#\s*;\s*#i',$header);
				$buffer = array();
				foreach($cookies  as $line){
					if(empty($line)) continue;
					preg_match('/^\s*([^=]+)\s*=\s*(.*)\s*$/',$line, $output);
					$buffer[$output[1]] = $output[2];
				}
				$this -> arr = $buffer;
			}else 
			$this -> arr = array();
		}
		return $this -> arr;
	}
	
	private function processCookie($cookie){
		
		if(preg_match('#^([^;]+);#i', $cookie, $result)){
			return $result[1].'; ';
		}
	}
}