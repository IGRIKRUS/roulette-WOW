<?php
date_default_timezone_set('Europe/Moscow');

define('ROOT',realpath(__DIR__).DIRECTORY_SEPARATOR);

function slash($path){
	return str_replace('\\','/',$path);
}

function auto_path(){
	$root = substr(slash(ROOT),0,-1);
    $root_site = (isset($_SERVER['DOCUMENT_ROOT'])) ? slash($_SERVER['DOCUMENT_ROOT']) : $root;
    $path = str_replace($root_site,'',$root);
    define('APP_PATH',$path);
}

auto_path();

define('DISPLAY_ERROR',false);

if(DISPLAY_ERROR === true) {
    if (!ini_get('display_errors')) {
        ini_set('display_errors', 1);
    }
    error_reporting(E_ALL|E_NOTICE|E_STRICT);
}else{
    ini_set('display_errors', 0);
    error_reporting(0);
}

require_once ROOT.'init/bootstrap.php';



