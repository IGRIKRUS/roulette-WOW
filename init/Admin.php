<?php
use \library\View,
    \library\DB as Data,
    \library\Language as Lang;

class Admin extends \library\App{

    protected function adminHome(){
        $statistic = Data::$roulette->query("SELECT DATE_FORMAT(date,'%d-%m-%Y' ) AS date_day,DATE_FORMAT(date_send,'%d-%m-%Y' ) AS date_sends,price,status FROM games WHERE DATE_FORMAT(date, '%Y%m') = DATE_FORMAT(NOW(), '%Y%m') ORDER BY date")->fetchAll(\PDO::FETCH_ASSOC);

        $line = false;

        $date = date('d-m-Y',time());
        $countWin = count($statistic);
        $countSend = 0;
        $countPrice = 0;
        $DeyCountSend = 0;
        $DayCountPrice = 0;

        if(is_array($statistic) and $countWin > 0){
            foreach($statistic as $stats){

                if($stats['status'] == 1){
                    $countSend += 1;
                }

                if(strtotime($stats['date_sends']) === strtotime($date) and $stats['status']){
                    $DeyCountSend += 1;
                }

                if((int) $stats['price'] != 0){
                    $countPrice += $stats['price'];
                }

                if(isset($line[$stats['date_day']])){
                    $line[$stats['date_day']] = $line[$stats['date_day']] + $stats['price'];
                }else{
                    $line[$stats['date_day']] = $stats['price'];
                }
            }

            $line = $this->stat($line);

            $DayCountPrice = (isset($line[$date])) ? $line[$date] : 0;
        }

        return View::render('admin/index',array(
                'user'=>self::$user,
                'line'=>$line,
                'countWin'=>$countWin,
                'countSend'=>$countSend,
                'countPrice'=>$countPrice,
                'DayCountPrice'=>$DayCountPrice,
                'DeyCountSend'=>$DeyCountSend
        ));
    }

    private function stat($line){
        if(is_array($line) and count($line) > 0){
            $keys = array_keys($line);

            foreach ($keys as $date) {
                $t = explode('-',$date);
                $n = ($t[0] < 10) ? '0'.($t[0] + 1) : $t[0] + 1;
                $time = $n .'-'.$t[1].'-'.$t[2];
                if(!isset($line[$time])){
                    $line[$time] = 0;
                }
            }

            ksort($line);

            return $line;
        }else{
            return array(date('d-m-Y',time())=>0);
        }
    }

