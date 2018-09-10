<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../Milk_EDI.php');

$result = Milk_EDI::GeneratePO855ByPonum('1cgsb8qk');
echo "<pre>";
print_r($result);
echo "</pre>";
?>