<?php 
/*
michael lee production
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('Milk_EDI.php');
$path                       = 'po.payload_0';
$result                     = Milk_EDI::InsertPo850($path);
echo "<pre>";
var_dump($result);
echo "</pre>";


?>