<?php 

/*
justin was here :D
*/

class MyLike__Network__HttpFsockopen__Response {
	
	protected $socket;
	protected $errno;
	protected $errstr;
	
	public function __construct($socket, $contents, $errno, $errstr){
		$this -> socket = $socket;
		$this -> errno = $errno;
		$this -> errstr = $errstr;
		if( $errno == 0){
			$limiter = strpos($contents,"\r\n\r\n");
			$headerStr = substr($contents, 0, $limiter);
			$this -> body = substr($contents, $limiter+4);
			$this -> header = new MyLike__Network__HttpFsockopen__Header($headerStr);
		} else {
			$this -> body = $contents;
			$this -> header = new MyLike__Network__HttpFsockopen__Header("");
		}
	}
	
	public function getContent(){
		return $this -> body;
	}
	
	public function getHeader(){
		return $this -> header;
	}
	
	public function getSocket(){
		return $this -> socket;
	}
	
	public function getErrno(){
		return $this -> errno;
	}
	
	public function getErrstr(){
		return $this -> errstr;
	}
}