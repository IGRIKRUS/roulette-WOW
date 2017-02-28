<?php
use \library\View,
    \library\Language as Lang;

class Install extends \library\App{

    protected static $_config;

    private static function existsConf(){
        self::$_config = (isset($_SESSION['config'])) ? $_SESSION['config'] : '';
        if(is_file(ROOT.'init/config/config.php')){
           require_once ROOT.'init/config/config.php';
        }else{
            define('INSTALL',true);
        }
    }

    protected static function testConnectDB($config){
        $config['dbname'] = (isset($config['dbname'])) ? $config['dbname'] : '';
         try{
             $db = new \medoo(array(
                     'database_type' => 'mysql',
                     'database_name' => $config['dbname'],
                     'server' => $config['host'],
                     'username' => $config['user'],
                     'password' => $config['pass'],
                     'charset' => 'utf8',
                     'port' => $config['port'],
                     'option' => array(
                         \PDO::ATTR_CASE => \PDO::CASE_NATURAL
                     ))
             );
             return $db;
         }catch (\Exception $e){
             return $e->getMessage();
         }
    }

    public static function getPage($page,$args = null){
        self::existsConf();
        if(defined('INSTALL') and INSTALL === true) {
            self::__Init();
            $method = (method_exists(self::$init, $page)) ? true : false;
            if ($method === true) {
                return self::$init->$page($args);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    protected function start(){
        $php = (version_compare(PHP_VERSION, "5.3.0", ">=")) ? array('success','Yes') : array('danger','No');
        $config = (is_writable(ROOT.'init/config/')) ? array('success','Yes') : array('danger','No');
        $cache = (is_writable(ROOT.'init/storage/cache/template/')) ? array('success','Yes') : array('danger','No');
        $log = (is_writable(ROOT.'init/storage/log/')) ? array('success','Yes') : array('danger','No');



        return View::render('install/index',array(
            'php_version'=>$php,
            'config_dir'=>$config,
            'cache'=>$cache,
            'log'=>$log,
        ));
    }

    protected function AjaxPageInstall($params){
        $page = $params['step'];
        $agrs = $params['param'];

        switch($page){
            case 'config':
                if($agrs === 'roulette') {
                    if (isset(self::$_config['roulette']) and is_array(self::$_config['roulette'])) {
                        return $this->message('success', 'roulette config complete...', '/install/config/wowdata', Lang::_('formsInstall:btnNext'));
                    } else {
                        return View::render('install/roulette');
                    }

                }elseif($agrs === 'wowdata'){
                    if (isset(self::$_config['wowdata']) and is_array(self::$_config['wowdata'])) {
                        return $this->message('success', 'wowdata config complete...', '/install/config/votedata', Lang::_('formsInstall:btnNext'));
                    } else {
                        return View::render('install/wowdata');
                    }
                }elseif($agrs === 'votedata'){
                    if (isset(self::$_config['votedata']) and is_array(self::$_config['votedata'])) {
                        return $this->message('success', 'votedata config complete...', '/install/config/soap', Lang::_('formsInstall:btnNext'));
                    } else {
                        return View::render('install/votedata');
                    }
                }elseif($agrs === 'soap'){
                    if (isset(self::$_config['soapConsole']) and is_array(self::$_config['soapConsole'])) {
                        return $this->message('success', 'soapConsole config complete...', '/install/config/parser', Lang::_('formsInstall:btnNext'));
                    } else {
                        return View::render('install/soap');
                    }
                }elseif($agrs === 'parser'){
                    return View::render('install/parser');
                }elseif($agrs === 'status'){
                    $stat = '';

                    $pageStatus = array(
                        'roulette'=>'/install/config/roulette',
                        'wowdata'=>'/install/config/wowdata',
                        'votedata'=>'/install/config/votedata',
                        'soap'=>'/install/config/soap',
                        'parser'=>'/install/config/parser',
                        'admin'=>'/install/admin/user',
                        'tables'=>'/install/database/sql'
                    );

                    if(self::$_config !== '') {
                        foreach (self::$_config as $key => $val) {
                            if ($key === 'soapConsole') {
                                $key = 'soap';
                            }

                            if (isset($pageStatus[$key])) {
                                $url = $pageStatus[$key];
                                if (is_array($val)) {
                                    $stat .=' <li class="treeview"><a href="#"><i class="fa fa-check text-success"></i><span id="stat" data-type="' . $url . '">' . $key . '</span><span class="label pull-right"><i id="remove" data-type="' . $key . '" class="fa fa-eraser"></i></span></a></li>';
                                } else {
                                    $stat .=' <li class="treeview"><a href="#"><i class="fa fa-remove text-danger"></i><span id="stat" data-type="' . $url . '">' . $key . '</span><span class="label pull-right"><i id="remove" data-type="' . $key . '" class="fa fa-eraser"></i></span></a></li>';
                                }
                            }
                        }
                    }

                    return $stat;
                }else{
                    return false;
                }
                break;
            case 'admin':
                if (isset(self::$_config['wowdata'])) {

                    if(isset(self::$_config['admin']) and self::$_config['admin'] !== ''){
                        return $this->message('success',Lang::_('install:msg:admin',array('{admin}'=>self::$_config['admin']['name'])),'/install/database/sql',Lang::_('formsInstall:btnInstallTable'));
                    }

                    $db = self::testConnectDB(self::$_config['wowdata']);
                    if(is_object($db)){
                        return View::render('install/admin');
                    }else{
                        unset(self::$_config['wowdata']);
                        return $this->message('danger','DB ERROR:'.$db,'/install/config/wowdata',Lang::_('formsInstall:btnEdit'));
                    }
                }else{
                    return $this->message('danger',Lang::_('install:msg:serverCfg'),'/install/config/wowdata',Lang::_('formsInstall:btnEdit'));
                }
                break;
            case 'database':
                if(isset(self::$_config['tables']) and is_array(self::$_config['tables'])){
                    return $this->message('success', 'tables install complete...', '/install/file/config', Lang::_('formsInstall:btnNext'));
                }else{
                    return View::render('/install/tables');
                }
                break;
            case 'file':
                return $this->consturctConfigFile();
                break;
            default:return false;
                break;
        }
    }

    private function message($type,$text,$url,$button_name){
        return '<section class="content"><div class="box box-primary box-body"> <p class="text-'.$type.'">'.$text.'</p> <button id="next" type="button" class="btn btn-primary" data-url="'.$url.'">'.$button_name.'</button></div></section>';
    }

    private function msg($type,$text,$buttons = ''){
        return '<section class="content"><div class="box box-primary box-body"><p class="text-'.$type.'">'.$text.'</p>'.$buttons.'</div></section>';
    }

    protected function AjaxSend($data){
        $step = $data['step'];
        $post = $data['post'];

        switch($step){
            case 'roulette':
                if(array_search('',$post) !== false){
                    return $this->message('danger',Lang::_('install:msg:inputEmpty'),'/install/config/roulette',Lang::_('formsInstall:btnEdit'));
                }

                $db = self::testConnectDB($post);
                if(!is_object($db)){
                    return $this->message('danger','ERROR CONNECTION DB: '.$db,'/install/config/roulette',Lang::_('formsInstall:btnEdit'));
                }else{
                    self::$_config['roulette'] = $post;
                    return $this->message('success','roulette config complete...','/install/config/wowdata',Lang::_('formsInstall:btnNext'));
                }

                break;
            case 'wowdata':
                if(array_search('',$post) !== false){
                    return $this->message('danger',Lang::_('install:msg:inputEmpty'),'/install/config/wowdata',Lang::_('formsInstall:btnEdit'));
                }

                $post['dbname'] = $post['auth'];
                $auth = self::testConnectDB($post);
                $post['dbname'] = $post['characters'];
                $characters = self::testConnectDB($post);

                if(is_object($auth) and is_object($characters)){
                    unset($post['dbname']);
                    self::$_config['wowdata'] = $post;
                    return $this->message('success','wowdata config complete...','/install/config/votedata',Lang::_('formsInstall:btnNext'));
                }else{
                    $auth = is_object($auth) ? 'connected': $auth;
                    $characters = is_object($characters) ? 'connected': $characters;
                    return $this->message('danger','ERROR CONNECTION DB <br> auth: '.$auth.'<br> characters: '.$characters,'/install/config/wowdata',Lang::_('formsInstall:btnEdit'));
                }
                break;
            case 'votedata':

                if(array_search('',$post) !== false){
                    return $this->message('danger',Lang::_('install:msg:inputEmpty'),'/install/config/votedata',Lang::_('formsInstall:btnEdit'));
                }

                if(array_search('',$post['db']) !== false){
                    return $this->message('danger',Lang::_('install:msg:inputEmpty'),'/install/config/votedata',Lang::_('formsInstall:btnEdit'));
                }

                $db = self::testConnectDB($post['db']);

                if(is_object($db)){
                    if(isset($post['install'])){
                        self::$_config['votedata'] = $post;
                        return $this->message('success', 'votedata config complete...', '/install/config/soap', Lang::_('formsInstall:btnNext'));
                    }else{
                        $table  = $db->select($post['tableVote'],array(
                            $post['columnVote'],
                            $post['columnName']
                        ));
                        if($table === false){
                            return $this->message('danger','vote table not found in the database: '.$post['db']['dbname'],'/install/config/votedata',Lang::_('formsInstall:btnEdit'));
                        }
                        self::$_config['votedata'] = $post;
                        return $this->message('success', 'votedata config complete...', '/install/config/soap', Lang::_('formsInstall:btnNext'));
                    }
                }else{
                    return $this->message('danger','ERROR CONNECTION DB: '.$db,'/install/config/votedata',Lang::_('formsInstall:btnEdit'));
                }

                break;
            case 'soap':
                if(array_search('',$post) !== false){
                    return $this->message('danger',Lang::_('install:msg:inputEmpty'),'/install/config/soap',Lang::_('formsInstall:btnEdit'));
                }else{
                    self::$_config['soapConsole'] = $post;
                    return $this->message('success', 'soapConsole config complete...', '/install/config/parser', Lang::_('formsInstall:btnNext'));
                }
                break;
            case 'parser':
                if(array_search('',$post) !== false){
                    return $this->message('danger',Lang::_('install:msg:inputEmpty'),'/install/config/parser',Lang::_('formsInstall:btnEdit'));
                }else{
                    self::$_config['parser'][$post['topName']] = array('file'=>$post['file'],'vote'=>$post['vote']);
                    return View::render('install/parser',array('msg'=>Lang::_('install:msg:addTop').' '.$post['topName']));
                }
                break;
            case 'admin':
                if(array_search('',$post) !== false){
                    return $this->message('danger',Lang::_('install:msg:inputEmpty'),'/install/admin/user',Lang::_('formsInstall:btnEdit'));
                }else{
                    $data = self::$_config['wowdata'];
                    $data['dbname'] = self::$_config['wowdata']['auth'];
                    $db = self::testConnectDB($data);

                    if(is_object($db)){
                       $user = $db->select('account',array('id','username(name)'),array('id'=>(int)$post['ID']));
                      if(is_array($user) and count($user) > 0){
                          self::$_config['admin'] = $user[0];
                          return $this->message('success',Lang::_('install:msg:admin',array('{admin}'=>self::$_config['admin']['name'])),'/install/database/sql',Lang::_('formsInstall:btnInstallTable'));
                      }else{
                          return $this->message('danger',Lang::_('install:msg:accountEmpty'),'/install/admin/user',Lang::_('formsInstall:btnEmpty'));
                      }
                    }else{
                        return $this->message('danger','ERROR DB CONNECT:'.$db,'/install/admin/user',Lang::_('formsInstall:btnEmpty'));
                    }
                }
                break;
            case 'clear':
                $data = $post['data'];

                if($data === 'soap'){
                    $data = 'soapConsole';
                }

                if(isset(self::$_config[$data])){
                    self::$_config[$data] = '';
                    return $this->json(array('msg'=>Lang::_('install:msg:delete'),'color'=>0));
                }
                break;
            case 'tables':
                self::$_config['tables'] = array();
                $install = array(
                    'category'=>$this->installSql(self::$_config['roulette'],'category'),
                    'games'=>$this->installSql(self::$_config['roulette'],'games'),
                    'items'=>$this->installSql(self::$_config['roulette'],'items'),
                    'macros'=>$this->installSql(self::$_config['roulette'],'macros'),
                    'parsers'=>(isset(self::$_config['parser'])) ? $this->installSql(self::$_config['roulette'],'parsers') : array('info','install false'),
                    'vote'=>(isset(self::$_config['votedata']['install'])) ? $this->installSql(self::$_config['votedata']['db'],'vote') : array('info','install false'),
                    'data'=>(isset($post['install'])) ? $this->installSql(self::$_config['roulette'],'data') : array('info','install false')
                );

                $msg = '';

                foreach($install as $key=>$status){
                    if($status[0] === 'danger'){
                        unset(self::$_config['tables']);
                    }
                    $msg .= "<p class='text-{$status[0]}'>Table {$key}:{$status[1]}</p>";///$this->msg($status[0],'Table '.$key.':'.$status[1]);
                }

                $btn = '<button id="next" type="button" class="btn btn-primary" data-url="/install/database/sql">'.Lang::_('formsInstall:btnPrev').'</button><button id="next" type="button" class="btn btn-primary pull-right" data-url="/install/file/config">'.Lang::_('formsInstall:btnNext').'</button>';


                return $this->msg('info',$msg,$btn);
                break;
            default:return false;
                break;
        }
    }

    private function getSQl($file){
        if(is_file(ROOT.'init/storage/install/sql/'.$file.'.sql')){
            return file_get_contents(ROOT.'init/storage/install/sql/'.$file.'.sql');
        }else{
            return false;
        }
    }

    private function installSql($connect,$name){
        $db  = self::testConnectDB($connect);
        if(is_object($db)){
            $sql = $this->getSQl($name);
            if($sql === false){
                return array('danger','Not Found install file '.$name.'.sql');
            }else{
                    $db->query($sql);
                    if($name === 'vote'){
                        $table = self::$_config['votedata']['tableVote'];
                        $colName = self::$_config['votedata']['columnName'];
                        $colVote = self::$_config['votedata']['columnVote'];
                        $db->insert($table,array(
                            "id"=>1,
                            "{$colName}"=>self::$_config['admin']['name'],
                            "{$colVote}"=>999
                        ));
                    }
                    $error = $db->error();
                    if($error[2] != null){
                        return array('danger','ERROR:'.$error[2]);
                    }
                    return array('success','installed...');
            }
        }else{
            return array('danger','ERROR DB:'.$db);
        }
    }

    private function lineCollect($config){
        if(is_array($config) and count($config) > 0){
            $cfg = '';
            foreach($config as $key=>$val){
                if($key !== 'lang') {
                    $cfg .= "\t'{$key}'=>'{$val}',\n";
                }
            }
            return $cfg;
        }
    }

    private function consturctConfigFile(){

        $file_temp = ROOT.'init/storage/install/config/config.dist.temp';
        $save_dir = ROOT.'init/config/config.php';

        $admin = self::$_config['admin']['id'];
        $lang = self::$_config['roulette']['lang'];
        $roulette = $this->lineCollect(self::$_config['roulette']);
        $wowdata = $this->lineCollect(self::$_config['wowdata']);
        if(isset(self::$_config['votedata']['install'])) unset(self::$_config['votedata']['install']);
        $vote = self::$_config['votedata'];
        $votedata_db = $this->lineCollect(self::$_config['votedata']['db']);
        unset($vote['db']);
        $votedata_vote = $this->lineCollect($vote);
        $soapConsole = $this->lineCollect(self::$_config['soapConsole']);
        $parser = '';
        if(isset(self::$_config['parser']) and is_array(self::$_config['parser']) and count(self::$_config['parser'])>0){
            foreach(self::$_config['parser'] as $key=>$val){
                $parser .="\t'{$key}'=>array(\n
                \t'file'=>'{$val['file']}',\n
                \t'vote'=>'{$val['vote']}'
                ),\n";
            }
        }

        $data = array(
            '{teg}'=>'<?php',
            '{admin_id}'=>$admin,
            '{lang}'=>$lang,
            '{roulette}'=>$roulette,
            '{wowdata}'=>$wowdata,
            '{votedata_db}'=>$votedata_db,
            '{votedata_vote}'=>$votedata_vote,
            '{soapConsole}'=>$soapConsole,
            '{parser}'=>$parser
        );

        if(is_file($file_temp)){
            $config = str_replace(array_keys($data),array_values($data),file_get_contents($file_temp));
            file_put_contents($save_dir,$config);

            if(isset($_SESSION['lang'])){
                unset($_SESSION['lang']);
            }

            if(is_file($save_dir)){
                unset($_SESSION['config']);
                self::$_config = '';
                return $this->msg('success','create config file ...','<a  href="'.APP_PATH.'/" class="btn btn-primary">'.Lang::_('install:msg:linkTo').'</a>');
            }else{
                return $this->message('danger','error create config file!','/install/database/sql',Lang::_('formsInstall:btnPrev'));
            }
        }
    }

    public function __destruct(){
        $_SESSION['config'] = self::$_config;
    }
}