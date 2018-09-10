<?php
/**
* michael lee production
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('/Class/MilkObject.php');
echo "Installation start<br>";

$structure 			= file('install_sql.sql');
MilkObject::initDb_helper();
$Tran 				= MilkObject::getTransaction();
MilkObject::beginTransaction($Tran);
$templine = '';
$flag 				= true;
foreach ($structure as $line)
{
	if (substr($line, 0, 2) == '--' || $line == '')
	    continue;
	$templine .= $line;
	if (substr(trim($line), -1, 1) == ';')
	{
	    $result 			= MilkObject::queryInsertWithTran($templine,$Tran);
	    if (!$result) {
	    	$flag 			= false;
	    	return;
	    }
	    //mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
	    $templine = '';
	}
}
$all_address 			= file('address_mapping_install.sql');
foreach ($all_address as $line)
{
	if (substr($line, 0, 2) == '--' || $line == '')
	    continue;
	$templine .= $line;
	if (substr(trim($line), -1, 1) == ';')
	{
	    $result 			= MilkObject::queryInsertWithTran($templine,$Tran);
	    if (!$result) {
	    	$flag 			= false;
	    	return;
	    }
	    //mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
	    $templine = '';
	}
}
if ($flag) {
	MilkObject::commitTransaciton($Tran);
	echo "Successfully install all tables";
}else{
	MilkObject::rollbackTransaction($Tran);
	echo "fail to install table";
}
?>

