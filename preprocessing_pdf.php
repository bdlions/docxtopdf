<?php
    include 'lib/xml_parser.php';
    include 'risk_register_pdf.php';

    $xml = new XMLParser();
    $pdf = new RiskRegisterPDF('L');

    $pdf->AddFont('calibri','', "calibri.php");
    $pdf->AddFont('calibri','I', "calibrii.php");
    $pdf->AddFont('calibri','B', "calibrib.php");
    $pdf->AddFont('calibri','BI', "calibriz.php");
    
    $xml->load_file('xml/'.trim($_POST['xmlName']));
    $isvalid = $xml->validate_xml('schema/riskRegister.xsd');
        
    $style_parser = new XMLParser();
    
    $style_parser->load_file('xml/docx_style/risk_register_style.xml');
    $isvalid_style = $style_parser->validate_xml('schema/docx_style/risk_register_style.xsd');
    
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
        
        $pdf->load_data($data, $styles);
        $pdf->AddPage();
        $pdf->AliasNbPages();
        $pdf->generate_pdf();
        $pdf->Output();
        
    } 
    
    
?>



