<?php
namespace library;

class Language{

    protected static $_init;
    protected static $_path = 'init/storage/i18n/';

    public static $_default = 'en';

    protected static $_lang = false;

    public static $_active;

    public static function __Init(){
        if (defined('APP_LANG')) {
            self::$_default = APP_LANG;
        }

        if(isset($_SESSION['lang'])){
            self::$_default = $_SESSION['lang'];
        }

        if (is_file(ROOT . self::$_path . self::$_default . '.php')) {
            self::$_active = self::$_default;
            self::$_lang = require_once(ROOT . self::$_path . self::$_default . '.php');
        } else {
            trigger_error('Language load error: not found lang "' . self::$_default . '" <br />');
            exit();
        }
    }

    public static function get(){
        return new self();
    }

    public static function _($params,$replace = null){
        if(strpos($params,':')){
            $keys = explode(':',$params);
            $arr = self::$_lang;
            foreach($keys as $val){
                if(isset($arr[$val])){
                    $arr = $arr[$val];
                    if($replace !== null and is_array($replace)){
                        $arr = str_replace(array_keys($replace),array_values($replace) ,$arr);
                    }
                }else{
                    $arr = '{'.implode('|',$keys).'}';
                }
            }
            if($arr === '{'){
                return $arr = '{'.implode('|',$keys).'}';
            }
            return $arr;
        }else{
            if(isset(self::$_lang[$params])){
                return self::$_lang[$params];
            }
            return '{'.$params.'}';
        }
    }

    public static function getLangList(){
        $lang =  array_values(array_diff(scandir(ROOT . self::$_path),array('..','.')));
        $lang = array_map(function($el){
            return str_replace('.php','',$el);
        },$lang);
        return $lang;
    }

    public static function getActive(){
        return self::$_active;
    }

    public static function au(){
        return "\x3C\x73\x74\x72\x6F\x6E\x67\x3E\x52\x6F\x75\x6C\x65\x74\x74\x65\x20\x57\x4F\x57\x20\xC2\xA9\x20\x32\x30\x31\x36\x20\x43\x6F\x64\x65\x20\x62\x79\x20\x3C\x61\x20\x68\x72\x65\x66\x3D\x22\x2F\x2F\x77\x6F\x77\x6A\x70\x2E\x6E\x65\x74\x2F\x69\x6E\x64\x65\x78\x2F\x38\x2D\x31\x38\x31\x36\x39\x32\x22\x3E\x49\x47\x52\x49\x4B\x52\x55\x53\x3C\x2F\x61\x3E\x2E\x3C\x2F\x73\x74\x72\x6F\x6E\x67\x3E";
    }
}