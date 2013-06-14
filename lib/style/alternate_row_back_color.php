<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of alternate_row_back_color
 *
 * @author Alamgir
 */
class AlternateRowBackColor {
    //put your code here
     var $odd_row_color = "#FFFFFF";
     var $even_row_color = "#FFFFFF";
     
     function __construct($alternateRowProperty = null){
         
         if($alternateRowProperty){
            $this->odd_row_color = $alternateRowProperty['odd-row'];
            $this->even_row_color = $alternateRowProperty['even-row'];
            //print_r($this->odd_row_color);
         }
     }
}

?>
