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
    var $background_color = "#FFFFFF";
    var $font_weight = "";
    var $font_color = "#000000";
    var $font_family = "Times";
    var $font_size = 12;
    var $text_align = "C";
    public function __construct($header = null){
        if(!is_object($header)){
            $this->background_color = $header['backgournd_color'];
            $this->font_weight = $header[FONT]['weight'];
            $this->font_color = $header[FONT]['color'];
            $this->font_family = $header[FONT]['font_name'];
            $this->font_size =  $header[FONT]['size'];
            $this->text_align = $header[FONT]['text_align'];
        }
    }
}

?>
