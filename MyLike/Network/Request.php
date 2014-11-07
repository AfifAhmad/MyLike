<?php

class MyLike__Network__Request extends MyLike__ArrayObject__Magic{

	protected static $object;
	
	protected $referrer;
	protected $uri_object;
	protected $raw_post;
	protected $array_post;
	
	public function referrer(){
		if(is_null($this -> referrer)){
			if($this['HTTP_REFERER']){
				$this -> referrer = new MyLike__Network__Url($this['HTTP_REFERER']);
			}else{
				$this -> referrer = new MyLike__Network__Url();
			}
		}
		return $this -> referrer;
	}
	
	public function getRawPost(){
		if(is_null($this -> raw_post)){
			$this -> raw_post = file_get_contents("php://input");
		}
		return $this -> raw_post;
	}
	
	public function getPost(){	
		if(is_null($this -> array_post)){
			if(!$this -> get('CONTENT_TYPE')){
				$data = new MyLike__Network__Query($this -> getRawPost());
				$data = $data -> toArray();
			} else {
				$data = $_POST;
			}
			$this -> array_post = new MyLike__ArrayObject__Magic($data);
		}
		$arguments = func_get_args();
		$args = MyLike__ArrayObject__Magic::processArguments($arguments);
		if(!$args){
			return $this -> array_post;
		}else{
			return $this -> array_post -> get($args);
		}
	}
	
	public function getDate($format){
		return date($format, $this["REQUEST_TIME"]);
	}
	
	public function isReferrerInside() {
		return ( strpos($this['HTTP_REFERER'] , $this -> getProtocolHostBase()) === 0);
	}
	
	public function getProtocolHostBase() {
		return $this -> getHttpProtocol() ."://". $this -> getHttpHost()  . $this -> base();
	}
	
	public function isPost(){
		if($this['REQUEST_METHOD'] == 'POST'){
			return true;
		}
	}
	
	public function scriptName(){
		return $this['SCRIPT_NAME'];
	}
	
	public function getHttpHost() {
		if ($this['HTTP_X_FORWARDED_HOST']) {
			return $this['HTTP_X_FORWARDED_HOST'];
		}
		return $this['HTTP_HOST'];
	}
	
	public function getSuffixHost(){
		preg_match('#[0-9a-z\-]{4,}(?:\.[a-z]{2,3}){0,2}$#', $this -> getHttpHost(), $match);
		return $match[0];
	}
	
	public function getIp() {
		$ipaddress = '';
		if ($this['HTTP_CLIENT_IP'])
		$ipaddress = $this['HTTP_CLIENT_IP'];
		elseif($this['HTTP_X_FORWARDED_FOR'])
		$ipaddress = $this['HTTP_X_FORWARDED_FOR'];
		elseif($this['HTTP_X_FORWARDED'])
		$ipaddress = $this['HTTP_X_FORWARDED'];
		elseif($this['HTTP_FORWARDED_FOR'])
		$ipaddress =$this['HTTP_FORWARDED_FOR'];
		elseif($this['HTTP_FORWARDED'])
		$ipaddress = $this['HTTP_FORWARDED'];
		elseif($this['REMOTE_ADDR'])
		$ipaddress = $this['REMOTE_ADDR'];
		else
		$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	
	public function getHttpProtocol() {
		if ($this['REQUEST_SCHEME']) {
			return $this['REQUEST_SCHEME'];
		}elseif ($this['HTTP_X_FORWARDED_PROTO']) {
			if ($this['HTTP_X_FORWARDED_PROTO'] === 'https') {
				return 'https';
			}else return 'http';
		}elseif ($this['HTTPS'] === 'on' || $this['HTTPS'] == 1) {
			return 'https';
		}else{
			return 'http';
		}
	}

	public function getProtocolHost(){
		return $this -> getHttpProtocol() ."://". $this -> getHttpHost();
	}

	public function getPort(){
		return	($this['SERVER_PORT'] &&
		(($this -> getHttpProtocol() === 'http' && $this['SERVER_PORT'] !== 80) ||
		($this -> getHttpProtocol() === 'https' && $this['SERVER_PORT'] !== 443)))
		? ':' . $this['SERVER_PORT'] : '';
	}
	
	public function documentRoot(){
		return $this['CONTEXT_DOCUMENT_ROOT'];
	}
	
	public function isAjax(){
		if(!is_null($this['HTTP_X_REQUESTED_WITH'])
			&& strtolower($this['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			return true;
		}else{
			return false;
		}
	}
	
	public function mobileAgent(){
		return $this['HTTP_X_OPERAMINI_PHONE'];
	}

	public function getCurrentUrl(){
		return $this->getHttpProtocol(). '://' .
				$this -> getHttpHost(). 
				$this -> getPort() . $this['REQUEST_URI'];
	}

	public function base(){
		if(strpos($this['REQUEST_URI'], $this['SCRIPT_NAME']) === 0){
			return $this['SCRIPT_NAME'].'/';
		} else {
			preg_match('#^((.+)/)[^/]+$#', $this['SCRIPT_NAME'], $match);
			return $match[1];
		}
	}

	public function getServer($key, $ifnull = null){
		return (!is_null($this[$key]))? $this[$key] : $ifnull;
	}
	
	public function getUriObject(){
		$arguments = func_get_args();
		$create_custom = array_key_exists(0, $arguments);
		if(!$this -> uri_object || $create_custom){
			$base = MyLike__Network__Request::getInstance() -> base();
			$fname = str_replace('.','\.',preg_replace('#/+$#','', $base));
			$uri = preg_replace(array('#^'.$fname."#"), array(''), 
				$create_custom ? $arguments[0] : $this['REQUEST_URI']);
			if($create_custom){
				return new MyLike__Network__Uri($uri);
			} else {
				$this -> uri_object = new MyLike__Network__Uri($uri);
			}
		}
		return $this -> uri_object;
	}
	
	public function getCookie($name){
		return $this -> get('COOKIE', $name);
	}
	
	public static function getObject(){
		return self::getInstance();
	}
	
	public static function getInstance(){
		if(is_null(self::$object)){
			$array = $_SERVER;
			$array['COOKIE'] = $_COOKIE;
			self::$object = new self($array);
		}
		return self::$object;
	}

}