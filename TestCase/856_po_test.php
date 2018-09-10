<?php 
require_once("../Milk_EDI.php");
$ponum 		= "";		
$result 	= Milk_EDI::GeneratePO856ByPonum($ponum);
echo '<pre>';
var_dump($result);
echo "</pre>";
?>