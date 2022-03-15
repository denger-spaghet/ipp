<?php
    ini_set('display_errors', 'stderr');
    $counter = 1;

    function clear_line (String $line) : String {
        //remove comments
        $line = preg_replace("/#.*/", "", $line);

        //remove whitespaces
        $line = trim($line);
        return $line;
    }

    function write_xml ($xml, $instruction) {
        $opcode = $xml->addChild('instruction');
        $opcode->addAttribute('order', $GLOBALS["counter"]++);
        $opcode->addAttribute('opcode', $instruction);
    }

    foreach($argv as $cl_arg){
        if ($cl_arg == '--help' && count($argv) == 2){
            echo 'help';
            exit();
        } else if ($cl_arg == '--help' && count($argv) !== 2){
            exit(10);
        }
    }

    while ($first_line = fgets(STDIN)){
        
        $first_line = clear_line($first_line);

        if ($first_line !== ""){
            break;
        }
        
    }
    if (strcasecmp($first_line, ".IPPcode22") !== 0){
        exit(21);
    } 
    $xml_header = '<?xml version="1.0" encoding="UTF-8"?><program></program>';
    $xml = new SimpleXMLElement($xml_header);
    $xml->addAttribute('language', 'IPPcode22');

    
    while ($line = fgets(STDIN)){
        $line = clear_line($line);

        $split_line = explode(" ", $line);
        $instr = strtoupper($split_line[0]);

        switch ($instr) {
            case "CREATEFRAME":
            case "PUSHFRAME":
            case "POPFRAME":
            case "RETURN":
            case "BREAK":
                if (count($split_line) !== 1) {
                    exit (23);
                }
                write_xml($xml, $instr);
                break;
            default:
                exit (22);
        }
    }
    

    echo $xml->asXML();
?>