    protected function adminContent($type){
        $page = $type['type'];
        $param = $type['param'];
        switch($page){
            case 'category':
                if(is_numeric($param)){
                    $category = Data::$roulette->select('category','*',array('id'=>$param));
                    if(count($category) > 0) {
                        return View::render('admin/category', array(
                            'user' => self::$user,
                            'type' => 'show',
                            'category'=>$category[0]
                        ));
                    }
                    return false;
                }elseif($param === 'add'){
                    return View::render('admin/category',array(
                        'user'=>self::$user,
                        'type'=>'add'
                    ));
                }elseif($param === 'index'){
                    $category = Data::$roulette->select('category','*');
                    return View::render('admin/category',array(
                        'user'=>self::$user,
                        'type'=>'index',
                        'category'=>(is_array($category) and count($category) > 0) ? $category : false
                    ));
                }else{
                    return false;
                }
                break;

            case 'macros':
                if(is_numeric($param)){
                    $macros = Data::$roulette->select('macros','*',array('id'=>$param));
                    if(count($macros) > 0) {
                        return View::render('admin/macros', array(
                            'user' => self::$user,
                            'type' => 'show',
                            'macros'=>$macros[0]
                        ));
                    }
                    return false;
                }elseif($param === 'add'){
                    return View::render('admin/macros',array(
                        'user'=>self::$user,
                        'type'=>'add'
                    ));
                }elseif($param === 'index'){
                    $macros = Data::$roulette->select('macros','*');
                    $macros = (is_array($macros) and count($macros) > 0) ? $macros : false;
                    return View::render('admin/macros',array(
                        'user'=>self::$user,
                        'type'=>'index',
                        'macros'=>$macros
                    ));
                }else{
                    return false;
                }
                break;

            case 'item':
                if($param === 'add') {
                    $macros = Data::$roulette->select('macros', array('id', 'name'));
                    $category = Data::$roulette->select('category', array('id', 'name'));

                    return View::render('admin/item', array(
                        'user' => self::$user,
                        'type' => 'add',
                        'category' => (count($category) > 0) ? $category : false,
                        'macros' => (count($macros) > 0) ? $macros : false,
                    ));

                }elseif(is_numeric($param)){

                    $macros = Data::$roulette->select('macros', array('id', 'name'));
                    $category = Data::$roulette->select('category', array('id', 'name'));
                    $item = Data::$roulette->select('items', '*',array('id'=>$param));

                    return View::render('admin/item', array(
                        'user' => self::$user,
                        'type' => 'edit',
                        'category' => (count($category) > 0) ? $category : false,
                        'macros' => (count($macros) > 0) ? $macros : false,
                        'item'=>(count($item[0]) > 0) ? $item[0] : false
                    ));


                }else{
                    return false;
                }
                break;
            case 'list':
                if(is_numeric($param)){
                    $items_category = Data::$roulette->query('SELECT items.id,items.name AS item_name,items.icon,macros.name AS macro_name,macros.macros,items.macro_param,items.tooltip FROM items INNER JOIN macros ON items.id_macros = macros.id WHERE items.id_category = '.$param)->fetchAll();
                    $list_item = (is_array($items_category) and count($items_category) > 0) ? $items_category : false;
                    if($list_item != false){
                        foreach($list_item as $key=>$items){
                            $list_item[$key]['macros'] = $this->macros_render($items['macros'],$items['macro_param']);
                        }

                    }

                    return View::render('admin/item',array(
                        'user'=>self::$user,
                        'type'=>'list',
                        'items'=>$list_item,
                    ));

                }else{
                    return false;
                }
                break;
            case 'logs':
                $path = ROOT . 'init/storage/log/';
                $logs = array_values(array_diff(scandir($path), array('.', '..','index.html')));
                $logs = (is_array($logs) and count($logs) > 0) ? $logs : false;


                if($param === 'files') {
                    return View::render('admin/logs', array(
                        'user' => self::$user,
                        'logs' => $logs
                    ));
                }elseif(is_numeric($param) and isset($logs[$param])){
                    header("Content-Type: text/plain");
                    return file_get_contents($path.$logs[$param]);
                }else{
                    return false;
                }
                break;
            default:return false;
                break;
        }
    }

    protected function deleteAjax($params){
        switch($params['type']){
            case 'item':
                if(is_numeric($params['param'])) {
                    $result = Data::$roulette->delete('items', array('id' => $params['param']));

                    if (is_numeric($result) and $result > 0) {
                        return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:delete')));
                    }
                    return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                }
                break;
            case 'category':
                if(is_numeric($params['param'])) {
                    $result = Data::$roulette->delete('category', array('id' => $params['param']));

                    if (is_numeric($result) and $result > 0) {
                        Data::$roulette->delete('items', array('id_category' => $params['param']));
                        return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:delete')));
                    }
                    return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                }
                break;
            case 'macros':
                  if(is_numeric($params['param'])) {

                      $result = Data::$roulette->select('items', array('id'),array('id_macros'=>$params['param']));

                      if(is_array($result) and count($result) === 0){

                          $resultMacro = Data::$roulette->delete('macros', array('id' => $params['param']));

                          if (is_numeric($resultMacro) and $resultMacro > 0) {
                              return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:delete')));
                          }
                          return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                      }else{
                          return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:deleteMacros')));
                      }
                  }
                break;
            default:return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                break;
        }
    }

