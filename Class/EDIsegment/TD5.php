<?php 
/**
//require EDIfunctionl php file
	850,855,856 ST sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
/**
*  850 TD5 
*/
class TD5 extends Segment
{
	/*TD5 02*/
	const TD502_92 = "92"; 				//[???]amazon TD5 02,Assigned by Buyer or Buyer's Agent
	const TD502_02 = "2"; 				//[856]Standard Carrier Alpha Code (SCAC)
	/*TD5 02 END*/ 	
	function __construct($array)
	{
		parent::__construct($array);
	}
	public static function IsTD5array($array)
	{
		if ($array[0]=="TD5") {
			return true;
		}else{
			return false;
		}
	}
	/*
		856 td5 generation
		td5 02:							(const) Identification Code Qualifier
											2 Standard Carrier Alpha Code (SCAC)
		td5 03:							(string) Identification Code
											Amazon.com defined codes indicating shipping carrier and shipment service level. These ship methods have to be returned verbatim.
	*/
	public static function GenerateTD5($td503,$td502=self::TD502_92)
	{
		$array = array("TD5");
		//TD5 01
		$array[] = "";
		//TD5 02
		$array[] = $td502;
		//td5 03
		$array[] = $td503;
		return new TD5($array);
	}
}
?>