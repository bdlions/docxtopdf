<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tableHeader
 *
 * @author Alamgir
 */
class TableHeader {
    //put your code here
    var $background_color = "#000000";
    var $font_weight = "";
    var $font_color = "#000000";
    var $font_family = "Times";
    
    public function __construct($header){
        if($header){
            $this->background_color = $header['backgournd_color'];
            $this->font_weight = $header[FONT]['weight'];
            $this->font_color = $header[FONT]['color'];
            $this->font_family = $header[FONT]['font_name'];
        }
    }
}

?>
