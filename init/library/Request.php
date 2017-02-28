<?php
namespace library;

class Request {

    protected static $_headers;

    public function __construct(){
        if (isset($_SERVER['REQUEST_METHOD'])) {
            self::$_headers['method'] = $_SERVER['REQUEST_METHOD'];
        }

        if(isset($_SERVER['HTTP_X_CSRF_TOKEN'])){
            self::$_headers['token'] = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            self::$_headers['ajax'] = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }

        $headers_ip = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR', 'HTTP_X_FORWARDED',
            'HTTP_FORWARDED', 'HTTP_VIA', 'HTTP_X_COMING_FROM',
            'HTTP_X_COMING_FROM', 'HTTP_COMING_FROM',
            'REMOTE_ADDR');

        foreach ($headers_ip as $header) {
            if (isset($_SERVER[$header])) {
                self::$_headers['clientIp'] = $_SERVER[$header];
            }
        }

        if(isset($_GET['_url'])){
            self::$_headers['url'] = $_GET['_url'];
        }
    }

    public function __get($key){
        if(isset(self::$_headers[$key]) and self::$_headers[$key] !== ''){
            return self::$_headers[$key];
        }
        return false;
    }

    public function getPost(){
        if($this->method === 'POST'){
            if(is_array($_POST) and count($_POST) > 0){
                return $_POST;
            }
            return false;
        }
    }
}