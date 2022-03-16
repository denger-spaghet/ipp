<?php
    $non_t = [
        'var' => "/^(LF|GF|TF)@[_\-$&%*!?A-Ža-ž][_\-$&%*!?A-Ža-ž0-9]?$/",
        'label' => "/^[_\-$&%*!?A-Ža-ž][_\-$&%*!?A-Ža-ž0-9]+$/",
        'int' => "/^int@([+-]?\d*)$/",
        'bool' => "/^bool@(true|false)$/",
        'string' => "/^string@([^\s\\\\]|[\\\\]\d{3})*$/",
        'nil' => "/^nil@nil$/",
        'type' => "/^(int|bool|string)$/",
    ];

    ini_set('display_errors', 'stderr');
    $counter = 1;

    function clear_line (String $line) : String {
        //remove comments
        $line = preg_replace("/#.*/", "", $line);

        //remove whitespaces
        $line = trim($line);
        return $line;
    }

    function write_xml ($xml, $instruction, $order) {
        $el = $xml->addChild('instruction');
        $el->addAttribute('order', $order);
        $el->addAttribute('opcode', $instruction);
        return $el;
    }

    function write_xml_arg($type, $arg, $el, $name){
        $arg_count = 1;
        $arg_el = $el->addChild($name, $arg);
        $arg_el->addAttribute('type', $type);

    }

    function check_symb($arg){
        global $non_t;
        if (preg_match($non_t['var'], $arg)) return 'var';
        if (preg_match($non_t['int'], $arg)) return 'int';
        if (preg_match($non_t['bool'], $arg)) return 'bool';
        if (preg_match($non_t['string'], $arg)) return 'string';
        if (preg_match($non_t['nil'], $arg)) return 'nil';
        return false;

    }

    foreach($argv as $cl_arg){
        if ($cl_arg == '--help' && count($argv) == 2){
            echo 'help';
            exit();
        } else if ($cl_arg == '--help' && count($argv) !== 2){
            echo 10;
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
        echo 21;
        exit(21);
    } 
    $xml_header = '<?xml version="1.0" encoding="UTF-8"?><program></program>';
    $xml = new SimpleXMLElement($xml_header);
    $xml->addAttribute('language', 'IPPcode22');

    
    while ($line = fgets(STDIN)){
        $line = clear_line($line);

        $split_line = explode(" ", $line);
        $instr = strtoupper($split_line[0]);

        echo "$instr\n";
        switch ($instr) {
            case "CREATEFRAME":
            case "PUSHFRAME":
            case "POPFRAME":
            case "RETURN":
            case "BREAK":
                if (count($split_line) !== 1) {
                    echo 23;
                    exit (23);
                }
                
                write_xml($xml, $instr, $counter++);
                break;
            case "DEFVAR":
            case "POPS":
                if (count($split_line) !== 2 || !preg_match($non_t['var'], $split_line[1])) {
                    echo $split_line[1];
                    echo 23;
                    exit (23);
                }
                $el = write_xml($xml, $instr, $counter++);
                write_xml_arg('var', $split_line[1], $el, 'arg1');
                break;
            case "CALL":
            case "LABEL":
            case "JUMP":
                if (count($split_line) !== 2 || !preg_match($non_t['label'], $split_line[1])) {
                    echo 23;
                    exit (23);
                }
                $el = write_xml($xml, $instr, $counter++);
                write_xml_arg('label', $split_line[1], $el, 'arg1');
                break;
            case "PUSHS":
            case "WRITE":
            case "EXIT":
                if (count($split_line) !== 2 || !check_symb($split_line[1])){
                    echo check_symb($split_line[1]);
                    echo 23;
                    exit (23);
                }
                $el = write_xml($xml, $instr, $counter++);

                $symb = check_symb($split_line[1]);
                if ($symb == 'var'){
                    write_xml_arg($symb, $split_line[1], $el, 'arg1');
                } else {
                    $arg = explode('@', $split_line[1], 2);
                    write_xml_arg($symb, $arg[1], $el, 'arg1');
                }
                break;
            case "MOVE":
            case "INT2CHAR":
            case "STRLEN":
            case "TYPE":
                if (count($split_line) !== 3 || !preg_match($non_t['var'], $split_line[1]) ||
                !check_symb($split_line[2])){
                    echo 23;
                    exit (23);
                }
                $el = write_xml($xml, $instr, $counter++);
                write_xml_arg('var', $split_line[1], $el, 'arg1');
                $symb = check_symb($split_line[2]);
                if ($symb == 'var'){
                    write_xml_arg($symb, $split_line[2], $el, 'arg2');
                } else {
                    $arg = explode('@', $split_line[2], 2);
                    write_xml_arg($symb, $arg[1], $el, 'arg2');
                }
                break;
            case "READ":
                if (count($split_line) !== 3 || !preg_match($non_t['var'], $split_line[1]) ||
                !preg_match($non_t['type'], $split_line[2])){
                    echo 23;
                    exit (23);
                }
                $el = write_xml($xml, $instr, $counter++);
                write_xml_arg('var', $split_line[1], $el, 'arg1');
                write_xml_arg('type', $split_line[2], $el, 'arg2');
                break;
            case "JUMPIFEQ":
            case "JUMPIFNEQ":
                if (count($split_line) !== 4 || !preg_match($non_t['label'], $split_line[1]) ||
                !check_symb($split_line[2]) || !check_symb($split_line[3])){
                    echo 23;
                    exit (23);
                }
                $el = write_xml($xml, $instr, $counter++);
                write_xml_arg('label', $split_line[1], $el, 'arg1');

                $symb = check_symb($split_line[2]);
                $symb2 = check_symb($split_line[3]);

                if ($symb == 'var'){
                    write_xml_arg($symb, $split_line[2], $el, 'arg2');
                } else {
                    $arg = explode('@', $split_line[2], 2);
                    write_xml_arg($symb, $arg[1], $el, 'arg2');
                }

                if ($symb2 == 'var'){
                    write_xml_arg($symb2, $split_line[3], $el, 'arg3');
                } else {
                    $arg = explode('@', $split_line[3], 2);
                    write_xml_arg($symb2, $arg[1], $el, 'arg3');
                }
                break;
            case "ADD":
            case "SUB":
            case "MUL":
            case "IDIV":
            case "LT":
            case "GT":
            case "EQ":
            case "AND":
            case "OR":
            case "NOT":
            case "STRI2INT":
            case "CONCAT":
            case "GETCHAR":
            case "SETCHAR":
                if (count($split_line) !== 4 || !preg_match($non_t['var'], $split_line[1]) ||
                !check_symb($split_line[2]) || !check_symb($split_line[3])){
                    echo 23;
                    exit (23);
                }
                $el = write_xml($xml, $instr, $counter++);
                write_xml_arg('var', $split_line[1], $el, 'arg1');

                $symb = check_symb($split_line[2]);
                $symb2 = check_symb($split_line[3]);

                if ($symb == 'var'){
                    write_xml_arg($symb, $split_line[2], $el, 'arg2');
                } else {
                    $arg = explode('@', $split_line[2], 2);
                    write_xml_arg($symb, $arg[1], $el, 'arg2');
                }

                if ($symb2 == 'var'){
                    write_xml_arg($symb2, $split_line[3], $el, 'arg3');
                } else {
                    $arg = explode('@', $split_line[3], 2);
                    write_xml_arg($symb2, $arg[1], $el, 'arg3');
                }
                break;
            case "":
                break;
            default:
                echo 22;
                exit (22);
        }
    }
    
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;
    echo $dom->saveXML();
?>