<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require('lib/fpdf.php');

class PDF extends FPDF {

    function __construct($orientation='P', $unit='mm', $size='A3') {
        parent::__construct($orientation, $unit, $size);
    }
    function noOfLines($w, $txt) {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];//print_r( $cw);
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            //print_r($c);//echo "</br>";
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;//echo "Current Line: ". $nl. "<br/>";
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l+=$cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                }
                else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                //echo "<br/>Current Line: ". $nl. "<br/>";
            }
            else
                $i++;
            //echo "Current Line: ". $nl. " ";
            //echo "</br>Value of : ".$i. "<br/>";
        }
        //echo "No of lines: ".$nl." ";
        //echo $s . "</br>";
        return $nl;
    }

    function fillBackGroundColor($col_data, $height, $is_first_row) {

        $x = $this->GetX();
        $y = $this->GetY();

        for ($col = 0; $col < count($col_data); $col++) {
            $borders = 'RB';
            $borders = $col == 0?$borders.'L':$borders;
            $borders = $is_first_row == true? $borders.'T':$borders;
            
            $this->Cell($col_data[$col]['cell_width'], $height, '', "RBLT", 0, 'C', true);
        }

        $this->SetXY($x, $y);
    }
    function getEssentialRowHeight($col_data) {
        $max_height = 0;
        for ($i = 0; $i < count($col_data); $i++) {
            $max_height = max($max_height, $this->noOfLines($col_data[$i]['cell_width'], $col_data[$i]['cell_value']));
        }
        //echo "Max Height: ". $max_height."<br/>";
        //To calculate the row_height we need to multiply max_height by 5
        //but I don't know why
        //I hv gotten it from a tutorial
        return (5 * $max_height) + 5;
    }
    function isPageBreakNeeded($height, $footer_height = 0) {
        //If the height would cause an overflow, 
        //add a new page immediately
        if ($this->GetY() + $height + $footer_height> $this->PageBreakTrigger) {
            return true;
        }
    }
   
    function addPageBreak($height, $header_height = 20, $footer_height = 0) {
        //If the height would cause an overflow, 
        //add a new page immediately
        if ($this->GetY() + $height + $footer_height> $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->ln( $header_height );
            return true;
        }
    }
    function getRowHeightByFillData($col_data) {
        $start_x_pos = $this->GetX();
        $start_y_pos = $this->GetY();

        $x = $this->GetX();
        $maxRowHeight = 0;
        for ($col = 0; $col < count($col_data); $col++) {

            $yBeforeCell = $this->GetY();
            $borders = "";
            //$borders = 'LB' . ($col + 1 == count($col_data) ? 'R' : ''); // Only add R for last col
            $column_style = new Column($col_data[$col]['cell_style']);
            
            $this->SetFont($column_style->font_family, $column_style->font_weight, $column_style->font_size);
            $this->colorConverter->convertHex2RGB($column_style->font_color);
            $this->SetTextColor($this->colorConverter->r, $this->colorConverter->g, $this->colorConverter->b);
        
            $this->MultiCell($col_data[$col]['cell_width'], DEFAULT_CELL_HEIGHT, $col_data[$col]['cell_value'], $borders, $column_style->text_align, false);

            $yCurrent = $this->GetY();
            $rowHeight = $yCurrent - $yBeforeCell;
            if ($maxRowHeight < $rowHeight) {
                $maxRowHeight = $rowHeight;
            }
            $this->SetXY($x + $col_data[$col]['cell_width'], $yCurrent - $rowHeight);
            $x = $this->GetX();
        }
        $this->SetXY($start_x_pos, $start_y_pos);
        return $maxRowHeight;
    }

    function addRow($col_data, $row_index, $table_style) {
        $this->colorConverter->convertHex2RGB($table_style->border_color);
        $style = array('width' => $table_style->border_width, 'color' => array($this->colorConverter->r, $this->colorConverter->g, $this->colorConverter->b));
        $this->SetLineStyle($style);
            
        if ($row_index % 2 == 0) {
            $this->colorConverter->convertHex2RGB($table_style->alternateRowBackColor->even_row_color);
            $this->SetFillColor($this->colorConverter->r, $this->colorConverter->g, $this->colorConverter->b);
        } else {
            $this->colorConverter->convertHex2RGB($table_style->alternateRowBackColor->odd_row_color);
            $this->SetFillColor($this->colorConverter->r, $this->colorConverter->g, $this->colorConverter->b);
        }
        
        //drawing data into the cell and get the maximum row height
        $fill_height = $this->getRowHeightByFillData($col_data);
        //filling the cell with background color that overlaps the data 
        //those are drawn before
        //now we have to draw the data again
        $this->fillBackGroundColor($col_data, $fill_height, $this->page_first_row);
        $maxRowHeight = $this->getRowHeightByFillData($col_data);
        $this->Ln($maxRowHeight);
    }

    function calTHeaderHeightAndSetText($style, $col_data){
        $start_x_pos = $this->GetX();
        $start_y_pos = $this->GetY();

        $x = $this->GetX();
        $maxRowHeight = 0;
        
        $this->colorConverter->convertHex2RGB($style->background_color);
        $this->SetFillColor($this->colorConverter->r, $this->colorConverter->g, $this->colorConverter->b);
        for ($col = 0; $col < count($col_data); $col++) {

            $yBeforeCell = $this->GetY();
            $borders = "";
            //$borders = 'LB' . ($col + 1 == count($col_data) ? 'R' : ''); // Only add R for last col
            //$column_style = new Column($col_data[$col]['cell_style']);
           
            //$this->SetFont($style->font_family, $style->font_weight, $style->font_size);
            $this->colorConverter->convertHex2RGB($style->font_color);
            $this->SetTextColor($this->colorConverter->r, $this->colorConverter->g, $this->colorConverter->b);
        
            $this->MultiCell($col_data[$col]['cell_width'], DEFAULT_CELL_HEIGHT, $col_data[$col]['cell_style']['header_text'], $borders, $style->text_align, false);

            $yCurrent = $this->GetY();
            $rowHeight = $yCurrent - $yBeforeCell;
            if ($maxRowHeight < $rowHeight) {
                $maxRowHeight = $rowHeight;
            }
            $this->SetXY($x + $col_data[$col]['cell_width'], $yCurrent - $rowHeight);
            $x = $this->GetX();
        }
        $this->SetXY($start_x_pos, $start_y_pos);
        
        return $maxRowHeight;
    }
    
    function addTableHeader($style, $table_style, $col_data) {
        
        $this->colorConverter->convertHex2RGB($style->background_color);
        $line_style = array('width' => $table_style->border_width, 'color' => array($this->colorConverter->r, $this->colorConverter->g, $this->colorConverter->b));
        $this->SetLineStyle($line_style);
        
        $maxRowHeight = $this->calTHeaderHeightAndSetText($style, $col_data);
        $this->fillBackGroundColor($col_data, $maxRowHeight, true);
        $this->calTHeaderHeightAndSetText($style, $col_data);
        $this->Ln($maxRowHeight);
    }

    
    function getCellValue($obj, $name) {
        return isset($obj[$name]) == true ? is_array($obj[$name]) == true ? "" : $obj[$name]  : "";
    }
    
    function splitRow($col, $footer_height){
        //$col = new Column($col);
        $is_splitting_possible = false;
        $rows = array();
        $first_row = $col;
        $second_row = $col;
        
        for($i = 0; $i < count($col); $i ++){
            foreach ($col[$i] as $key => $value) {
                if($key == 'cell_value'){
                    $no_of_lines = $this->noOfLines($col[$i]['cell_width'], $value);
                    $assumedLineHeight = $no_of_lines * 5 + 5;
                    
                    //print_r("Assumed Height". $assumedLineHeight. " Footer Height: ".$footer_height. " Current page size: ". $this->CurPageSize[ 0 ]. "<br/>");
                    
                    //if($this->isPageBreakNeeded($assumedLineHeight, $footer_height)){

                    $multiplier = 3;
                    if ($this->PageNo() == 1) {
                        $multiplier = 1;
                    }
                    if ($this->GetY() + $assumedLineHeight + 2 * $footer_height > $this->CurPageSize[0]) {
                        $strs = $this->splitString($col[$i], $footer_height);

                        $first_row[$i][$key] = $strs[0];
                        $second_row[$i][$key] = $strs [1];


                        $is_splitting_possible = true;
                    }
                    else{
                        $second_row[ $i ][$key ] = "";
                    }
                }
            }
        }
        if($is_splitting_possible){
            array_push($rows, $first_row, $second_row);
        }
        else{
            array_push($rows, $first_row, false);
        }
        return $rows;
    }
    
    function splitString($col, $footer_height){
       $string_pieces = explode(" ", $col['cell_value']);
       
       $first_string = "";
       $second_string = "";
       
       for($i = 0; $i < count($string_pieces); $i ++){
            $first_string = $first_string. $string_pieces[ $i ] . " ";
            $no_of_lines = $this->noOfLines($col['cell_width'], $first_string);
            $assumedLineHeight = $no_of_lines * 5 + 5;

            $multiplier = 3;
            if ($this->PageNo() == 1) {
                $multiplier = 1;
            }
            if($this->GetY() + $assumedLineHeight +  $multiplier * $footer_height > $this->CurPageSize[ 0 ]){

                break;
            }
       }
       
       for($j = $i + 1; $j < count($string_pieces); $j ++){
           $second_string = $second_string. " ".$string_pieces[ $j ];
       }
       
       $strs = array();
       
       $first_string = trim($first_string);
       $second_string = trim($second_string);
       
       array_push($strs, $first_string, $second_string);
       return $strs;
    }
    
        // Sets line style
    // Parameters:
    // - style: Line style. Array with keys among the following:
    //   . width: Width of the line in user units
    //   . cap: Type of cap to put on the line (butt, round, square). The difference between 'square' and 'butt' is that 'square' projects a flat end past the end of the line.
    //   . join: miter, round or bevel
    //   . dash: Dash pattern. Is 0 (without dash) or array with series of length values, which are the lengths of the on and off dashes.
    //           For example: (2) represents 2 on, 2 off, 2 on , 2 off ...
    //                        (2, 1) is 2 on, 1 off, 2 on, 1 off.. etc
    //   . phase: Modifier of the dash pattern which is used to shift the point at which the pattern starts
    //   . color: Draw color. Array with components (red, green, blue)
    function SetLineStyle($style) {
        extract($style);
        if (isset($width)) {
            $width_prev = $this->LineWidth;
            $this->SetLineWidth($width);
            $this->LineWidth = $width_prev;
        }
        if (isset($cap)) {
            $ca = array('butt' => 0, 'round'=> 1, 'square' => 2);
            if (isset($ca[$cap]))
                $this->_out($ca[$cap] . ' J');
        }
        if (isset($join)) {
            $ja = array('miter' => 0, 'round' => 1, 'bevel' => 2);
            if (isset($ja[$join]))
                $this->_out($ja[$join] . ' j');
        }
//        if (isset($dash)) {
//            $dash_string = '';
//            if ($dash) {
//                if(ereg('^.+, ', $dash))
//                    $tab = explode(', ', $dash);
//                else
//                    $tab = array($dash);
//                $dash_string = '';
//                foreach ($tab as $i => $v) {
//                    if ($i > 0)
//                        $dash_string .= ' ';
//                    $dash_string .= sprintf('%.2f', $v);
//                }
//            }
//            if (!isset($phase) || !$dash)
//                $phase = 0;
//            $this->_out(sprintf('[%s] %.2f d', $dash_string, $phase));
//        }
        if (isset($color)) {
            list($r, $g, $b) = $color;
            $this->SetDrawColor($r, $g, $b);
        }
    }

    // Draws a line
    // Parameters:
    // - x1, y1: Start point
    // - x2, y2: End point
    // - style: Line style. Array like for SetLineStyle
    function Line($x1, $y1, $x2, $y2, $style = null) {
        if ($style)
            $this->SetLineStyle($style);
        parent::Line($x1, $y1, $x2, $y2);
    }

    // Draws a rectangle
    // Parameters:
    // - x, y: Top left corner
    // - w, h: Width and height
    // - style: Style of rectangle (draw and/or fill: D, F, DF, FD)
    // - border_style: Border style of rectangle. Array with some of this index
    //   . all: Line style of all borders. Array like for SetLineStyle
    //   . L: Line style of left border. null (no border) or array like for SetLineStyle
    //   . T: Line style of top border. null (no border) or array like for SetLineStyle
    //   . R: Line style of right border. null (no border) or array like for SetLineStyle
    //   . B: Line style of bottom border. null (no border) or array like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    function Rect($x, $y, $w, $h, $style = '', $border_style = null, $fill_color = null) {
        if (!(false === strpos($style, 'F')) && $fill_color) {
            list($r, $g, $b) = $fill_color;
            $this->SetFillColor($r, $g, $b);
        }
        switch ($style) {
            case 'F':
                $border_style = null;
                parent::Rect($x, $y, $w, $h, $style);
                break;
            case 'DF': case 'FD':
                if (!$border_style || isset($border_style['all'])) {
                    if (isset($border_style['all'])) {
                        $this->SetLineStyle($border_style['all']);
                        $border_style = null;
                    }
                } else
                    $style = 'F';
                parent::Rect($x, $y, $w, $h, $style);
                break;
            default:
                if (!$border_style || isset($border_style['all'])) {
                    if (isset($border_style['all']) && $border_style['all']) {
                        $this->SetLineStyle($border_style['all']);
                        $border_style = null;
                    }
                    parent::Rect($x, $y, $w, $h, $style);
                }
                break;
        }
        if ($border_style) {
            if (isset($border_style['L']) && $border_style['L'])
                $this->Line($x, $y, $x, $y + $h, $border_style['L']);
            if (isset($border_style['T']) && $border_style['T'])
                $this->Line($x, $y, $x + $w, $y, $border_style['T']);
            if (isset($border_style['R']) && $border_style['R'])
                $this->Line($x + $w, $y, $x + $w, $y + $h, $border_style['R']);
            if (isset($border_style['B']) && $border_style['B'])
                $this->Line($x, $y + $h, $x + $w, $y + $h, $border_style['B']);
        }
    }

    // Draws a Bézier curve (the Bézier curve is tangent to the line between the control points at either end of the curve)
    // Parameters:
    // - x0, y0: Start point
    // - x1, y1: Control point 1
    // - x2, y2: Control point 2
    // - x3, y3: End point
    // - style: Style of rectangule (draw and/or fill: D, F, DF, FD)
    // - line_style: Line style for curve. Array like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    function Curve($x0, $y0, $x1, $y1, $x2, $y2, $x3, $y3, $style = '', $line_style = null, $fill_color = null) {
        if (!(false === strpos($style, 'F')) && $fill_color) {
            list($r, $g, $b) = $fill_color;
            $this->SetFillColor($r, $g, $b);
        }
        switch ($style) {
            case 'F':
                $op = 'f';
                $line_style = null;
                break;
            case 'FD': case 'DF':
                $op = 'B';
                break;
            default:
                $op = 'S';
                break;
        }
        if ($line_style)
            $this->SetLineStyle($line_style);

        $this->_Point($x0, $y0);
        $this->_Curve($x1, $y1, $x2, $y2, $x3, $y3);
        $this->_out($op);
    }

    // Draws an ellipse
    // Parameters:
    // - x0, y0: Center point
    // - rx, ry: Horizontal and vertical radius (if ry = 0, draws a circle)
    // - angle: Orientation angle (anti-clockwise)
    // - astart: Start angle
    // - afinish: Finish angle
    // - style: Style of ellipse (draw and/or fill: D, F, DF, FD, C (D + close))
    // - line_style: Line style for ellipse. Array like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    // - nSeg: Ellipse is made up of nSeg Bézier curves
    function Ellipse($x0, $y0, $rx, $ry = 0, $angle = 0, $astart = 0, $afinish = 360, $style = '', $line_style = null, $fill_color = null, $nSeg = 8) {
        if ($rx) {
            if (!(false === strpos($style, 'F')) && $fill_color) {
                list($r, $g, $b) = $fill_color;
                $this->SetFillColor($r, $g, $b);
            }
            switch ($style) {
                case 'F':
                    $op = 'f';
                    $line_style = null;
                    break;
                case 'FD': case 'DF':
                    $op = 'B';
                    break;
                case 'C':
                    $op = 's'; // small 's' means closing the path as well
                    break;
                default:
                    $op = 'S';
                    break;
            }
            if ($line_style)
                $this->SetLineStyle($line_style);
            if (!$ry)
                $ry = $rx;
            $rx *= $this->k;
            $ry *= $this->k;
            if ($nSeg < 2)
                $nSeg = 2;

            $astart = deg2rad((float) $astart);
            $afinish = deg2rad((float) $afinish);
            $totalAngle = $afinish - $astart;

            $dt = $totalAngle/$nSeg;
            $dtm = $dt/3;

            $x0 *= $this->k;
            $y0 = ($this->h - $y0) * $this->k;
            if ($angle != 0) {
                $a = -deg2rad((float) $angle);
                $this->_out(sprintf('q %.2f %.2f %.2f %.2f %.2f %.2f cm', cos($a), -1 * sin($a), sin($a), cos($a), $x0, $y0));
                $x0 = 0;
                $y0 = 0;
            }

            $t1 = $astart;
            $a0 = $x0 + ($rx * cos($t1));
            $b0 = $y0 + ($ry * sin($t1));
            $c0 = -$rx * sin($t1);
            $d0 = $ry * cos($t1);
            $this->_Point($a0 / $this->k, $this->h - ($b0 / $this->k));
            for ($i = 1; $i <= $nSeg; $i++) {
                // Draw this bit of the total curve
                $t1 = ($i * $dt) + $astart;
                $a1 = $x0 + ($rx * cos($t1));
                $b1 = $y0 + ($ry * sin($t1));
                $c1 = -$rx * sin($t1);
                $d1 = $ry * cos($t1);
                $this->_Curve(($a0 + ($c0 * $dtm)) / $this->k,
                            $this->h - (($b0 + ($d0 * $dtm)) / $this->k),
                            ($a1 - ($c1 * $dtm)) / $this->k,
                            $this->h - (($b1 - ($d1 * $dtm)) / $this->k),
                            $a1 / $this->k,
                            $this->h - ($b1 / $this->k));
                $a0 = $a1;
                $b0 = $b1;
                $c0 = $c1;
                $d0 = $d1;
            }
            $this->_out($op);
            if ($angle !=0)
                $this->_out('Q');
        }
    }

    // Draws a circle
    // Parameters:
    // - x0, y0: Center point
    // - r: Radius
    // - astart: Start angle
    // - afinish: Finish angle
    // - style: Style of circle (draw and/or fill) (D, F, DF, FD, C (D + close))
    // - line_style: Line style for circle. Array like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    // - nSeg: Ellipse is made up of nSeg Bézier curves
    function Circle($x0, $y0, $r, $astart = 0, $afinish = 360, $style = '', $line_style = null, $fill_color = null, $nSeg = 8) {
        $this->Ellipse($x0, $y0, $r, 0, 0, $astart, $afinish, $style, $line_style, $fill_color, $nSeg);
    }

    // Draws a polygon
    // Parameters:
    // - p: Points. Array with values x0, y0, x1, y1, ..., x(np-1), y(np - 1)
    // - style: Style of polygon (draw and/or fill) (D, F, DF, FD)
    // - line_style: Line style. Array with one of this index
    //   . all: Line style of all lines. Array like for SetLineStyle
    //   . 0..np-1: Line style of each line. Item is 0 (not line) or like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    function Polygon($p, $style = '', $line_style = null, $fill_color = null) {
        $np = count($p) / 2;
        if (!(false === strpos($style, 'F')) && $fill_color) {
            list($r, $g, $b) = $fill_color;
            $this->SetFillColor($r, $g, $b);
        }
        switch ($style) {
            case 'F':
                $line_style = null;
                $op = 'f';
                break;
            case 'FD': case 'DF':
                $op = 'B';
                break;
            default:
                $op = 'S';
                break;
        }
        $draw = true;
        if ($line_style)
            if (isset($line_style['all']))
                $this->SetLineStyle($line_style['all']);
            else { // 0 .. (np - 1), op = {B, S}
                $draw = false;
                if ('B' == $op) {
                    $op = 'f';
                    $this->_Point($p[0], $p[1]);
                    for ($i = 2; $i < ($np * 2); $i = $i + 2)
                        $this->_Line($p[$i], $p[$i + 1]);
                    $this->_Line($p[0], $p[1]);
                    $this->_out($op);
                }
                $p[$np * 2] = $p[0];
                $p[($np * 2) + 1] = $p[1];
                for ($i = 0; $i < $np; $i++)
                    if (!empty($line_style[$i]))
                        $this->Line($p[$i * 2], $p[($i * 2) + 1], $p[($i * 2) + 2], $p[($i * 2) + 3], $line_style[$i]);
            }

        if ($draw) {
            $this->_Point($p[0], $p[1]);
            for ($i = 2; $i < ($np * 2); $i = $i + 2)
                $this->_Line($p[$i], $p[$i + 1]);
            $this->_Line($p[0], $p[1]);
            $this->_out($op);
        }
    }

    // Draws a regular polygon
    // Parameters:
    // - x0, y0: Center point
    // - r: Radius of circumscribed circle
    // - ns: Number of sides
    // - angle: Orientation angle (anti-clockwise)
    // - circle: Draw circumscribed circle or not
    // - style: Style of polygon (draw and/or fill) (D, F, DF, FD)
    // - line_style: Line style. Array with one of this index
    //   . all: Line style of all lines. Array like for SetLineStyle
    //   . 0..ns-1: Line style of each line. Item is 0 (not line) or like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    // - circle_style: Style of circumscribed circle (draw and/or fill) (D, F, DF, FD) (if draw)
    // - circle_line_style: Line style for circumscribed circle. Array like for SetLineStyle (if draw)
    // - circle_fill_color: Fill color for circumscribed circle. Array with components (red, green, blue) (if draw fill circle)
    function RegularPolygon($x0, $y0, $r, $ns, $angle = 0, $circle = false, $style = '', $line_style = null, $fill_color = null, $circle_style = '', $circle_line_style = null, $circle_fill_color = null) {
        if ($ns < 3)
            $ns = 3;
        if ($circle)
            $this->Circle($x0, $y0, $r, 0, 360, $circle_style, $circle_line_style, $circle_fill_color);
        $p = null;
        for ($i = 0; $i < $ns; $i++) {
            $a = $angle + ($i * 360 / $ns);
            $a_rad = deg2rad((float) $a);
            $p[] = $x0 + ($r * sin($a_rad));
            $p[] = $y0 + ($r * cos($a_rad));
        }
        $this->Polygon($p, $style, $line_style, $fill_color);
    }

    // Draws a star polygon
    // Parameters:
    // - x0, y0: Center point
    // - r: Radius of circumscribed circle
    // - nv: Number of vertices
    // - ng: Number of gaps (ng % nv = 1 => regular polygon)
    // - angle: Orientation angle (anti-clockwise)
    // - circle: Draw circumscribed circle or not
    // - style: Style of polygon (draw and/or fill) (D, F, DF, FD)
    // - line_style: Line style. Array with one of this index
    //   . all: Line style of all lines. Array like for SetLineStyle
    //   . 0..n-1: Line style of each line. Item is 0 (not line) or like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    // - circle_style: Style of circumscribed circle (draw and/or fill) (D, F, DF, FD) (if draw)
    // - circle_line_style: Line style for circumscribed circle. Array like for SetLineStyle (if draw)
    // - circle_fill_color: Fill color for circumscribed circle. Array with components (red, green, blue) (if draw fill circle)
    function StarPolygon($x0, $y0, $r, $nv, $ng, $angle = 0, $circle = false, $style = '', $line_style = null, $fill_color = null, $circle_style = '', $circle_line_style = null, $circle_fill_color = null) {
        if ($nv < 2)
            $nv = 2;
        if ($circle)
            $this->Circle($x0, $y0, $r, 0, 360, $circle_style, $circle_line_style, $circle_fill_color);
        $p2 = null;
        $visited = null;
        for ($i = 0; $i < $nv; $i++) {
            $a = $angle + ($i * 360 / $nv);
            $a_rad = deg2rad((float) $a);
            $p2[] = $x0 + ($r * sin($a_rad));
            $p2[] = $y0 + ($r * cos($a_rad));
            $visited[] = false;
        }
        $p = null;
        $i = 0;
        do {
            $p[] = $p2[$i * 2];
            $p[] = $p2[($i * 2) + 1];
            $visited[$i] = true;
            $i += $ng;
            $i %= $nv;
        } while (!$visited[$i]);
        $this->Polygon($p, $style, $line_style, $fill_color);
    }

    // Draws a rounded rectangle
    // Parameters:
    // - x, y: Top left corner
    // - w, h: Width and height
    // - r: Radius of the rounded corners
    // - round_corner: Draws rounded corner or not. String with a 0 (not rounded i-corner) or 1 (rounded i-corner) in i-position. Positions are, in order and begin to 0: top left, top right, bottom right and bottom left
    // - style: Style of rectangle (draw and/or fill) (D, F, DF, FD)
    // - border_style: Border style of rectangle. Array like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    function RoundedRect($x, $y, $w, $h, $r, $round_corner = '1111', $style = '', $border_style = null, $fill_color = null) {
        if ('0000' == $round_corner) // Not rounded
            $this->Rect($x, $y, $w, $h, $style, $border_style, $fill_color);
        else { // Rounded
            if (!(false === strpos($style, 'F')) && $fill_color) {
                list($red, $g, $b) = $fill_color;
                $this->SetFillColor($red, $g, $b);
            }
            switch ($style) {
                case 'F':
                    $border_style = null;
                    $op = 'f';
                    break;
                case 'FD': case 'DF':
                    $op = 'B';
                    break;
                default:
                    $op = 'S';
                    break;
            }
            if ($border_style)
                $this->SetLineStyle($border_style);

            $MyArc = 4 / 3 * (sqrt(2) - 1);

            $this->_Point($x + $r, $y);
            $xc = $x + $w - $r;
            $yc = $y + $r;
            $this->_Line($xc, $y);
            if ($round_corner[0])
                $this->_Curve($xc + ($r * $MyArc), $yc - $r, $xc + $r, $yc - ($r * $MyArc), $xc + $r, $yc);
            else
                $this->_Line($x + $w, $y);

            $xc = $x + $w - $r ;
            $yc = $y + $h - $r;
            $this->_Line($x + $w, $yc);

            if ($round_corner[1])
                $this->_Curve($xc + $r, $yc + ($r * $MyArc), $xc + ($r * $MyArc), $yc + $r, $xc, $yc + $r);
            else
                $this->_Line($x + $w, $y + $h);

            $xc = $x + $r;
            $yc = $y + $h - $r;
            $this->_Line($xc, $y + $h);
            if ($round_corner[2])
                $this->_Curve($xc - ($r * $MyArc), $yc + $r, $xc - $r, $yc + ($r * $MyArc), $xc - $r, $yc);
            else
                $this->_Line($x, $y + $h);

            $xc = $x + $r;
            $yc = $y + $r;
            $this->_Line($x, $yc);
            if ($round_corner[3])
                $this->_Curve($xc - $r, $yc - ($r * $MyArc), $xc - ($r * $MyArc), $yc - $r, $xc, $yc - $r);
            else {
                $this->_Line($x, $y);
                $this->_Line($x + $r, $y);
            }
            $this->_out($op);
        }
    }

    /* PRIVATE METHODS */

    // Sets a draw point
    // Parameters:
    // - x, y: Point
    function _Point($x, $y) {
        $this->_out(sprintf('%.2f %.2f m', $x * $this->k, ($this->h - $y) * $this->k));
    }

    // Draws a line from last draw point
    // Parameters:
    // - x, y: End point
    function _Line($x, $y) {
        $this->_out(sprintf('%.2f %.2f l', $x * $this->k, ($this->h - $y) * $this->k));
    }

    // Draws a Bézier curve from last draw point
    // Parameters:
    // - x1, y1: Control point 1
    // - x2, y2: Control point 2
    // - x3, y3: End point
    function _Curve($x1, $y1, $x2, $y2, $x3, $y3) {
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k, $x3 * $this->k, ($this->h - $y3) * $this->k));
    }


}

?>