    protected function modifyAjax($params){
        $page = $params['type'];
        $param = $params['param'];
        $post = $this->parseForm($params['post'],'data');

        switch ($page) {
            case 'category':
                if(is_numeric($param)){
                    if(isset($post['icon']) and isset($post['name']) and isset($post['description']) and isset($post['price'])){
                        if($this->regex('stringInt',$post['icon']) === false  ||
                            $post['name'] === '' ||
                            $post['description'] === '' ||
                            is_numeric($post['price']) === false ){
                            return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:valid')));
                        }else{
                            $upd = Data::$roulette->update('category',$post,array('id'=>$param));

                            if($upd !== false){
                                return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:edit'),'location'=>APP_PATH.'/admin/category/index'));
                            }else{
                                return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                            }
                        }
                    }
                }elseif($param === 'add'){
                    if(isset($post['icon']) and isset($post['name']) and isset($post['description']) and isset($post['price'])){
                        if($this->regex('stringInt',$post['icon']) === false  ||
                            $post['name'] === '' ||
                            $post['description'] === '' ||
                            is_numeric($post['price']) === false ){
                            return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:valid')));
                        }else{
                            $id = Data::$roulette->insert('category',array(
                                "name" =>  $this->stringReplace($post['name']),
                                "icon" => $post['icon'],
                                "description" => $this->stringReplace($post['description']),
                                "price" => (int) $post['price']
                            ));

                            if($id !== false){
                                return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:add')));
                            }else{
                                return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                            }
                        }
                    }
                }else{
                    return false;
                }
                break;

            case 'macros':
                if(is_numeric($param)){
                    if(isset($post['name']) and isset($post['macros']) and $post['name'] != '' and $post['macros'] != ''){

                        $upd = Data::$roulette->update('macros',$post,array('id'=>$param));

                        if($upd !== false){
                            return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:edit'),'location'=>APP_PATH.'/admin/macros/index'));
                        }else{
                            return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                        }

                    }else{
                        return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:valid')));
                    }
                }elseif($param === 'add'){
                    if(isset($post['name']) and isset($post['macros']) and $post['name'] != '' and $post['macros'] != ''){

                        $id = Data::$roulette->insert('macros',array(
                            "name" =>  $post['name'],
                            "macros" => $post['macros']
                        ));

                        if($id != false){
                            return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:add')));
                        }else{
                            return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                        }

                    }else{
                        return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:valid')));
                    }
                }elseif($param === 'get'){
                    if(isset($post['macro']) and is_numeric($post['macro'])) {
                        $macros = Data::$roulette->select('macros', array('macros'), array('id' => $post['macro']));
                        $macros = (count($macros) > 0) ? $macros[0]['macros'] : false;
                        if($macros !== false){
                            if(preg_match_all('/#(.*?)#/',$macros,$match)){
                                return $this->json(array_unique($match[0]));
                            }
                        }
                        return false;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
                break;

            case 'item':
                if($param === 'add') {
                    if (isset($post['macros']) and
                        isset($post['category']) and
                        isset($post['icon']) and
                        isset($post['name']) and
                        isset($post['tooltip']) and
                        $post['macros'] != '' and
                        $post['category'] != '' and
                        $post['icon'] != '' and
                        $post['name'] != '' and
                        $post['tooltip'] != ''
                    ) {
                        $macro_params = '';
                        $col = '';
                        foreach ($post as $key => $val) {
                            if (preg_match('/#(.*?)#/', $key)) {
                                $macro_params[$key] = $val;
                            } else {
                                $col[$key] = $val;
                            }
                        }

                        if ($macro_params === '') {
                            return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:macros')));
                        } else {
                            $item = Data::$roulette->insert('items', array(
                                "id_category" => $col['category'],
                                "id_macros" => $col['macros'],
                                "macro_param" => serialize($macro_params),
                                "name" => $col['name'],
                                "icon" => $col['icon'],
                                "tooltip" => stripslashes(htmlspecialchars($col['tooltip']))
                            ));

                            if ($item !== false) {
                                return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:add')));
                            } else {
                                return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                            }
                        }
                    } else {
                        return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:valid')));
                    }
                }elseif(is_numeric($param)){
                    if(isset($post['macros']) and
                        isset($post['category']) and
                        isset($post['icon']) and
                        isset($post['name']) and
                        isset($post['tooltip']) and
                        $post['macros'] != '' and
                        $post['category'] != '' and
                        $post['icon'] != '' and
                        $post['name'] != '' and
                        $post['tooltip'] != ''
                    ){
                        $macro_params = '';
                        $col = '';
                        foreach($post as $key=>$val){
                            if(preg_match('/#(.*?)#/',$key)){
                                $macro_params[$key] = $val;
                            }else{
                                $col[$key] = $val;
                            }
                        }

                        if($macro_params === ''){
                            return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:macros')));
                        }else{
                            $item = Data::$roulette->update('items',array(
                                "id_category" =>  $col['category'],
                                "id_macros" =>  $col['macros'],
                                "macro_param" =>  serialize($macro_params),
                                "name" =>   $col['name'],
                                "icon" =>   $col['icon'],
                                "tooltip" =>  stripslashes(htmlspecialchars($col['tooltip']))
                            ),array('id'=>$param));

                            if($item !== false){
                                return $this->json(array('color' => 0, 'msg' => Lang::_('admin:msg:edit'),'hideform'=>true));
                            }else{
                                return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:error')));
                            }
                        }
                    }else{
                        return $this->json(array('color' => 3, 'msg' => Lang::_('admin:msg:valid')));
                    }
                }else{
                    return false;
                }
                break;
            default:
                return false;
                break;
        }
    }
}