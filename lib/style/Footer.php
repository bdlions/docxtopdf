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
    var $size;
    
    public function __construct($footer){
        if($footer){
            $this->src = $footer['img']['src'];
            $this->x = $footer['img']['x'];
            $this->y = $footer['img']['y'];
            $this->size = $footer['img']['size'];
        }
    }
}



?>
