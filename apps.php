<?php 

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);

if(array_key_exists('dev', $_COOKIE) && preg_match('#^[a-z]{2,5}$#', $_COOKIE['dev'])){
	spl_autoload_extensions('.'.$_COOKIE['dev'].'.php,.php');
} else {
	spl_autoload_extensions('.php');
}
	
include_once("MyLike/Autoload/Autoload.php");
include_once("MyLike/Autoload/Psr4.php");

spl_autoload_register(array("MyLike__Autoload__Autoload", "handler"));

if(isset($psr4)){
	MyLike__Autoload__Psr4::addApp($psr4, dirname(dirname(dirname(__FILE__))) . DS . 'apps');
	$app_namespace = $psr4;
} else {
	MyLike__Autoload__Autoload::setConfig($app_namespace, dirname(dirname(dirname(__FILE__))) . DS . 'apps', 'mylike');
}
echo MyLike__Core__Core::dispatch($app_namespace);