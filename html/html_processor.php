<?php
    include '../lib/xml_parser.php';
    include 'risk_register_html.php';

    $xml = new XMLParser();
    $html = new RiskRegisterHTML();
    
    $xml->load_file('../xml/'.trim($_POST['xmlName']));
    $isvalid = $xml->validate_xml('../schema/riskRegister.xsd');
        
    $style_parser = new XMLParser();
    
    $style_parser->load_file('../xml/docx_style/risk_register_style.xml');
    $isvalid_style = $style_parser->validate_xml('../schema/docx_style/risk_register_style.xsd');
    
    if($isvalid != true){
        print_r($isvalid);
        //die("Validation error");
    }
    if($isvalid_style != true){
        print_r($isvalid_style);
        //die("validation erro");
    }else{
        $data = $xml->xml_to_array();
        $styles = $style_parser->xml_to_array();
        
        $html->load_data($data, $styles);
        $html->showHtml();
    } 
    
    
?>
