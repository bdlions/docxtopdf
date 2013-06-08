<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of color_converter
 *
 * @author Alamgir
 */
class ColorConverter {

    var $r = 0;
    var $g = 0;
    var $b = 0;
    //put your code here
    function convertHex2RGB($hex) {
        if($hex){
            $hex = str_replace("#", "", $hex);

            if (strlen($hex) == 3) {
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }
            $this->r = $r;
            $this->g = $g;
            $this->b = $b;
        }
        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $this; // returns an array with the rgb values
    }

}

?>
