<?php

class MyLike__Network__Url extends MyLike__ArrayObject__Magic{
	
	public function __construct(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$this -> process($args[0]);
		}
	}
	
	public function process($raw){
		$this -> reset();
		$url = parse_url($raw);
		if(isset($url['query'])){
			$url['query'] = new MyLike__Network__Query($url['query']);
		} else {
			$url['query'] = new MyLike__Network__Query("");
		}
		parent::__construct($url);
	}

	public function isInside(){
		return ( strpos($this -> get('scheme') . '://' . $this -> get('host') . $this -> get('path') , MyLike__Network__Request::getInstance() -> getProtocolHostBase()) === 0);
	}

	public function pathAfterBase(){
		if($this -> isInside()){
			return preg_replace('#^'.MyLike__Network__Request::getInstance() -> base().'#','', $this['path']);
		}else null;
	}
	
	public function test(){
		$args = func_get_args();
		$value = array_pop($args);
		return $value === $this -> get($args);
	}
	
	public function testDomain($domain){
		$origin = $this['host'];
		if(preg_match('#(\.)?(?P<domain>[a-z0-9-]+(?:(?:\.[a-z0-9-]+)+(?:\.[a-z]+)|(?:\.[a-z]+))?)$#i', $domain, $result)){
			if(!empty($result[1])){
				if(preg_match('#^(.+\.)?'.str_replace('.','\.',$result['domain']).'$#',$origin)){
					return true;
				}
			} elseif($origin == $result['domain']){
				return true;
			}
		}
		return false;
	}
}