<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../Milk_EDI.php');
$ponum 					= '';
Milk_EDI::Accept850AndSend855($ponum);
echo "1";
?>