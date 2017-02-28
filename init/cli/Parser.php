<?php
namespace cli;

use \library\View;

class Parser extends \library\DB{

    public static $_ARGV;
    protected static $Tops;
    protected static $_loadTop;

    private static $_log = '';

    private static function setLog($log){
        self::$_log .=$log."\n";
    }

    public static function CliRun(){
       // ini_set("memory_limit", "32M");
        set_time_limit(0);

        if(HTTP_RUN_CMD === true and self::$_ARGV === false and isset($_GET['cmd_run'])){
            if(strpos($_GET['cmd_run'],':')){
                $cmd = explode(':',$_GET['cmd_run']);
                if(is_array($cmd)){
                    $count = count($cmd);

                    foreach($cmd as $command){
                        if(!preg_match('/^[a-zA-Z0-9_\.-]+$/', $command)){
                            exit;
                        }
                    }

                    if($count == 2 or $count == 3){
                        self::$_ARGV[0] = '';
                        self::$_ARGV = array_merge(self::$_ARGV,$cmd);
                    }
                }
            }
        }


        if(self::$_ARGV !== false) {
            self::setLog('Initialized Load Parser ['.date('d-m-Y H:i:s',time()).']');
            self::__init();
            self::auth();
            self::characters();
            self::roulette();
            self::voteData();
            self::$Tops = self::$_config['parser'];
            $class = new self();
            $method = '';
            $params = '';


            if(!isset(self::$_config['parser']) or count(self::$_config['parser']) === 0){
                exit("Not found config top sites!\n");
            }

            foreach (self::$_ARGV as $k => $arg) {
                if ($k == 1) {
                    $method = $arg;
                } elseif ($k >= 2) {
                    $params[] = $arg;
                }
            }

            if (method_exists($class, $method)) {
                return $class->$method($params);
            }
        }else{
            View::__Init();
            header("HTTP/1.1 404 Not Found");
            define('APP_PATH','');
            $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            echo View::render('404',array('url'=>$url));
        }
    }

    /**
     * Generation of fail votes:
     * example = randomVoteFile [lines] [filename]
     * default = randomVoteFile // create default-vote.txt 10000 lines
     * testCmd = randomVoteFile 10000 mmotop.txt
     */

    protected function randomVoteFile($lines){
        $line = (is_numeric($lines[0])) ? $lines[0] : 10000;
        $file = (isset($lines[1])) ? $lines[1] : 'default-vote.txt';

        $char_names = self::$characters->select('characters',array('name'));
        $chars = '';
        if(is_array($char_names) and count($char_names) > 0){
            foreach($char_names as $names){
                $chars[] = $names['name'];
            }
        }

        if(!is_file(ROOT.$file)){
            $data = '';
            for ($i = 1; $i <= $line; $i++) {
                echo $i."\n";
                $vote_id = 10000 + (11 * $i) + 99;
                $time = (time() - 2629743) + 400 * $i;
                $date = date('d.m.Y H:i:s', $time);
                $vote_ip = rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
                $vote_char = $chars[array_rand($chars, 1)];
                $vote_v = rand(1, 2);
                $data .= "$vote_id\t$date\t$vote_ip\t$vote_char\t$vote_v\n";
            }

            file_put_contents(ROOT.$file,$data);
            die('the file generated for the test :)');
        }else{
            die('file already generated !');
        }
    }

    /**
     * Parsing file votes:
     * example = parserLoad [top name]
     * testCmd = parserLoad mmotop
     */

