<?php
namespace library;

class Route extends Request{

    private static $url;
    private $ServerToken;
    private $_token = false;
    protected static $_route = array();
    private static $_result = false;

    public function __construct($token = false){
        session_start();

        if($token === true) {
            $this->token();
        }

        parent::__construct();
    }

    public function runToken($type){

        if($type === 'http'){
            $ServerToken = $this->ServerToken;
            $ClientToken = (isset($_COOKIE['CSRF_TOKEN'])) ? $_COOKIE['CSRF_TOKEN'] : false;
        }

        if($type === 'ajax'){
            $ServerToken = 'CSRF_TOKEN='.$this->ServerToken;
            $ClientToken = $this->token;
        }

        if($ServerToken !== false and $ClientToken !== false){
            if($ServerToken === $ClientToken){
                return true;
            }
            return false;
        }
        return false;
    }


    public function get($regex,$callback,$type = 'http'){
        self::$_route['GET'][$type][$this->regexRoute($regex)] = $callback;
    }

    public function post($regex,$callback,$type = 'http'){
        self::$_route['POST'][$type][$this->regexRoute($regex)] = $callback;
    }



    private function regexRoute($regexs){
        if(strpos($regexs,'[')){
            $regexs = str_replace(array('/:','[',']:'),array('/(?P<','>[',']+)'),$regexs);
        }
        $regex = '#^' . str_replace(array('/:', ':'), array('/(?P<', '>[a-zA-Z0-9_-]+)'), $regexs) . '$#uD';
        return $regex;
    }

    protected function collect(){
        if (isset(self::$_route[$this->method])) {

            $type = ($this->ajax === false) ? 'http' : 'ajax';
            $token = ($this->_token === false) ? $this->runToken($type) : true;

            if($token === false){
                session_destroy();
            }

            if (isset(self::$_route[$this->method][$type])) {

                if ($token === true) {

                    $route = self::$_route[$this->method][$type];
                    $url = ($this->url === false) ? '/' : $this->url;

                    foreach ($route as $key => $val) {

                        if (preg_match($key, $url, $matches)) {

                            array_walk($matches, function ($v, $k) use (&$matches) {
                                if (is_numeric($k)) unset($matches[$k]);
                            });

                            $matches['url'] = $url;

                            if ($this->method === 'POST') {
                                $matches['post'] = $this->getPost();
                            }

                            $callback = $route[$key];

                            if (is_callable($callback)) {
                                self::$_result = $callback($matches);
                            }
                        }
                    }
                }
            }
        }
    }

    private function token(){
        //unset($_SESSION['token']);
        if(is_array($_SESSION) and count($_SESSION) === 0){
            if(isset($_COOKIE['CSRF_TOKEN'])){
                unset($_COOKIE['CSRF_TOKEN']);
            }
        }

        if (!isset($_COOKIE['CSRF_TOKEN']) and !isset($_SESSION['token'])) {
            $token = $this->generation();
            setcookie('CSRF_TOKEN', $token, 0, '/');
            $_SESSION['token'] = $token;
            $this->_token = true;
        }
        $this->ServerToken = (isset($_SESSION['token'])) ? $_SESSION['token'] : false;
    }

    private function generation(){
        return md5($this->clientIp.uniqid(rand(), true));
    }

    public function Render(){
        $this->collect();
        if(self::$_result !== false){
            echo self::$_result;
        }else{
            header("HTTP/1.1 404 Not Found");
            echo View::render('404',array('url'=>$_SERVER['REQUEST_URI']));
        }
    }

}