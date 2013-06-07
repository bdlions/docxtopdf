<?php
    include 'lib/xml_parser.php';
    include 'risk_register_pdf.php';

    $xml = new XMLParser();
    
    
    $pdf = new RiskRegisterPDF();

    $xml->load_file('xml/'.trim($_POST['xmlName']));

    $isvalid = $xml->validate_xml('schema/riskRegister.xsd');
    if ($isvalid === True) {
        $data = $xml->xml_to_array();
        
        $pdf->load_data($data);
        $pdf->AddPage();
        $pdf->AliasNbPages();
        $pdf->generate_pdf();
        $pdf->Output();
        
    } else {
        print_r($isvalid);
    }
?>