    protected function parserLoad($name){
        $name = (isset($name[0])) ? $name[0] : false;
        if($name !== false and isset(self::$Tops[$name])){
            self::$_loadTop = $name;
            $file = self::$Tops[$name]['file'];
            $vp = self::$Tops[$name]['vote'];

            $hash_list = self::$roulette->select('parsers',array('hash'));

            $hash = false;

            if(is_array($hash_list) and count($hash_list) > 0){
                $dbHash = array();
                foreach($hash_list as $hs){
                    $dbHash[] = $hs['hash'];
                }

                $hash = array_fill_keys($dbHash, '');
            }

            if($data = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) {

                $date_start = strtotime(date('d.m.Y', time()));
                $date_end = $date_start + 86400;

                $votes = '';

                if (is_array($data) and count($data) > 0) {
                    foreach ($data as $key => $line) {
                        $data_line = str_getcsv($line, "\t");
                        if (count($data_line) === 5) {
                            $real_time = strtotime($data_line[1]);
                            if ($real_time >= $date_start and $real_time <= $date_end) {
                                $hash_gen = md5(implode(':', $data_line));
                                $votes[$hash_gen] = array(
                                    'name_top'=>$name,
                                    'vote_id'=>$data_line[0],
                                    'date'=>date('Y-m-d H:i:s',$real_time),
                                    'ip'=>$data_line[2],
                                    'name'=>$data_line[3],
                                    'vp'=>(int) $data_line[4] * $vp,
                                    'hash'=>$hash_gen
                                );
                            }
                        }else{
                            self::setLog('line error!'.$data_line[0]);
                        }
                    }

                    $white_list = ($hash !== false) ? array_values(array_diff_key($votes, $hash)) : array_values($votes);
                    $rows = array();

                    if(count($white_list) > 0){
                        $rows = $this->insertVote($white_list);
                    }

                    $rows = (is_array($rows)) ? count($rows) : 0;
                    self::setLog('Created parse votes rows: '.$rows);

                    $names_list = self::$roulette->query("SELECT id,name FROM parsers WHERE DATE(date) = CURDATE() AND status = 0 AND name_top = '{$name}'")->fetchAll(\PDO::FETCH_ASSOC);

                    if(is_array($names_list) and count($names_list) > 0){
                        foreach($names_list as $key=>$list_name){
                            $acc_id = self::$characters->select('characters',array('account'),array('name'=>$list_name['name']));
                            if(is_array($acc_id) and count($acc_id) > 0){
                                $name_acc = self::$auth->select('account',array('id','username'),array('id'=>$acc_id[0]['account']));
                                if(is_array($name_acc) and count($name_acc) > 0){
                                   $rows = self::$roulette->update('parsers',array('acc_id'=>$name_acc[0]['id'],'acc_name'=>$name_acc[0]['username']),array('id'=>$list_name['id']));
                                    if($rows){
                                        self::setLog("created name account {$list_name['id']}:{$name_acc[0]['username']}");
                                    }
                                }else{
                                   self::setLog('Account not Found id: '.$acc_id[0]['account']);
                                }
                            }else{
                                self::setLog('Character not Found name: '.$list_name['name']);
                            }
                        }
                    }else{
                        self::setLog("Not 'null' accounts");
                    }

                    $status = self::$roulette->query("SELECT id,acc_name,vp FROM parsers WHERE DATE(date) = CURDATE() AND status = 0 AND name_top = '{$name}'")->fetchAll(\PDO::FETCH_ASSOC);
                    if(is_array($status) and count($status) > 0){
                        foreach($status as $stat){
                            $name_vote = self::$roulette->select('vote',array('id'),array('name'=>$stat['acc_name']));
                            if(count($name_vote) > 0){
                                if(self::updateVp($stat['acc_name'],$stat['vp'])){
                                    self::setLog("Update Account: {$stat['acc_name']} (+ {$stat['vp']})");
                                }
                            }else{
                                if(self::addVpAccount($stat['acc_name'],$stat['vp'])){
                                    self::setLog("Create Account: {$stat['acc_name']} (+ {$stat['vp']})");
                                }
                            }
                            self::$roulette->update('parsers',array('status'=>1,'#date_add'=>'NOW()'),array('id'=>$stat['id']));
                        }
                    }else{
                        self::setLog("no of enrolled voters");
                    }

                }else{
                    self::setLog('Votes NULL!');
                }

            }else{
                self::setLog('No file or it is empty: '.$file);
            }
        }else{
            self::setLog('Not Found top '.$name.'!');
        }

        self::setLog('Stop Parser ['.date('d-m-Y H:i:s',time()).']');
        $this->saveLog();
        die("Parser Stop... \n");
    }

    private function saveLog(){
        self::$_log .= "--------------------------------------------------------------\t\n";
        file_put_contents(ROOT.'init/storage/log/'.date('d-m-Y',time()).'.'.self::$_loadTop.'.parser.log',self::$_log,FILE_APPEND | LOCK_EX);
    }

    private function insertVote($lines){
        return self::$roulette->insert('parsers',$lines);
    }
}