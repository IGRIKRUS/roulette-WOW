<?php
namespace library;

use library\Language as Lang;

class View {

    private static $_view;

    private static $ext = '.twig';

    public static function __Init(){
        try {
            if(!is_writable(ROOT.'init/storage/cache/template')){
                exit('No rights to write "~init/storage/cache/template" !');
            }

            $loader = new \Twig_Loader_Filesystem(ROOT.'init/template');
            self::$_view = new \Twig_Environment($loader, array(
                'cache'       => ROOT.'init/storage/cache/template',
                'auto_reload' => true
            ));

            $filter = new \Twig_SimpleFilter('unserialize', 'unserialize');

            self::$_view->addFilter($filter);

        } catch (\Exception $e) {
            if(DISPLAY_ERROR === true) {
                trigger_error('View Error: ( ' . $e->getMessage() . '). Error Code : ' . $e->getCode() . ' <br />');
            }
            exit();
        }
    }

    public static function render($layout,$params = array()){
        try {

            if(!isset($params['lang'])) {
                $params['lang'] = Lang::get();
                $params['app_path'] = APP_PATH;
            }

            $params["\x63\x6F\x70\x79"] = Lang::au();
            $params['version'] = array('install'=>1.2,'app'=>1.5);
            $view = self::$_view->render($layout.self::$ext,$params);

            if(APP_PATH !== '') {
                return str_replace(array(
                    'href="/public/',
                    '<a href="/',
                    'src="/public/',
                    'action="/'
                ), array(
                    'href="' . APP_PATH . '/public/',
                    '<a href="' . APP_PATH . '/',
                    'src="' . APP_PATH . '/public/',
                    'action="' . APP_PATH . '/'
                ), $view);
            }else{
                return $view;
            }

        } catch (\Exception $e) {
            if(DISPLAY_ERROR === true) {
				if(defined('INSTALL') and INSTALL === true and is_writable(ROOT.'init/storage/cache/template') === false){
					die('init/storage/cache/template chmod 777 !');
				}
                trigger_error('View Error: ( ' . $e->getMessage() . '). Error Code : ' . $e->getCode() . ' <br />');
            }
            exit();
        }
    }
}