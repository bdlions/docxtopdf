<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of column
 *
 * @author Alamgir
 */
class Column {
    var $id;
    var $header_text;
    var $font_weight = "";
    var $font_color = "#000000";
    var $font_family = "Times";
    var $font_size = 12;
    var $text_align = "L";
    
    public function __construct($col = null){
        $this->id = $col['id'];
        $this->header_text = $col['header_text'];
        $this->font_weight = $col['font']['weight'];
        $this->font_color = $col['font']['color'];
        $this->font_family = $col['font']['font_name'];
        $this->font_size = $col['font']['size'];
        $this->text_align = $col['font']['text_align'];
    }
}

?>
