<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Footer
 *
 * @author Alamgir
 */

class Footer {
    //put your code here
    var $src;
    var $x;
    var $y;
    var $width;
    var $height;
    
    public function __construct($footer = null){
        if($footer){
            $this->src = $footer['img']['src'];
            $this->x = $footer['img']['x'];
            $this->y = $footer['img']['y'];
            $this->width = $footer['img']['width'];
            $this->height = $footer['img']['height'];
        }
    }
}



?>
