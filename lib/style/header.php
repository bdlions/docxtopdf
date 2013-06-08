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
    var $size;
    
    public function __construct($header){
        if($header){
            $this->src = $header['img']['src'];
            $this->x = $header['img']['x'];
            $this->y = $header['img']['y'];
            $this->size = $header['img']['size'];
        }
    }
}

?>
