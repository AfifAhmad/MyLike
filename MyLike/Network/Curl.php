<?php

class MyLike__Network__Curl{

	protected $api_endpoint;
	protected $query;
	protected $option = array();

	public function __construct($api_endpoint){
		$this -> setApiEndpoint($api_endpoint);
		$this -> option("CURLOPT_RETURNTRANSFER", true);
	}

	public function setApiEndpoint($api_endpoint){
		$urlarray = parse_url($api_endpoint);
		$this -> api_endpoint = (!empty($urlarray['scheme']) ? $urlarray['scheme'] : 'http' ).'://'.
			(!empty($urlarray['host']) ? $urlarray['host'] : MyLike__Network__Request::getObject() -> get('HTTP_HOST')) . 
			(array_key_exists('path', $urlarray) ? $urlarray['path'] : "" );
		return $this -> setQuery(empty($urlarray['query']) ? "": $urlarray['query']);
	}

	public function setOutputHeader($output){
		if(is_bool($output)){
			$this -> option("CURLINFO_HEADER_OUT", $output);
		}
		return $this;
	}

	public function getApiEndpoint(){
		return $this -> api_endpoint;
	}
	
	public function setCAInfo($filename, $use_data_dir = true){
		if($use_data_dir){
			$filename = MyLike__Autoload__Path::getDataDir() . $filename;
		}
		return $this -> option('CURLOPT_CAINFO', $filename);
	}

	public function resetOption(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			if(is_array($args[0])){
				foreach($args[0] as $option){
					unset($this -> option[$this -> keyOption($option)]);
				}
			} else {
				unset($this -> option[$this -> keyOption($args[0])]);
			}
		} else {
			$this -> option = array();
			$this -> option("CURLOPT_RETURNTRANSFER", true);
		}
		return $this;
	}

	public function option(){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$option = $args[0];
			if(is_array($option)){
				foreach($option as $opt => $value)
				$this -> option[$this -> keyOption($opt)] = $value;
			}elseif(array_key_exists(1, $args)){
				$this -> option[$this -> keyOption($option)] = $args[1];
			}else{
				$key = $this -> keyOption($option);
				if(array_key_exists($key, $this -> option)){
					return $this -> option[$key];
				}else return null;
			}
		}else{
			return $this -> option;
		}
		return $this;
	}

	private function keyOption($arg){
		if(is_string($arg)) $arg = constant(strtoupper($arg));
		return $arg;
	}

	public function setPost($post){
		if(is_array($post)){
			$count = count($post);
			$post = http_build_query($post);
		}else{
			$count = true;
		}
		$this -> option(
			array(
				'CURLOPT_POST'	=>$count,
				'CURLOPT_POSTFIELDS'=>$post
			)
		);
		return $this;
	}

	public function setQuery($get){
		$this -> getQuery() -> add($get);
		return $this;
	}

	public function getQuery(){
		if(is_null($this -> query)){
			$this -> query = new MyLike__Network__Query();
		}
		return $this -> query;
	}

	public function resetPost(){
		$this -> resetOption('CURLOPT_POST','CURLOPT_POSTFIELDS');
		return $this;
	}

	public function resetQuery(){
		$this -> query = array();
		return $this;
	}

	public function postToQuery(){
		$this -> setQuery($this -> option('CURLOPT_POSTFIELDS'));
		return $this -> resetPost();
	}

	public function queryToPost(){
		$this -> setPost($this -> getQuery() -> toArray());
		return $this -> resetQuery();
	}

	public function getResponse(){
		$api_endpoint = $this -> getApiEndpoint();
		if($api_endpoint){
			$connection = curl_init($api_endpoint .
				(($query = $this -> getQuery() -> toArray()) ? '?' . http_build_query($query): ''));
			foreach($this -> option() as $option => $value){
				curl_setopt($connection, $option, $value);
			}
			$return =  curl_exec($connection);
			return new MyLike__Network__Curl__Response(
				$return, 
				curl_getinfo($connection), 
				array_key_exists(CURLOPT_HEADER, $this -> option)
			);
		}else{
			return null;
		}
	}
}