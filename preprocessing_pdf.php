<?php
    include 'lib/xml_parser.php';
    include 'risk_register_pdf.php';

    $xml = new XMLParser();
    $pdf = new RiskRegisterPDF();

    $xml->load_file('xml/'.trim($_POST['xmlName']));
    $isvalid = $xml->validate_xml('schema/riskRegister.xsd');
        
    $style_parser = new XMLParser();
    
    $style_parser->load_file('xml/docx_style/risk_register_style.xml');
    $isvalid_style = $style_parser->validate_xml('schema/docx_style/risk_register_style.xsd');
    
    if(!$isvalid){
        print_r($isvalid);
        die();
    }
    if(!$isvalid_style){
        print_r($isvalid_style);
        die();
    }
    
    if ($isvalid === True && $isvalid_style) {
        $data = $xml->xml_to_array();
        $styles = $style_parser->xml_to_array();
        
        $pdf->load_data($data, $styles);
        $pdf->AddPage();
        $pdf->AliasNbPages();
        $pdf->generate_pdf();
        $pdf->Output();
        
    } 
    
?>



