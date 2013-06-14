<?php

include '../lib/style/paragraph.php';
include '../lib/style/table.php';
include '../lib/style/header.php';
include '../lib/style/footer.php';
include '../lib/style/column.php';

class RiskRegisterHTML {

    var $header;
    var $footer;
    var $table;
    var $risk_register_para;
    var $firm_para;
    var $date_para;
    var $col;
    
    var $css_style = "";
    
    var $firm_id;
    var $firm_name;
    var $risk_register;
    var $report_date;
    var $risk_register_rows = array();
    var $page_first_row = true;
    var $data;
    var $report_request_id;

    public function __construct() {
        $this->header = new Header();
        $this->footer = new Footer();
        $this->table = new Table();
        $this->risk_register_para = new Paragraph();
        $this->firm_para = new Paragraph();
        $this->date_para = new Paragraph();
        $this->col = new Column();
    }

    public function load_data($data, $styles) {
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


        foreach ($styles as $style) {

            $this->header = new Header($style[HEADER]);
            $this->table = new Table($style[TABLE]);
            $this->footer = new Footer($style[FOOTER]);

            foreach ($style[PARAGRAPHS] as $key => $paragraph) {
                if ($paragraph['id'] == 'risk_register') {
                    $this->risk_register_para = new Paragraph($paragraph);
                } else if ($paragraph['id'] == 'firm') {
                    $this->firm_para = new Paragraph($paragraph);
                } else if ($paragraph['id'] == 'date') {
                    $this->date_para = new Paragraph($paragraph);
                }
            }
        }
        $this->css_style = "<style type=\"text/css\">";
        $this->css_style = $this->css_style. ".content{margin-left:auto;margin-right:auto;width:900px;height:auto;}".
        ".header{height: ".($this->header->height * 6)."px; "."width: ".($this->header->width * 6)."px; margin-left: auto; margin-right: 0px;}".
        ".header_logo{height: ".($this->header->height * 6)."px; "."width: ".($this->header->width * 6)."px;}".
        ".footer{padding-top:30px; height: ".($this->footer->height * 6)."px; "."width: ".($this->footer->width * 6)."px;}".
        "hr{color: #4F81BD;}".
        ".risk_register{font-weight: {$this->risk_register_para->font_weight}; font-size: {$this->risk_register_para->font_size}pt; color: {$this->risk_register_para->font_color};font-family: {$this->risk_register_para->font_family};}".
        ".firm{font-weight: {$this->getActualFontWeight($this->firm_para->font_weight)}; font-size: {$this->firm_para->font_size}pt; color: {$this->firm_para->font_color};font-family: {$this->firm_para->font_family};}".
        ".date{font-weight: {$this->getActualFontWeight($this->date_para->font_weight)}; font-size: {$this->date_para->font_size}pt; color: {$this->date_para->font_color};font-family: {$this->date_para->font_family};}".
        ".main_container{min-height:400px;height:auto;}".
        "th{background-color:{$this->table->header->background_color}; border: solid {$this->table->border_width}px {$this->table->header->background_color}; font-weight: {$this->getActualFontWeight($this->table->header->font_weight)}; font-size: {$this->table->header->font_size}pt; color: {$this->table->header->font_color}; text-align: {$this->getActualTextAlign($this->table->header->text_align)}}".
        "table{border-collapse:collapse; table-layout:fixed; width:900px;}".
        ".tr0{background-color:{$this->table->alternateRowBackColor->odd_row_color}}".
        ".tr1{background-color:{$this->table->alternateRowBackColor->even_row_color}}".
        "table td {border: solid {$this->table->border_width}px {$this->table->border_color}; word-wrap: break-word;vertical-align: top;}";
        
        $this->css_style = $this->css_style."</style>";
        //echo $this->css_style;
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
    function showHtml(){
        $header_html = "<div class='header'>
				<img src='../images/{$this->header->src}' class='header_logo'/>
			</div>";
        $footer_html = "<div class='footer'>
				<img src='../images/{$this->footer->src}' class='footer'/>
			</div>";
        $paragraph_div = "<div>
				<div class='risk_register'>
					Risk Register
				</div>
				<hr></hr>
				<div class='firm'>
					Firm: {$this->firm_name}
				</div>
				<div class='date'>
					Date: {$this->report_date}
				</div>
			</div>";
       $table_html_begin = "<div class='main_container'>
                                <table> 
                                        <tr>
                                                <th width='25px'>SRA Risk ID</th>
                                                <th width='80px'>Date</th>
                                                <th width='100px'>Source register</th>
                                                <th width='100px'>Practice area</th>
                                                <th width='140px'>Trigger event</th>
                                                <th width='80px'>Compliance officer</th>
                                                <th width='175px'>Immediate action taken to manage the risk and date</th>
                                                <th width='170px'>Comments</th>
                                        </tr>";
                                        $rows_html = "";
                                        $row_count = 0;
                                        foreach ($this->risk_register_rows as $value) {
                                            $sra_riks_id = $this->getCellValue($value, SRA_RISK_ID);
                                            $date = $this->getCellValue($value, DATE);
                                            $source_register = $this->getCellValue($value, SOURCE_REGISTER);
                                            $practice_area = $this->getCellValue($value, PRACTICE_AREA);
                                            $trigger_event = $this->getCellValue($value, TRIGGER_EVENT);
                                            $compliance_officer = $this->getCellValue($value, COMPLIANCE_OFFICER);
                                            $action_taken = $this->getCellValue($value, ACTION_TAKEN);
                                            $comments = $this->getCellValue($value, COMMENTS);
                                            $row_count = $row_count % 2;
                                            $rows_html = $rows_html."<tr class='tr{$row_count }'><td>{$sra_riks_id}</td><td>{$date}</td><td>{$source_register}</td><td>{$practice_area}</td><td>{$trigger_event}</td><td>{$compliance_officer}</td><td>{$action_taken}<td>{$comments}</td></tr>";
                                            $row_count = $row_count +1;
                                        }
                $table_html_end = "</table>
                        </div>";
     $table_html = $table_html_begin.$rows_html.$table_html_end;       
     $head = "<!DOCTYPE html>
                <html>
                    <head>
                        <title></title>
                            {$this->css_style}
                    </head>";
     $content_div = "<body><div class='content'>
                    {$header_html}{$paragraph_div}{$table_html}{$footer_html}
                    </div></body>";
     echo $head.$content_div;
    }
    
    function getCellValue($obj, $name) {
        return isset($obj[$name]) == true ? is_array($obj[$name]) == true ? "" : $obj[$name]  : "";
    }

    function getActualFontWeight($symbol){
        $symbol = strtoupper($symbol);
        if($symbol == 'B'){
            return 'bold';
        }
        else if($symbol == 'I'){
            return '; font-style:italic';
        }
        else if($symbol == 'BI' || $symbol == 'IB'){
            return 'bold; font-style:italic;';
        }
    }
    function getActualTextAlign($symbol){
        $symbol = strtoupper($symbol);
        if($symbol == "C"){
            return 'center;';
        }
        else if($symbol == 'L'){
            return 'left;';
        }
        else if($symbol == 'R'){
            return 'right;';
        }
    }
}

?>
