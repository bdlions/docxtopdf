<?php

require('lib/pdf.php');
//require 'lib/draw.php';

class RiskRegisterPDF extends PDF {

    var $data;
    var $report_request_id;
    var $firm_id;
    var $firm_name;
    var $risk_register;
    var $report_date;
    var $header_height = 15;
    var $footer_height = 15;
    var $risk_register_rows = array();
    var $page_first_row = true;

    public function load_data($data) {
        $this->data = $data;
        if (is_array($data)) {
            if (key($data) == REPORT_REQUEST) {
                foreach ($data[REPORT_REQUEST] as $key => $value) {
                    if ($key == REPORT_REQUEST_ID) {
                        $this->report_request_id = $value;
                    } else if ($key == FIRM_ID) {
                        $this->firm_id = $value;
                    } else if ($key == FIRM_NAME) {
                        $this->firm_name = $value;
                    } else if ($key == RISK_REGISTER) {
                        $this->processRiskRegister($value);
                    }
                }
            }
            if (key($data) == RISK_REGISTER) {
                $this->processRiskRegister($data[RISK_REGISTER]);
            } else if (key($data) == RISK_REGISTER_ROWS) {
                foreach ($data[RISK_REGISTER_ROWS] as $key => $value) {
                    if (is_array($value) && sizeof($value) > 0) {
                        array_push($this->risk_register_rows, $value);
                    }
                }
            } else if (key($data) == RISK_REGISTER_ROW) {
                $this->risk_register_rows = $data;
            }
        }
    }

    function processRiskRegister($riskRegister) {
        foreach ($riskRegister as $key => $value) {
            if ($key == REPORT_DATE) {
                $this->report_date = $value;
            }
            if (is_array($value) && sizeof($value) > 0) {
                if ($key == RISK_REGISTER_ROWS) {
                    $this->risk_register_rows = $value;
                }
            }
        }
    }

    function generate_pdf() {
        
        // Arial bold 15
        $this->SetFont('Times', '', 20);
        $this->Cell(0, 30, "Risk Register", 0, 1);
        
        $style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => '10, 20, 5, 10', 'phase' => 10, 'color' => array(27,161,226));
        $this->Line(10, 31, 200, 31, $style);

        $this->SetXY($this->GetX(), 34);
        $this->SetFont('Times', 'B', 12);
        $this->SetTextColor(27,161,226);
        $this->Cell(0, DEFAULT_CELL_HEIGHT, "Firm: " . $this->firm_name, 0, 1);
        $this->Cell(0, DEFAULT_CELL_HEIGHT, "Date: " . $this->report_date, 0, 1);
        
        
        
        $this->SetAutoPageBreak(false);
        $this->SetFont('Times', '', 12);
        $this->SetTextColor(0,0,0);
        
        //$this->SetFillColor(224, 235, 255);
        //$this->SetFillColor(190, 190, 190);
        
        //$this->SetTextColor(0);
        $this->SetDrawColor(190, 190, 190);

        $row_count = 0;
        $col_data = array();
        foreach ($this->risk_register_rows as $value) {

            $sra_riks_id = $this->getCellValue($value, SRA_RISK_ID);
            $date = $this->getCellValue($value, DATE);
            $source_register = $this->getCellValue($value, SOURCE_REGISTER);
            $practice_area = $this->getCellValue($value, PRACTICE_AREA);
            $trigger_event = $this->getCellValue($value, TRIGGER_EVENT);
            $compliance_officer = $this->getCellValue($value, COMPLIANCE_OFFICER);
            $action_taken = $this->getCellValue($value, ACTION_TAKEN);
            $comments = $this->getCellValue($value, COMMENTS);

            $col_data = array();
            array_push($col_data, 
                    array('cell_width' => 15, 'cell_value' => $sra_riks_id), 
                    array('cell_width' => DEFAULT_CELL_WIDTH, 'cell_value' => $date), 
                    array('cell_width' => DEFAULT_CELL_WIDTH, 'cell_value' => $source_register), 
                    array('cell_width' => DEFAULT_CELL_WIDTH, 'cell_value' => $practice_area), 
                    array('cell_width' => DEFAULT_CELL_WIDTH, 'cell_value' => $trigger_event), 
                    array('cell_width' => DEFAULT_CELL_WIDTH, 'cell_value' => $compliance_officer), 
                    array('cell_width' => DEFAULT_CELL_WIDTH, 'cell_value' => $action_taken), 
                    array('cell_width' => DEFAULT_CELL_WIDTH, 'cell_value' => $comments)
            );

            //the height that we need to add new row
            $row_height = $this->getEssentialRowHeight($col_data);

            //Issue a page break first if needed
            if($this->isPageBreakNeeded($row_height, $this->footer_height)){
                $this->addPageBreak($row_height, $this->header_height, $this->footer_height);
                $this->page_first_row = true;
            }
            if($row_count % 2 == 0){
                $this->SetFillColor(230, 230, 230);
            }
            else{
                $this->SetFillColor(255, 255, 255);
            }
            //drawing data into the cell and get the maximum row height
            $fill_height = $this->getRowHeightByFillData($col_data);
            //filling the cell with background color that overlaps the data 
            //those are drawn before
            //now we have to draw the data again
            $this->fillBackGroundColor($col_data, $fill_height, $this->page_first_row);
            //drawing data and set next line
            $this->addRow($col_data);
            $row_count ++;
            $this->page_first_row = false;
        }
    }
    


    // Page header
    function Header() {
        // Move to the right
        $this->Cell(80);
        // Logo
        //image(filename, x, y, width)
        $this->Image('images/header_logo.png', 160, 8, 40);
        $this->Ln();
    }

    // Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Logo
        //image(filename, x, y, width)
        $this->Image('images/footer_logo.png', 10, $this->GetY(), 30);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(370, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }

}

?>
