<?php
require_once ROOT.'init/Autoload.php';

use library\Route,
    library\DB,
    library\View,
    library\Language as Lang;

Autoload::register(ROOT);

View::__Init();
DB::__Init();

if(!defined('INSTALL')){

    $route = new Route(true); // token run = true

    Lang::__Init();

    $route->get('/',function(){
        return Lang::_('install:start',array('{url}'=>APP_PATH.'/install'));
    });

    $route->get('/install',function(){
        return Install::getPage('start');
    });

    $route->get('/install/:step[a-z]:/:param[a-z]:',function($params){
        return Install::getPage('AjaxPageInstall',$params);
    },'ajax');

    $route->post('/install/:step[a-z]:',function($params){
        return Install::getPage('AjaxSend',$params);
    },'ajax');

    $route->post('/lang',function($post){
        if(isset($post["post"]['data'])){
            $_SESSION['lang'] = $post["post"]['data'];
            return true;
        }
        return false;
    },'ajax');

    $route->Render();
    return false;
}

if (version_compare(PHP_VERSION, '5.3.0') < 0) {
    die('[My PHP version('.PHP_VERSION.')] << [APP PHP version(5.3.0)]');
}

DB::roulette();
DB::voteData();

$route = new Route(true); // token run = true

Lang::__Init();

$route->get('/',function(){
    return Application::getPage('index');
});

$route->get('/roulette/:id[0-9]:',function($params){
    return Application::getPage('roulette',$params['id']);
});

$route->get('/win',function(){
    return Application::getPage('WinPage');
});

$route->get('/history',function(){
    return Application::getPage('history');
});

$route->post('/roulette/:id[0-9]:',function($params){
    return Application::getPage('rouletteRandom',$params['id']);
},'ajax');

$route->post('/send',function($params){
    return Application::getPage('sendItem',$params);
},'ajax');

$route->get('/admin/:type[a-z]:/:param[a-z0-9]:',function($params){
    return Admin::getPage('adminContent',$params);
});

$route->get('/exit',function(){
    return Application::getPage('user_exit');
});

$route->post('/admin/:type[a-z]:/:param[a-z0-9]:',function($params){
    return Admin::getPage('modifyAjax',$params);
},'ajax');

$route->get('/admin/delete/:type[a-z]:/:param[a-z0-9]:',function($params){
    return Admin::getPage('deleteAjax',$params);
},'ajax');

$route->get('/admin',function(){
    return Admin::getPage('adminHome');
});

$route->post('/login',function($params){
    return Application::getPage('login',$params['post']);
},'ajax');

$route->post('/lang',function($post){
    if(isset($post["post"]['data'])){
        $_SESSION['lang'] = $post["post"]['data'];
        return true;
    }
    return false;
},'ajax');


$route->get('/captcha.gif',function(){
     Application::Captcha();
});

$route->Render();





