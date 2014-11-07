<?php

class MyLike__Datasource__MySQL extends PDO{

	protected $db_conf;
	protected $table_prefix;
	protected $parameter;
	protected $last_query;

	public function __construct($db_conf){
		$this -> db_conf = $db_conf;
		$parameter = MyLike__Config__Config::getData('db_connection/'.$db_conf);
		parent::__construct ('mysql:dbname='.$parameter['database'].';host='.$parameter['host'], $parameter['login'], $parameter['password'] ); 
		if($parameter["names"]){
			$this -> exec("SET NAMES ".$parameter['names']);
		}		
		$this -> parameter = $parameter;
	}
	
	public function getTablePrefix(){
		return $this -> parameter['prefix'];
	}
	
	public function getAffectedRow(){
		if($this -> last_query){
			return $this -> last_query -> rowCount();
		} else {
			return 0;
		}
	}
	
	public function query($sql){
		$result = parent::query($sql);
		$this -> last_query  = $result;
		if(!$result){
			$as_string = MyLike__Debug__Debug::asString();
			MyLike__Debug__Debug::asString(true);
			$text = "SQL Error: ".$this -> errorCode()."\r\n";
			$text .= "Query: ".$sql."\r\n";
			$text .= "Backtrace: ".MyLike__Debug__Debug::trace()."\r\n\r\n";
			$logging = MyLike__Cache__File::getInstance();
			$logging -> set("mysql/error.txt") -> savePrepend($text);
			MyLike__Debug__Debug::asString($as_string);
		}
		return $result;
	}
	
}