<?php 


class MyLike__Routing__Uri extends MyLike__Routing__Routing{

	protected function generate(){
		return $this -> processRequest();
	}

	protected function processRequest(){
		$controller_prefix = $this -> getControllerPrefix();
		$notfound =  MyLike__Config__Config::getPluginData("core", "notfound");
		if(strlen($notfound)==0){
			$notfound = "notfound";
		}
		$routing = MyLike__Config__Config::getPluginData("core", "routing"); 
		if(!MyLike__ArrayObject__Magic::indexable($routing)){
			$routing = array('/' => 'index');
		}
		
		$arguments = func_get_args();
		if(array_key_exists(0, $arguments)){
			$uri_object = MyLike__Network__Request::getInstance() -> getUriObject($arguments[0]);
		} else {
			$uri_object = MyLike__Network__Request::getInstance() -> getUriObject();
		}
		
		$path = $uri_object -> toArray('path');
		
		$uri_array['parameter'] = explode('/', $path);
		
		$uriorig = preg_match('#^((?:/[a-z_][a-z0-9_]*)*)(/.*)?$#i',$path, $match);
		$args = (!empty($match[2])) ? explode('/', $match[2]) : array();
		

		array_shift($args);
		$next_controller = $match[1];
		$i = 0;
		$app_namespace = MyLike__Core__Core::getAppNamespace()."__";
		$controller_to_try = "Controller". ( $controller_prefix ? "__" . $controller_prefix : "" );
		while(preg_match("#(.*)/([^/]+)$#",$next_controller, $match)){
			$i++;
			if(array_key_exists($next_controller, $routing)){
				$routed = $routing[$next_controller];
			} else {
				$routed = $next_controller;
			}
			$mainpath = $routed;
			$controller = $controller_to_try.str_replace('/','__',$routed);
			if(class_exists($app_namespace . $controller)){
				$fix_controller = $controller;
				$cpath = preg_replace('#^/#','',$routed);
				if(!empty($args) && method_exists($app_namespace . $controller, $args[0].'Action')){
					$main_path = '/'.$args[0];
					$action = $args[0];
					array_shift($args);
				}
				break;
			} else {
				$next_controller = $match[1];
				array_unshift($args, $match[2]);
			}
		}
		if(empty($fix_controller)){
			$mainpath = '/';
			$routed = $routing['/'];
			$controller = $controller_to_try . '__' . str_replace('/','__',$routed);
			if($i==0){
				$fix_controller = $controller;
				$cpath = $routed;
			}
			if(class_exists($app_namespace . $controller) && !empty($args) 
				&& method_exists($app_namespace . $controller, $args[0].'Action')){
				$fix_controller = $controller;
				$cpath = $routed;
				$main_path = '/'.$args[0];
				$action = $args[0];
				array_shift($args);
			}
		}
		if(!isset($fix_controller)){
			$controller = $controller_to_try . '__' . str_replace('/','__',$notfound);
			$fix_controller = $controller;
			$cpath = $notfound;
		}
			
		$data['controller'] = preg_replace('#^Controller__#', '', $fix_controller);
		$data['query'] = $uri_object -> toArray('query');
		$data['path'] = $uri_object -> toArray('path');
		$data['controller_path'] = $controller_prefix.'__'.$cpath;
		$data['content_directory'] = str_replace('__', DS, $controller_prefix);
		$data['content'] = $cpath;
		$data['main_path'] = $mainpath;
		$data['action'] ='';
		if(isset($action)&&isset($main_path)){
			$data['main_path'] .= $main_path;
			$data['action'] = $action;
		}
		$data['post'] = MyLike__Network__Request::getInstance() -> getPost() -> toArray();
		$data['args'] = $args;
		$this -> data['data'] = new MyLike__ArrayObject__Magic($data);
	}
	
}