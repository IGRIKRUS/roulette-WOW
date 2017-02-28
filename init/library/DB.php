<?php
namespace library;

class DB {

    public static $auth;
    public static $characters;
    public static $roulette;
    protected static $_config;
    private static $_voteData;

    public static function __Init(){
        if(is_file(ROOT.'init/config/config.php')){
            self::$_config = require_once ROOT.'init/config/config.php';
        }
    }

    public static function auth(){
    try {
        self::$auth = new \medoo(array(
                'database_type' => 'mysql',
                'database_name' => self::$_config['wowdata']['auth'],
                'server' => self::$_config['wowdata']['host'],
                'username' => self::$_config['wowdata']['user'],
                'password' => self::$_config['wowdata']['pass'],
                'charset' => 'utf8',
                'port' => self::$_config['wowdata']['port'],
                'option' => array(
                    \PDO::ATTR_CASE => \PDO::CASE_NATURAL
                ))
        );
    } catch (\Exception $e) {
        if(DISPLAY_ERROR === true) {
            trigger_error('Data Base Error: ( ' . $e->getMessage() . '). Error Code : ' . $e->getCode() . ' <br />');
        }
        exit();
    }
}

    public static function characters(){
        try {
            self::$characters = new \medoo(array(
                    'database_type' => 'mysql',
                    'database_name' => self::$_config['wowdata']['characters'],
                    'server' => self::$_config['wowdata']['host'],
                    'username' => self::$_config['wowdata']['user'],
                    'password' => self::$_config['wowdata']['pass'],
                    'charset' => 'utf8',
                    'port' => self::$_config['wowdata']['port'],
                    'option' => array(
                        \PDO::ATTR_CASE => \PDO::CASE_NATURAL
                    ))
            );
        } catch (\Exception $e) {
            if(DISPLAY_ERROR === true) {
                trigger_error('Data Base Error: ( ' . $e->getMessage() . '). Error Code : ' . $e->getCode() . ' <br />');
            }
            exit();
        }
    }

    public static function roulette(){
        try {
            self::$roulette = new \medoo(array(
                    'database_type' => 'mysql',
                    'database_name' => self::$_config['roulette']['dbname'],
                    'server' => self::$_config['roulette']['host'],
                    'username' => self::$_config['roulette']['user'],
                    'password' => self::$_config['roulette']['pass'],
                    'charset' => 'utf8',
                    'port' => self::$_config['roulette']['port'],
                    'option' => array(
                        \PDO::ATTR_CASE => \PDO::CASE_NATURAL
                    ))
            );
        } catch (\Exception $e) {
            if(DISPLAY_ERROR === true) {
                trigger_error('Data Base Error: ( ' . $e->getMessage() . '). Error Code : ' . $e->getCode() . ' <br />');
            }
            exit();
        }
    }

    public static function voteData(){
        try {
            self::$_voteData = new \medoo(array(
                    'database_type' => 'mysql',
                    'database_name' => self::$_config['votedata']['db']['dbname'],
                    'server' => self::$_config['votedata']['db']['host'],
                    'username' => self::$_config['votedata']['db']['user'],
                    'password' => self::$_config['votedata']['db']['pass'],
                    'charset' => 'utf8',
                    'port' => self::$_config['votedata']['db']['port'],
                    'option' => array(
                        \PDO::ATTR_CASE => \PDO::CASE_NATURAL
                    ))
            );
        } catch (\Exception $e) {
            if(DISPLAY_ERROR === true) {
                trigger_error('Data Base Error: ( ' . $e->getMessage() . '). Error Code : ' . $e->getCode() . ' <br />');
            }
            exit();
        }
    }

    public static function getVote($name){
        $vote = self::$_voteData->select(
            self::$_config['votedata']['tableVote'],
            array(self::$_config['votedata']['columnVote']),
            array(self::$_config['votedata']['columnName']=>$name)
        );
        if($vote !== false and isset($vote[0])){
            return $vote[0][self::$_config['votedata']['columnVote']];
        }
    }

    public static function updateVote($name,$price){
        $update = self::$_voteData->update(self::$_config['votedata']['tableVote'],
            array(self::$_config['votedata']['columnVote'].'[-]'=>$price),
            array(self::$_config['votedata']['columnName']=>$name)
        );
        if($update != 0){
            return true;
        }
        return false;
    }

    public static function addVpAccount($name,$price){
        $insert = self::$_voteData->insert(self::$_config['votedata']['tableVote'],array(
            self::$_config['votedata']['columnVote']=>$price,
            self::$_config['votedata']['columnName']=>$name
        ));
        if($insert != 0){
            return true;
        }
        return false;
    }

    public static function updateVp($name,$price){
        $update = self::$_voteData->update(self::$_config['votedata']['tableVote'],
            array(self::$_config['votedata']['columnVote'].'[+]'=>$price),
            array(self::$_config['votedata']['columnName']=>$name)
        );
        if($update != 0){
            return true;
        }
        return false;
    }

    public static function sendSoap($command,$user){
        $client = new \SoapClient(NULL,
            array(
                "location" => "http://".self::$_config['soapConsole']['host'].":".self::$_config['soapConsole']['port'],
                "uri" => "urn:TC",
                'login' => self::$_config['soapConsole']['user'],
                'password' => self::$_config['soapConsole']['pass']
            )
        );

        try {
            $client->executeCommand(new \SoapParam($command, "command"));
            self::soapLog(date('[d-m-Y H:i:s]',time()).'[user( '.$user.' )][Send]: '.$command);
            return true;
        }
        catch (\Exception $e) {
            self::soapLog(date('[d-m-Y H:i:s]',time()).'[ErrorSend]: '.$e->getMessage()."; [user( {$user} )]: $command");
            return false;
        }
    }

    private static function soapLog($msg){
        $msg .= ";\t\n";
        file_put_contents(ROOT.'init/storage/log/'.date('d-m-Y',time()).'.soap.log',$msg,FILE_APPEND | LOCK_EX);
    }
}