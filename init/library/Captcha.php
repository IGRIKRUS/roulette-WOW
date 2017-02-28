<?php
namespace library;

class Captcha {

    private $_options = array(
        'width'=>240,
        'height'=>70,
        'font_size'=>16,
        'captcha_nums'=>5,
        'captcha_font_nums'=>50,
    );

    private $_characters = array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','2','3','4','5','6','7','9');
    private $_colors = array('10','30','50','70','90','110','130','150','170','190','210');

    private $_cod;

    private $_src;

    private $_pathFont;

    public function setFont($dir){
        $this->_pathFont = ROOT.$dir;
    }

    public function generation(){
        $src = imagecreatetruecolor($this->_options['width'],$this->_options['height']);
        $fon = imagecolorallocate($src,255,255,255);
        imagefill($src,0,0,$fon);

        $fonts = array();
        $dir=opendir($this->_pathFont);
        while($fontName = readdir($dir))
        {
            if($fontName != "." && $fontName != "..")
            {
                $fonts[] = $fontName;
            }
        }
        closedir($dir);

        for($i=0;$i<$this->_options['captcha_font_nums'];$i++)
        {
            $color = imagecolorallocatealpha($src,rand(0,255),rand(0,255),rand(0,255),100);
            $font = $this->_pathFont.$fonts[rand(0,sizeof($fonts)-1)];
            $letter = $this->_characters[rand(0,sizeof($this->_characters)-1)];
            $size = rand($this->_options['font_size']-2,$this->_options['font_size']+2);
            imagettftext($src,$size,rand(0,45),rand($this->_options['width']*0.1,$this->_options['width']-$this->_options['width']*0.1),rand($this->_options['height']*0.2,$this->_options['height']),$color,$font,$letter);
        }

        for($i=0;$i<$this->_options['captcha_nums'];$i++)
        {
            $color = imagecolorallocatealpha($src,$this->_colors[rand(0,sizeof($this->_colors)-1)],$this->_colors[rand(0,sizeof($this->_colors)-1)],$this->_colors[rand(0,sizeof($this->_colors)-1)],rand(20,40));
            $font = $this->_pathFont.$fonts[rand(0,sizeof($fonts)-1)];
            $letter = $this->_characters[rand(0,sizeof($this->_characters)-1)];
            $size = rand($this->_options['font_size']*2.1-2,$this->_options['font_size']*2.1+2);
            $x = ($i+1)*$this->_options['font_size'] + rand(4,7);
            $y = (($this->_options['height']*2)/3) + rand(0,5);
            $cod[] = $letter;
            imagettftext($src,$size,rand(0,15),$x+50,$y,$color,$font,$letter);
        }

        $this->_cod = implode('',$cod);
        $this->_src = $src;
    }

    public function getCode(){
        return $this->_cod;
    }

    public function run(){
        header ("Content-type: image/gif");
        imagegif($this->_src);
    }
}
