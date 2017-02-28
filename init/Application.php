<?php

use \library\View,
    \library\DB as Data,
    \library\Language as Lang;

class Application extends \library\App{


    private function getVotePoints(){
        return (int) Data::getVote(self::$user['name']);
    }

    private function updateVotePoints($price = 0){
        return Data::updateVote(self::$user['name'],$price);
    }

    private function countWin(){
        return Data::$roulette->count('games',array('AND'=>array('id_acc'=>self::$user['id'],'user'=>self::$user['name'],'status'=>0)));
    }

    public static function Captcha(){
        $captcha = new \library\Captcha();
        $captcha->setFont('init/storage/font/');
        $captcha->generation();
        $_SESSION['captcha'] = $captcha->getCode();
        $captcha->run();
    }

    protected function index(){
        if(self::$user === false){
            return View::render('login',array('title'=>Lang::_('app:title:login')));
        }else{
            $category = Data::$roulette->select('category','*');

            return View::render('category',array(
                'user'=>self::$user,
                'admin'=>self::$admin,
                'vp'=>$this->getVotePoints(),
                'win'=>$this->countWin(),
                'category'=> (count($category) > 0 ) ? $category :false,
                'title'=>Lang::_('app:title:index')
            ));
        }
    }

    protected function user_exit(){
        unset($_SESSION['user']);
        if(self::$admin){
            unset($_SESSION['admin']);
        }
        $this->location(APP_PATH.'/');
    }

    protected function login($post){
        if (isset($post['data'])) {
            $post = $this->parseForm($post,'data');
            if (isset($post['login']) and isset($post['pass']) and isset($post['captcha'])) {
                if(isset($_SESSION['captcha']) and $post['captcha'] !== ''){
                    if(strtolower($_SESSION['captcha']) === strtolower($post['captcha'])){
                        if ($this->regex('stringInt', $post['login']) and $this->regex('stringInt', $post['pass'])) {
                            Data::auth();

                            $user = Data::$auth->select('account', array('id', 'username(name)'), array(
                                'AND' => array(
                                    'username' => $post['login'],
                                    'sha_pass_hash' => $this->hashPass($post['login'], $post['pass'])
                                )
                            ));

                            if (isset($user[0])) {
                                Data::characters();

                                if (defined('ADMIN_ID')) {
                                    if (ADMIN_ID === $user[0]['id']) {
                                        $_SESSION['admin'] = true;
                                    }else{
                                        $_SESSION['admin'] = false;
                                    }
                                }

                                $characters = Data::$characters->select('characters', array('guid', 'name'), array('account' => $user[0]['id']));

                                if($_SESSION['admin'] !== false){
                                    $user[0]['chars'] = $characters;
                                    $_SESSION['user'] = $user[0];
                                    return $this->json(array('colors' => 0, 'msg' => Lang::_('app:msg:login'), 'location' => APP_PATH.'/'));
                                }


                                if (is_array($characters) and count($characters) > 0) {

                                    $vp = (int)Data::getVote($user[0]['name']);

                                    if ($vp == null or $vp == 0) {
                                        return $this->json(array('colors' => 1, 'msg' => Lang::_('app:msg:noVp')));
                                    } else {
                                        $user[0]['chars'] = $characters;
                                        $_SESSION['user'] = $user[0];
                                        return $this->json(array('colors' => 0, 'msg' => Lang::_('app:msg:login'), 'location' => APP_PATH.'/'));
                                    }
                                } else {
                                    return $this->json(array('colors' => 1, 'msg' => Lang::_('app:msg:noChar')));
                                }
                            } else {
                                return $this->json(array('colors' => 3, 'msg' => Lang::_('app:msg:noAcc')));
                            }
                        } else {
                            return $this->json(array('colors' => 2, 'msg' => Lang::_('app:msg:inputs')));
                        }
                    }else{
                        return $this->json(array('colors' => 2, 'msg' => Lang::_('app:msg:code')));
                    }
                }else{
                    return $this->json(array('colors' => 2, 'msg' => Lang::_('app:msg:code')));
                }
            }
        }
        return false;
    }

    protected function rouletteRandom($id){
        $vp = $this->getVotePoints();

        $category = Data::$roulette->select('category',array('id','price'),array('id' => $id));
        if(count($category) > 0){
            $price = $category[0]['price'];
            if($vp >= $price){
                $items_category = Data::$roulette->query('SELECT items.id,items.name AS item_name,items.icon,macros.name AS macro_name,macros.macros,items.macro_param,items.tooltip FROM items INNER JOIN macros ON items.id_macros = macros.id WHERE items.id_category = '.$id.' ORDER BY items.id ASC')->fetchAll();
                $items = (is_array($items_category) and count($items_category) > 0) ? $items_category : false;

                if($items !== false){
                    $id = array_rand($items, 1);
                    $game = Data::$roulette->insert('games',array(
                        'id_acc'=>self::$user['id'],
                        'user'=>self::$user['name'],
                        'macros'=>$this->macros_render($items[$id]['macros'],$items[$id]['macro_param']),
                        'item_icon'=>$items[$id]['icon'],
                        'item_name'=>$items[$id]['item_name'],
                        'item_tooltip'=>$items[$id]['tooltip'],
                        'price'=>$price,
                        '#date'=>'NOW()'
                    ));

                    if($game !== false){
                        $this->updateVotePoints($price);
                        return $this->json(array('id' => $id,'color'=>0,'vp'=>($vp - $price),'win'=>$this->countWin(),'name' => $items[$id]['item_name']));
                    }
                    return $this->json(array('msg' => Lang::_('app:msg:error'),'color'=>3));
                }
            }else{
                return $this->json(array('msg' => Lang::_('app:msg:vpMin'),'color'=>2));
            }
        }
    }

