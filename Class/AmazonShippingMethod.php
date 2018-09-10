<?php
/**
* 	michael lee production
*	available shipping method
*	used for controllers
*/
require_once('Milk_EDIConfig.php');
require_once(Milk_EDIConfig::MilkObject_path);
class AmazonShippingMethod extends MilkObject
{
	private static $array = array(
									'UPS_GROUND'	=>'UPS ground',
									'FedEx_GROUND'	=>'FedEx ground',
									'Freight'		=>'Freight Pick Up(LTL)'
									);
	function __construct()
	{
		
	}
	public static function getMethods(){
		return self::$array;
	}
}
?>