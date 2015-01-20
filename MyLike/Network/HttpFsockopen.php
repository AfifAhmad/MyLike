<?php 

/*
justin was here :D
*/

class MyLike__Network__HttpFsockopen {

	protected $url;
	protected $path;
	protected $host;
	protected $query;
	protected $post;
	protected $port;
	protected $headers;
	protected $ssl;
	protected $method;
	protected $timeout;

	
	public function __construct($url, $use_autoload = true){
		$url_array = parse_url($url);
		
		if(!empty($url_array["scheme"]) && $url_array["scheme"]=="https"){
			$this -> ssl = true;
		} else {
			$this -> ssl = false;
		}

		if(empty($url_array["port"])){
			if($this -> ssl){
				$this -> port = 443;
			} else {
				$this -> port = 80;
			}
		}

		if(array_key_exists("path", $url_array)){
			$this -> path = $url_array["path"];
		} else {
			$this -> path = false;
		}
		
		if(array_key_exists("query", $url_array)){
			$this -> query = $url_array["query"];
		} else {
			$this -> query = false;
		}
		
		$this -> host = $url_array["host"];
		$this -> method = "GET";
		$this -> timeout = 15;
	}
	
	public function setQueryData($data){
		if(is_array($data)){
			$data = http_build_query($data);
		}
		$this -> query = $data;
		return $this;
	}
	
	public function setPostData($data){
		if(is_array($data)){
			$data = http_build_query($data);
		}
		$this -> post = $data;
		$this -> method = "POST";
		$this -> setHeaders("Content-Type", "application/x-www-form-urlencoded");
		return $this;
	}

	public function setMethod($method){
		$previous_method = $this -> method;
		if(preg_match("#^[a-z]+$#i", $method)){
			$this -> method = strtoupper($method);
		}
		if($this -> method == "POST" && $previous_method != "POST"){
			$this -> setHeaders("Content-Type", "application/x-www-form-urlencoded");
		}
		if($this -> method != "POST" && $previous_method == "POST"){
			$this -> setHeaders("Content-Type", null);
		}
		return $this;
	}

	public function setTimeout($timeout){
		$this -> timeout = $timeout;
		return $this;
	}

	public function setPort($port){
		$this -> port = $port;
		return $this;
	}

	public function setHeaders($key, $value = null){
		if(is_array($key)){
			foreach($key as $key => $value){
				if(is_null($value)){
					unset($this -> headers[$key]);
				} else {
					$this -> headers[$key] = $value;
				}
			}
		} else {
			if(is_null($value)){
				unset($this -> headers[$key]);
			} else {
				$this -> headers[$key] = $value;
			}
 		}
		return $this;
	}
	
	public function setUserAgent($user_agent){
		return $this -> setHeaders("User-Agent", $user_agent);
	}
	
	public function exec(){
		$socket = fsockopen(($this -> ssl ? "ssl://" : "") . $this -> host, $this -> port, $errno, $errstr,
			$this -> timeout);
		$contents = "";
		
		if($socket){
			$path = (strlen($this -> path) ? $this -> path : "/") . (strlen($this -> query)>0 ? "?" . $this -> query : "");
			$http  = $this -> method . " ". $path ." HTTP/1.1\r\n";
			$http .= "Host: ".$this -> host."\r\n";
			if(is_array($this -> headers))
			foreach($this -> headers as $key => $value){
				$http .= $key. ": ".$value."\r\n";
			}
			$http .= "Content-length: " . strlen($this -> post) . "\r\n";
			$http .= "Connection: close\r\n\r\n";
			if(!is_null($this -> post))
			$http .= $this -> post . "\r\n\r\n";
			fwrite($socket, $http);	
			while (!feof($socket)) {
				$contents .= fgetc($socket);
			}
			fclose($socket);
		}
		
		return new MyLike__Network__HttpFsockopen__Response($socket, $contents, $errno, $errstr);
	}

}