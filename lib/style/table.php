<?php
include 'table_header.php';
include 'alternate_row_back_color.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of table
 *
 * @author Alamgir
 */
class Table {
    //put your code here
    var $header;
    var $alternateRowBackColor;
    var $cols;
    var $border_color = "#000000";
    var $border_width = 0.4;
    
    public function __construct( $table = null){
        if($table){
            $this->header = new TableHeader( $table[HEADER] );
            $this->alternateRowBackColor = new AlternateRowBackColor($table[ALTERNATE_BACKGROUND_COLOR]);
            $this->cols = $table[ROW][COLUMNS];          
            $this->border_color = $table['border_color'];
            $this->border_width = floatval($table['border_width']);
        }
    }
}

?>
