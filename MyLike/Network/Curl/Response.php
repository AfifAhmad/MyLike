<?php

class MyLike__Network__Curl__Response{

	protected $info;
	protected $body;
	protected $header;

	public function __construct($raw, $info, $extractheader){
		$this -> info = new MyLike__ArrayObject__Magic($info);
		if( $extractheader){
			$limiter = strpos($raw,"\r\n\r\n");
			$headerStr = substr($raw, 0, $limiter);
			$this -> body = substr($raw, $limiter+3);
			$this -> header = new MyLike__Network__Curl__Header($headerStr);
		} else {
			$this -> body = $raw;
			$this -> header = new MyLike__Network__Curl__Header("");
		}
	}
	
	public function getInfo(){
		return $this -> info;
	}

	public function getHeader(){
		return $this -> header;
	}
	public function getBody(){
		return $this -> body;
	}
}