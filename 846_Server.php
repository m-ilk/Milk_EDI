<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__) ."/Milk_EDI.php");
echo "1";
CONST USER_ACCOUUNT = 'amv0001';
$update_products 	= Milk_EDIProduct::getNeed2UpdateProducts();
/*echo "<pre>";
var_dump($update_products);
echo "</pre>";*/
$result 			= Milk_EDI::Generate846AndSend($update_products,USER_ACCOUUNT);
var_dump($result);
?>