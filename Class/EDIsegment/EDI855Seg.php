<?php 
/**

	855 only sgements
*/
require_once(dirname(__FILE__) . "/Segment.php");
class BAK extends Segment 
{	
	//po
	const BAK02_AC = "AC";			//AC Acknowledge - With Detail and Change po
									//po 
	const BAK02_AD = "AD";			//AD Acknowledge - With Detail, No Change po
									//po should be used if no change apply to quantity and price.
	//ds
	CONST BAK02_AT = 'AT'; 			//If ALL line items on the order are accepted, use 'AT'.
	CONST BAK02_RD = 'RD'; 			//If ANY line items on the order are rejected, use 'RD'.
	const BAK02_ACCEPT = "AT";		//AMAZON x12 If ALL line items on the order are accepted,
	const BAK02_REJECT = "RT";		//AMAZON x12 If ANY line items on the order are rejected,
	//not in use
	const BAK02_AE = "AE";			//AE Acknowledge - With Exception Detail Only
	const BAK02_AK = "AK";			//AK Acknowledge - No Detail or Change
	const BAK02_AP = "AP";			//AP Acknowledge - Product Replenishment
	const BAK02_RJ = "RJ";			//RJ Rejected - No Detail
	public function __construct($array)
	{
		parent::__construct($array);
	}
	public static function GenerateBAK_PO($bak02,$poNum)
	{
		$array   = array("BAK");
		//bak 01
		$array[] = "00";
		//bak 02
		$array[] = $bak02;
		//bak 03
		$array[] = $poNum;
		//bak 04
		$array[] = Milk_EDIfunctionl::ccyymmdd();
		return new BAK($array);
	}
	public static function GenerateBAK_DR($bak02,$poNum,$bak08)
	{
		$array   = array("BAK");
		//bak 01
		$array[] = "00";
		//bak 02
		$array[] = $bak02;
		//bak 03
		$array[] = $poNum;
		//bak 04
		$array[] = Milk_EDIfunctionl::ccyymmdd();
		//bak 05
		$array[] = "";
		//bak 06
		$array[] = "";
		//bak 07
		$array[] = "";
		//bak 08
		$array[] = $bak08;
		return new BAK($array);
	}
}
?>