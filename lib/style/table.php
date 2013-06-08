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
    
    public function __construct( $table){
        if($table){
            $this->header = new TableHeader( $table[HEADER] );
            $this->alternateRowBackColor = new AlternateRowBackColor($table[ALTERNATE_BACKGROUND_COLOR]);
            $this->cols = $table[ROW][COLUMNS];
        }
    }
}

?>
