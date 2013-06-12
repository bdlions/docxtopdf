<?php
include 'constants.php';
class XMLParser {
    
    var $doc;
    var $size;
    var $lowercase;
    var $defaultBRText ;
    var $contents;

    public function __construct($str = null, $lowercase = true) {
        $this->doc = null;
        $this->size = 0;
        $this->lowercase = true;
        $this->defaultBRText = DEFAULT_BR_TEXT;
        $this->contents = '';

        if ($str) {
            if (preg_match("/^http:\/\//i", $str) || is_file($str)) {
                $this->load_file($str);
            } else {
                $this->load($str, $lowercase);
            }
        }
    }
    public function __destruct() {
        $this->clear();
    }        

    private function clear()
    {
        unset($this->doc);
        unset($this->contents);
    }
    function load_file() {
        $args = func_get_args();
        $this->load(call_user_func_array('file_get_contents', $args), true);
        // Throw an error if we can't properly load the dom.
        if (($error = error_get_last()) !== null) {
            //$this->clear();
            return false;
        }
    }
    
    function load($str, $lowercase = true, $defaultBRText = DEFAULT_BR_TEXT) {
        // prepare
        $this->prepare($str, $lowercase, $defaultBRText);
        $this->contents = $str;
        // make load function chainable
        return $this;
    }
    
    protected function prepare($str, $lowercase = true, $defaultBRText = DEFAULT_BR_TEXT) {
        unset($this->doc);

        // set the length of content before we do anything to it.
        $this->size = strlen($str);
        $this->lowercase = $lowercase;
        $this->defaultBRText = $defaultBRText;
        $this->doc = $str;
    }
    public function xml_to_array() { 
        $namespaceFree = preg_replace('/<([a-z0-9]+):([a-z0-9]+)/i', '<$2', $this->contents);
        $namespaceFree = preg_replace('/<\/([a-z0-9]+):([a-z0-9]+)/i', '</$2', $namespaceFree);
        
        //print_r($namespaceFree);
        $contents = $namespaceFree;

        if(!$contents) return array(); 

        if(!function_exists('xml_parser_create')) { 
            throw new Exception("'xml_parser_create()' function not found!");
            return null; 
        } 

        //Get the XML parser of PHP - PHP must have this module for the parser to work 
        $parser = xml_parser_create(''); 
        
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
        xml_parse_into_struct($parser, trim($contents), $xml_values); 
        xml_parser_free($parser); 

        if(!$xml_values) return;

        //Initializations 
        $xml_array = array(); 
        $parents = array(); 
        $level = 0;
        $is_array = array();
        
        $current    = &$xml_array;

        $dc = count($xml_values);
        for($i=0;$i<$dc;$i++) {
        
            $data = $xml_values[$i];
            if(!isset($is_array[$data['level']]) && ($data['level'] > 1) && ($data['tag'].'s' == $xml_values[$i-1]['tag'] || $data['tag'].'es' == $xml_values[$i-1]['tag'])) {
                $is_array[$data['level']] = true;
            }
            $level = $data['level'];
            
            if($data['type'] == 'open') {
                
                $c = array();
                if(isset($data['attributes'])) $c = $data['attributes'];
                if(!isset($is_array[$level])) {
                    $current[$data['tag']]  = $c;
                    $parents[$level]        = &$current;
                    $current                = &$current[$data['tag']];
                    
                } else {
                    $current[]              = $c;
                    $parents[$level]        = &$current;
                    $current                = &$current[count($current)-1];
                }
                
                
            } else if($data['type'] == 'complete') {
                $c = (substr($data['tag'], -1)=="s")?array():null;
                if(isset($data['attributes'])) $c = $data['attributes']; 
                if(isset($data['value']) && isset($data['attributes'])) $c = array_merge($c,array('value'=>trim($data['value'])));   
                if(isset($data['value']) && !isset($data['attributes'])) $c = trim($data['value']); 

                if(isset($is_array[$level])) {
                    $current[]  = $c;
                } else {
                    $current[$data['tag']]  = $c;
                }
                
            } else {
                unset($is_array[$level+1]);
                $current    = &$parents[$level];
                
            }
            
        } 
         
        return $xml_array; 
    }

    private function _libxml_display_error($error) {
        $return = "Error in $error->file (Line:{$error->line}):"; 
        $return .= trim($error->message); 
        return $return; 
    } 

    private function _libxml_display_errors() { 

        $errors = libxml_get_errors(); 
        $res = array();
        foreach ($errors as $error) { 
            $res[] = $this->_libxml_display_error($error); 
        } 
        libxml_clear_errors(); 
        return $res;
    } 

    public function validate_xml($schema) {
        if(!class_exists('DOMDocument')) { 
            throw new Exception("'DOMDocument' class not found!");
            return False; 
        } 

        libxml_use_internal_errors(true); 

        $xml = new DOMDocument(); 
        $xml->loadXML($this->contents); 

        if (!$xml->schemaValidate($schema)) { 
            return $this->_libxml_display_errors(); 
        } else { 
            return true;
        } 
    }
}

?>