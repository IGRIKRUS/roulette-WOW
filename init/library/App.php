<?php
namespace library;

class App {

    protected static $init;
    protected static $user;
    public static $admin;

    public static function __Init(){
        self::$user = (isset($_SESSION['user'])) ? $_SESSION['user'] : false;
        self::$admin = (isset($_SESSION['admin'])) ? $_SESSION['admin'] : false;

        $className = self::getClassName();
        if(self::$init === null){
            self::$init = new $className();
        }
        return self::$init;
    }

    final protected static function getClassName(){
        return get_called_class();
    }

    protected function __construct(){}

    public static function getPage($page,$args = null){
        self::__Init();
        $method = (method_exists(self::$init,$page)) ? true : false;

        $Acl = array(
            'adminHome'=>self::$admin,
            'adminContent'=>self::$admin,
            'deleteAjax'=>self::$admin,
            'modifyAjax'=>self::$admin,
            'check'=>self::$admin
        );

        if($method === true) {
            if(self::$user === false and $page == 'login') {
                return self::$init->login($args);
            } elseif (self::$user === false and !isset($Acl[$page])) {
                return self::$init->index($args);
            } elseif (isset($Acl[$page]) and $Acl[$page] === true) {
                return self::$init->$page($args);
            }elseif(!isset($Acl[$page])){
                return self::$init->$page($args);
            }else{
                return false;
            }
        }
    }

    public function regex($filter, $string){
        if ($filter == 'stringInt') {
            return (preg_match('/^[a-zA-Z0-9_\.-]+$/', $string)) ? true : false;
        }

        if ($filter == 'email') {
            return (filter_var($string, FILTER_VALIDATE_EMAIL)) ? true : false;
        }
    }

    public function stringReplace($string){
        return htmlentities(strip_tags($string));
    }

    public function json($array){
        header('Content-type: application/json; charset=utf-8');
        return json_encode($array);
    }

    protected function hashPass($login,$pass){
        return sha1(strtoupper($login).':'.strtoupper($pass));
    }

    protected function refrash($sec = 0){
        header("Refresh: {$sec};");
    }

    protected function location($url = ''){
        header("Location: $url");
    }

    protected function parseForm($data,$key){
        if($data !== ''){
            parse_str($data[$key], $data);
            return $data;
        }
        return false;
    }

    protected function macros_render($macros,$params,$name = null){
        $prm = unserialize($params);

        if($name !== null){
            $prm['{charName}'] = $name;
        }

        return str_replace(array_keys($prm),array_values($prm),$macros);
    }

    final protected function __clone(){}

    final protected function __sleep(){}

    final protected function __wakeup(){}
}