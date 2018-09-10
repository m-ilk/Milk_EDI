<?php
/**
* michael lee production
*	not implement
*/
require_once('Milk_EDIConfig.php');
require_once(Milk_EDIConfig::MilkObject_path);
class AmazonPurchaseOrderErrorLog extends MilkObject
{
	CONST TABLE ='amazon_purchase_order_error_log';
	function __construct()
	{

	}
	public static function createErrorLog($ponum,$method,$msg)
	{
		
	}
}
?>