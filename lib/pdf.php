<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require('lib/fpdf.php');

class PDF extends FPDF {

    function NbLines($w, $txt) {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
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
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
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
            }
            else
                $i++;
        }
        return $nl;
    }

    function fillBackGroundColor($col_data, $height) {

        $x = $this->GetX();
        $y = $this->GetY();

        for ($col = 0; $col < count($col_data); $col++) {
            $this->Cell($col_data[$col]['cell_width'], $height, '', "TB", 0, 'C', true);
        }

        $this->SetXY($x, $y);
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
            $this->MultiCell($col_data[$col]['cell_width'], DEFAULT_CELL_HEIGHT, $col_data[$col]['cell_value'], $borders, 'L', true);

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

    function addRow($col_data) {
        $maxRowHeight = $this->getRowHeightByFillData($col_data);
        $this->Ln($maxRowHeight);
    }

    function getCellValue($obj, $name) {
        return isset($obj[$name]) == true ? is_array($obj[$name]) == true ? "" : $obj[$name]  : "";
    }

}

?>
