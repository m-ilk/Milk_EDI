<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('Milk_EDI.php');

$list = array(
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
''
);
for($i = 0; $i < sizeof($list); $i++){
	Milk_EDI::deleteEDIfileWithTypeAndPOnum("856",$list[$i]);
}

echo '1';

?>