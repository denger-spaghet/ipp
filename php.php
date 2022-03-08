<?php
$idk = 'zz';
$yes = "hello world $idk".PHP_EOL;
echo $yes;

function no (String $string) : int {
    return 1;
}
if (no("bler")){
    echo no("ble");
} else {
    echo 2;
}

$bool = true;

switch ($bool){
    case true:
        echo 'true';
        break;
    case false:
        echo 'false';
        break;
}
$arr = [
    'name' => 'Fero',
    'pass' => 'dfsdf'
];

foreach ($arr as $key => $item){

    echo "$key : $item".PHP_EOL;
}

Class NazovTriedy {

    private $name;
    private $data;

    function __construct ($name, $data){
        $this->name = $name;
        $this->data = $data;
    }

    public function getName () {
        return $this->name;
    }

    public function getData () {
        return $this->data;
    }
}

Class Next extends NazovTriedy {
    
}

$objekt = new NazovTriedy('meno', 'dataaa');

echo $objekt->getName();
?>