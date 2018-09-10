<?php
/*send 855*/
require_once('Milk_EDI.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<title>Send 855 File</title>";
$list = array('31-170830-11165',
				);
for($i = 0; $i < sizeof($list); $i++){
	Milk_EDI::Accept850AndSend855($list[$i]);
}

echo '1';
?>