    protected function roulette($id){
        $category = Data::$roulette->select('category', array('id','name', 'description','price'), array('id' => $id,'ORDER'=>'id ASC'));
        $category = (count($category) > 0) ? $category[0] : false;
        if ($category !== false) {
            $items = Data::$roulette->select('items', '*', array('id_category' => $id));

                return View::render('roulette', array(
                    'user' => self::$user,
                    'admin' => self::$admin,
                    'vp' => $this->getVotePoints(),
                    'win' => $this->countWin(),
                    'title' => Lang::_('app:title:roulette'),
                    'items' => (count($items) >= 2) ? $items : false,
                    'category' => $category,
                    'js' => true,
                    'style' => true
                ));

        }
        return false;
    }

    protected function WinPage(){
        $games = Data::$roulette->select('games','*',array('AND'=>array('id_acc'=>self::$user['id'],'user'=>self::$user['name'],'status'=>0),'ORDER' => 'date DESC'));

        return View::render('win', array(
            'user' => self::$user,
            'admin' => self::$admin,
            'vp' => $this->getVotePoints(),
            'win'=>$this->countWin(),
            'title' => Lang::_('app:title:win'),
            'games'=>(count($games) > 0 ) ? $games : false,
            'style' => true
        ));
    }

    protected function sendItem($params){
        $id = (is_numeric($params['post']['id'])) ? $params['post']['id'] : false;
        $char = (is_numeric($params['post']['char'])) ? $params['post']['char'] : false;
        if(isset(self::$user['chars'][$char])){
            $game = Data::$roulette->select('games',array('macros'),array('AND'=>array('id_acc'=>self::$user['id'],'user'=>self::$user['name'],'status'=>0,'id'=>$id)));
            if(count($game) > 0){
                $macros = str_replace('{charName}',self::$user['chars'][$char]['name'],$game[0]['macros']);
                if(Data::sendSoap($macros,self::$user['name']) === false){
                    return $this->json(array('msg' => Lang::_('app:msg:itemSendError'),'color'=>3));
                }else{
                    $rows = Data::$roulette->update('games',
                        array(
                            'status'=>1,
                            'id_char'=>self::$user['chars'][$char]['guid'],
                            'name_char'=>self::$user['chars'][$char]['name'],
                            '#date_send'=>'NOW()'
                        ),
                        array('id'=>$id)
                    );

                    if(is_numeric($rows)) {
                        return $this->json(array('msg' => Lang::_('app:msg:itemSend'), 'color' => 0,'win'=>$this->countWin()));
                    }
                }
            }
            return $this->json(array('msg' => Lang::_('app:msg:itemSendError'),'color'=>3));
        }else{
            return $this->json(array('msg' => Lang::_('app:msg:itemSendError'),'color'=>3));
        }
    }

    protected function history(){
        $parser = Data::$roulette->select('parsers',
            array(
                'name',
                'status',
                'name_top',
                'date',
                'date_add',
                'vp'
            ),
            array(
                'AND' => array(
                    'acc_id' => self::$user['id'],
                    'acc_name' => self::$user['name']
                ),
                'ORDER' => 'date_add DESC',
                "LIMIT" => 50
            )
        );

        $pars = ($parser !== false and count($parser) > 0) ? $parser : array();


        $games = Data::$roulette->select('games',
            array(
                'item_icon',
                'item_name',
                'item_tooltip',
                'price',
                'status',
                'date',
                'date_send',
                'name_char'
            ),
            array(
            'AND'=>array(
                'id_acc'=>self::$user['id'],
                'user'=>self::$user['name']
            ),
            'ORDER'=>'date DESC',
            "LIMIT" =>50
        ));


        $game = ($games !== false and count($games) > 0) ? $games : array();

        $parser = '';
        foreach($pars as $p){
            $time_p = strtotime($p['date']);

            if(isset($parser[$time_p])){
                $time_p += 2;
            }

            $parser[$time_p] = array(
                'status'=>$p['status'],
                'price'=>$p['vp'],
                'date'=>date('[d-m-Y] H:i:s',strtotime($p['date'])),
                'name'=>$p['name'],
                'params'=>$p['name_top'],
                'date_add'=>date('[d-m-Y] H:i:s',strtotime($p['date_add']))
            );
        }

        $games = '';
        foreach($game as $g){
            $time = strtotime($g['date']);

            if(isset($games[$time])){
                $time += 2;
            }

            $games[$time] = array(
                'status'=>$g['status'],
                'price'=>$g['price'],
                'date'=>date('[d-m-Y] H:i:s',strtotime($g['date'])),
                'name'=>$g['name_char'],
                'params'=>array(
                    $g['item_icon'],
                    $g['item_name'],
                    $g['item_tooltip'],
                    date('[d-m-Y] H:i:s',strtotime($g['date_send']))
                )
            );
        }


        if(is_array($parser) and count($parser) > 0 and is_array($games) and count($games) > 0){
            $history = $parser + $games;
            krsort($history, SORT_NUMERIC);
        }elseif(is_array($parser) and count($parser) > 0){
            $history = $parser;
            krsort($history, SORT_NUMERIC);
        }elseif(is_array($games) and count($games) > 0){
            $history = $games;
            krsort($history, SORT_NUMERIC);
        }else{
            $history  = false;
        }

        return View::render('history', array(
            'user' => self::$user,
            'admin' => self::$admin,
            'vp' => $this->getVotePoints(),
            'win'=>$this->countWin(),
            'title' => Lang::_('app:title:history'),
            'history'=>$history,
            'style' => true
        ));
    }
}
