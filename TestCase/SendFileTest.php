<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("Milk_EDI.php");
CONST SEND_FROM = '';
CONST SEND_TO 	= '';

$file_path='Files/170427/1676/855_DYJhzPC0k';
var_dump(Milk_EDI::SendFile(SEND_FROM,SEND_TO,$file_path));
echo "123";
?>