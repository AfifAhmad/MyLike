<?php

class MyLike__View__Controller extends MyLike__View__View{

	protected $_header = array();
	protected $_header_sent = false;
	protected $_respond_code = false;
	protected $_respond_code_status = array(
					100 => 'Continue',
					101 => 'Switching Protocols',
					200 => 'OK',
					201 => 'Created',
					202 => 'Accepted',
					203 => 'Non-Authoritative Information',
					204 => 'No Content',
					205 => 'Reset Content',
					206 => 'Partial Content',
					300 => 'Multiple Choices',
					301 => 'Moved Permanently',
					302 => 'Found',
					303 => 'See Other',
					304 => 'Not Modified',
					305 => 'Use Proxy',
					306 => '(Unused)',
					307 => 'Temporary Redirect',
					400 => 'Bad Request',
					401 => 'Unauthorized',
					402 => 'Payment Required',
					403 => 'Forbidden',
					404 => 'Not Found',
					405 => 'Method Not Allowed',
					406 => 'Not Acceptable',
					407 => 'Proxy Authentication Required',
					408 => 'Request Timeout',
					409 => 'Conflict',
					410 => 'Gone',
					411 => 'Length Required',
					412 => 'Precondition Failed',
					413 => 'Request Entity Too Large',
					414 => 'Request-URI Too Long',
					415 => 'Unsupported Media Type',
					416 => 'Requested Range Not Satisfiable',
					417 => 'Expectation Failed',
					500 => 'Internal Server Error',
					501 => 'Not Implemented',
					502 => 'Bad Gateway',
					503 => 'Service Unavailable',
					504 => 'Gateway Timeout',
					505 => 'HTTP Version Not Supported'
				);
				
	public function getHelper($helper){
		return $this -> getParent() -> getHelper($helper);
	}
	
	public function execute(){
		if(!$this -> _header_sent) $this -> sentHeader();
		return parent::execute();
	}

	public function header(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			if(array_key_exists(1, $args)){
				if($args[0]){
					$this -> _header[$args[0]] = $args[1];
				}else {
					$buffer = $this -> _header;
					unset($buffer[$args[0]]);
					$this -> _header = $buffer;
				}
				return $this;
			} else{
				if(isset($this -> _header[$args[0]])) {
					return $this -> _header[$args[0]];
				} else {
					return null;
				}
			}
		} else {
			return $this -> _header;
		}
	}
	
	public function setRespondCode($code){
		$this -> _respond_code = $code;
		return $this;
	}
	
	public function sentHeader(){
		if($this -> _respond_code && 
			array_key_exists($this -> _respond_code, $this -> _respond_code_status)){
			header("{$this -> getRequest() -> getHttpProtocol()}/1.0 {$this -> _respond_code} {$this -> _respond_code_status[$this -> _respond_code]}");
		}
		foreach($this -> _header as $key => $header){
			header($key.": ".$header);
			if($key == "Location"){
				$killme = true;
			}
		}
		$this -> _header_sent = true;
		if(isset($killme)) die; 
		return $this;
	}
	
	public function redirect($url){
		$this -> header("Location",$url);
	}
	
	public function getLayout(){
		$parent = $this -> getParent();
		if(!$this -> getRequest() -> isAjax() || ($parent instanceof MyLike__Controller__Web) ||$parent -> useLayoutWhenAjax()){
			$layout = $this['layout'];
			if($layout !== false){
				$package = $this -> getLayoutPackage();
				$layout = ((strlen($package)>0) ? $package.'/' : '' ) . 
						($layout ? $layout : MyLike__View__Abstract::DEFAULT_LAYOUT);
			}
			return $layout;
		} else {
			return false;
		}
	}
	
	public function allowAjaxXDomain(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			if($args[0] === true){
				if(!$this -> getRequest() -> isReferrerInside()){
					preg_match('#^https?://(?P<domain>[^/]+)#',$_SERVER['HTTP_REFERER'], $domain);
					$this -> header("Access-Control-Allow-Origin",  $domain['domain']);
				}
			} elseif(!$args[0]) {
				$this -> header("Access-Control-Allow-Origin",  null);
			} else {
				$this -> header("Access-Control-Allow-Origin",  $args[0]);
			}
			return $this;
		} else {
			return $this -> header("Access-Control-Allow-Origin");
		}
	}
}