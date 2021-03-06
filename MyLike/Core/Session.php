<?php

class MyLike__Core__Session extends MyLike__ArrayObject__Magic{

	private $handler;
	protected $_prepare_data = array();
	protected $_default_data = array();
	protected $is_started = false;
	protected static $instance;
	const DEFAULT_CLASS_HANDLER = "File";
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	protected function callCookiePath(){
		$data = $this -> usePrepareCookiePath();
		if(!is_null($data)){
			ini_set('session.cookie_path', $data);
		} 
		return $this;
	}
	
	protected function callRemember(){
		$data = $this -> usePrepareRemember();
		if(!is_null($data)){
			if($data === true ){
				ini_set('session.cookie_lifetime', $this -> getRememberTime());
			}elseif(preg_match("#^[0-9]+$#", $data)){
				ini_set('session.cookie_lifetime', $data);
			} else {
				ini_set('session.cookie_lifetime', 0);
			}
		} 
		return $this;
	}
	
	protected function callName(){
		$data = $this -> usePrepareName();
		if(!is_null($data)){
			if(strlen($data)==0){
				$data = $this -> getConfig('name');
				if(strlen($data)>0) session_name($data);
			} else {
				session_name($data);
			}
		}
		return $this;
	}
	
	protected function callUseCookies(){
		$data = $this -> usePrepareUseCookies();
		if(!is_null($data)){
			ini_set('session.use_cookies', (boolean) $data);
		}
		return $this;
	}

	protected function callHandler(){
		$data = $this -> usePrepareHandler();
		if($data !== false){
			if(is_null($data)){
				$data = $this -> getConfig("handler");
				if(!$data){
					$data = self::DEFAULT_CLASS_HANDLER;
				}
			}
			$class = MyLike__Core__Core::getAppNamespace() . "__Session__" . $data;
			if(!class_exists($class)){
				$class = "MyLike__Session__".$data;
			}
			if(!($this -> handler) || get_class($this -> handler) != $class){
				$this -> handler = new $class();
				session_set_save_handler(
					array($this -> handler, 'open'),
					array($this -> handler, 'close'),
					array($this -> handler, 'read'),
					array($this -> handler, 'write'),
					array($this -> handler, 'destroy'),
					array($this -> handler, 'gc')
				);
			}
		}
	}

	protected function callId(){
		$data = $this -> usePrepareId();
		if($data === ""){
			$char[0] = "abcdefghijklmnopqrstuvwxyz";
			$char[1] = "0123456789";
			$string = "";
			while(strlen($string)<26){
				$rand = rand (0, 1);
				$string .= $char[$rand][rand(0, strlen($char[$rand])-1)];
			}
			session_id($string);
		} elseif(!is_null($data)){
			session_id($data);
		}
		return $this;
	}
	
	protected function callSavePath(){
		$data = $this -> usePrepareSavePath();
		if(!array_key_exists("save_path", $this -> _default_data)){
			$this -> _default_data["save_path"] = session_save_path();
		}
		if($data !== false){
			if(strlen((string)$data)>0){
				$path = $data;
			} else{
				$path = $this -> getConfig("save_path");
				if(strlen((string)$path)==0) {
					$path = MyLike__Autoload__Path::getSessionDir();
				}	
			}
			session_save_path($path);
		} else {
			session_save_path($this -> _default_data["save_path"]);
		}
		return $this;
	}
	
	public function getTimeExpired(){
		if(ini_get('session.cookie_lifetime')>0){
			return time() + $this -> getConfig("remember_time");
		} else {
			return 0;
		}
	}
	
	public function getConfig($key){
		return MyLike__Config__Config::getPluginData("session", $key);
	}
	
	private function getRememberTime()
	{
		$rtime = $this -> getConfig("remember_time");
		if(is_null($rtime)){
			$rtime = 1296000;
		}
		return $rtime;
	}

	public function start($force_start = false){
		if($force_start || $this -> is_started === false){
			session_write_close();
			$this -> callRemember()
				  -> callId()
				  -> callName()
				  -> callCookiePath()
				  -> callUseCookies()
				  -> callSavePath()
				  -> callHandler();
			session_start();
			$this -> is_started = true;
		}
		$this -> data = &$_SESSION;
		return $this;
	}

	public function getId(){
		return session_id();
	}

	public function setNotification($key, $message){
		if(!$this['notification'] || !array_key_exists($key, $this['notification']) ){
			$this -> set('notification', $key, array());
		}
		$buffer = $this['notification'];
		if(is_array($message)){
			foreach($message as $msg){
				$buffer[$key][] = $msg;
			}
		} else {
			$buffer[$key][] = $message;
		}
		$this['notification'] = $buffer;
		return $this;
	}

	public function getNotification($key){
		$args = func_get_args();
		if(array_key_exists(0, $args)){
			$key = $args[0];
			if(isset($this['notification'][$key])){
				$notif = $this['notification'][$key];
				$this -> remove ('notification', $key);
				if(!$this['notification']){
					unset($this['notification']);
				}
				return  $notif;
			} else {
				return null;
			}
		} else {
			$notif = $this['notification'];
			$this -> remove ('notification');
			return  $notif;
		}
	}
	
	public function getAllNotification(){
		$notif_keys = array('error' => 'danger', 'success' => 'success', 'warning' => 'warning');
		$notifications = array();
		if(MyLike__ArrayObject__Magic::foreachable($this['notification'])){
			foreach($notif_keys as $code => $class){
				$message = $this -> get('notification', $code);
				if($message){
					$notifications[$code]['messages'] = $message;
					$notifications[$code]['class'] = $class;
					$notifications[$code]['caption'] = ucfirst($code);
				}
			}
		} 
		unset($this['notification']);
		return $notifications;
	}
	
	public function __call($method, $value){
		if(preg_match("#^(?P<function>prepare|usePrepare)(?P<property>(?:[A-Z][a-z0-9]*)+)$#",
			$method,$match)){
			preg_match_all ( "#[A-Z][a-z0-9]*#" , $match["property"], $matches);
			$str = "";
			foreach( $matches[0] as $var){
				$str .= "_".strtolower($var);
			}
			$str = preg_replace("#^_#","",$str);
			if($match["function"]=="prepare"){
				if(!array_key_exists(0, $value) || is_null($value[0]))
				unset($this -> _prepare_data[$str]);
				else $this -> _prepare_data[$str] = $value[0];
				return $this;
			} elseif(array_key_exists($str, $this -> _prepare_data)) {
				return $this -> _prepare_data[$str];
			} else {
				return null;
			}
		} else {
			return parent::__call($method, $value);
		}
	}
}