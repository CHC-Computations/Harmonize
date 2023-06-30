<?php
require_once('loader.php');

/* 
Skrypt do używania z poziomu terminala. 

*/

echo "starting: ".date("H:i:s")."\n";
$BASE_PATH = 'recfiles';

$maxLL = 100;
$len = 10;
$lp = 0;	
$LP = 0;


$glob = glob("$BASE_PATH/*");

$row = 0;
$file = file("$BASE_PATH/100a.csv");
if (($handle = fopen("$BASE_PATH/100a.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }
    }
    fclose($handle);
}


?>


