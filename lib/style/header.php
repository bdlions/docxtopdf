<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of header
 *
 * @author Alamgir
 */
class Header {
    //put your code here
    var $src;
    var $x;
    var $y;
    var $width;
    var $height;
    
    public function __construct($header = null){
        if($header){
            $this->src = $header['img']['src'];
            $this->x = $header['img']['x'];
            $this->y = $header['img']['y'];
            $this->width = $header['img']['width'];
            $this->height = $header['img']['height'];
        }
    }
}

?>
