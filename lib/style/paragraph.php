<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paragraph
 *
 * @author Alamgir
 */
class Paragraph {
    //put your code here
    var $id;
    var $background_color = "#000000";
    var $font_weight = "";
    var $font_color = "#000000";
    var $font_family = "Times";
    var $font_size = 10;
    
    public function __construct($p = null){
        if($p){
            $this->id = $p['id'];
            $this->background_color = $p['backgournd_color'];
            $this->font_weight = $p['font']['weight'];
            $this->font_color = $p['font']['color'];
            $this->font_family = $p['font']['font_name'];
            $this->font_size = $p['font']['size'];
        }
        
    }
}

?>
