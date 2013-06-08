<?php

require('lib/pdf.php');

class RiskRegisterPDF extends PDF {

    var $data;
    var $report_request_id;
    var $firm_id;
    var $firm_name;
    var $risk_register;
    var $report_date;
    var $risk_register_rows = array();

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

        $this->SetFont('Times', '', 12);
        $this->SetFillColor(224, 235, 255);
        $this->SetAutoPageBreak(false);
        //$this->SetTextColor(0);
        //$this->SetDrawColor(128,0,0);

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
                    array('cell_width' => 10, 'cell_value' => $sra_riks_id), 
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
            $this->CheckPageBreak($row_height);
            
            //drawing data into the cell and get the maximum row height
            $fill_height = $this->getRowHeightByFillData($col_data);
            //filling the cell with background color that overlaps the data 
            //those are drawn before
            //now we have to draw the data again
            $this->fillBackGroundColor($col_data, $fill_height);
            //drawing data and set next line
            $this->addRow($col_data);
        }
    }

    function getEssentialRowHeight($col_data) {
        $max_height = 0;
        for ($i = 0; $i < count($col_data); $i++) {
            $max_height = max($max_height, $this->NbLines($col_data[$i]['cell_width'], $col_data[$i]['cell_value']));
        }

        //To calculate the row_height we need to multiply max_height by 5
        //but I don't know why
        //I hv gotten it from a tutorial
        return 5 * $max_height;
    }

    function CheckPageBreak($height) {
        //If the height would cause an overflow, 
        //add a new page immediately
        if ($this->GetY() + $height > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    // Page header
    function Header() {
        // Move to the right
        $this->Cell(80);
        // Logo
        $this->Image('images/logo.png', 100, 8, 100);
        // Arial bold 15
        $this->SetFont('Times', 'B', 15);
        $this->Ln();
        $this->Cell(0, 30, "Risk Register", 0, 1);

        $this->Cell(0, 10, "Firm: " . $this->firm_name, 0, 1);
        $this->Cell(0, 10, "Date: " . $this->report_date, 0, 1);
        // Line break
        $this->Ln(20);
    }

    // Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

}

?>
