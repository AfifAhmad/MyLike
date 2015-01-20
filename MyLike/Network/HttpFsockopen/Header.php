<?php

/*
justin was here :D
*/

class MyLike__Network__HttpFsockopen__Header{

	protected $data = array();
	protected $cookie;

	public function __construct($data){
		if(is_string($data)){
			$output = explode("\r\n",$data);
		} else {
			$output = $data;
		}
		$header = array();
		if(empty($output)){
			$output=array();
		}
		foreach($output as $line){
			if(empty($line)){
				break;
			}elseif(preg_match("#^((?P<key>[^\:]+)\s*:)?\s*(?P<content>.+)$#i", $line, $result)){
				if(!empty($result['key'])){
					if(empty($header[$result['key']])){
						$header[$result['key']] = $result['content'];
					}else{
						if(!is_array($header[$result['key']])){
							$buffer = $header[$result['key']];
							unset($header[$result['key']]);
							$header[$result['key']] = array($buffer, $result['content']);
							
						}else{
							$header[$result['key']][] = $result['content'];
						}
					}
				}else $header[] = $result['content'];
			}
		}
		$this -> data = $header;
	}
	
	public function test(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			if(array_key_exists($args[0], $this -> data)){
				if(array_key_exists(1, $args)){
					return $this -> data[$args[0]] == $args[1];
				} else return true;
			}else return false;
		}
	}

	public function get(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			if(array_key_exists($args[0], $this -> data)){
				return $this -> data[$args[0]];
			}else return null;
		}else return $this -> data;
	}
	
	public function getHttpCode(){
		$httpcode = $this -> get(0);
		if(preg_match("#([0-9]{3,3})\s+#",$httpcode,$match)){
			return $match[1];
		} else {
			return 0;
		}
	}

	public function getCookie(){
		if(!$this -> cookie){
			$this -> cookie = new HttpFsockopen_Cookie(
				$this -> get('Set-Cookie')
			);
		}
		return $this -> cookie;
	}
}
