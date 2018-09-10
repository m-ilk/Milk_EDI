<?php 
/**
*	michael lee production
* 	Item Identification
*	used for 856,846
*/
require_once(dirname(__FILE__) . "/Segment.php");
class LIN extends Segment
{
	/*LIN 02*/
	const LIN02_SK = "SK";				//Stock Keeping Unit (SKU)
	CONST LIN02_UP = 'UP'; 				//U.P.C. Consumer Package Code (1-5-5-1)
	CONST LIN02_VN = 'VN'; 				//VN Vendor's (Seller's) Item Number
	/*LIN 02 END*/
	public static $COUNT = 1;
	function __construct($array)
	{
		parent::__construct($array);
	}
	/**
	*	@param LIN01:							(string)Assigned Identification
	*										
	*	@param LIN03: 							(string)Product/Service ID
	*											
	*/
	public static function Generate846LIN($lin03,$lin02=self::LIN02_SK ){
		$array = array("LIN");
		//lin 01
		$array[] = self::$COUNT;
		self::$COUNT++;
		//lin 02
		$array[] = $lin02;
		//lin 03
		$array[] = $lin03;
		return new LIN($array);
	}
	/**
	*	@param LIN01:							(string)Assigned Identification
	*										Alphanumeric characters assigned for differentiation within a transaction set Same as PO101 in the 850
	*	@param LIN03: 							(string)Product/Service ID
	*											Should match PO107 from 850/855
	*/
	public static function Generate856LIN($lin01,$lin03,$lin02=self::LIN02_SK ){
		$array = array("LIN");
		//lin 01
		$array[] = $lin01;
		//lin 02
		$array[] = $lin02;
		//lin 03
		$array[] = $lin03;
		return new LIN($array);
	}
	/**
	*	@param LIN01:							(string)Assigned Identification
	*												This will be a unique line number representing this item
	*	@param LIN 02: 							(string)Product/Service ID Qualifier
	*												UP UCC - 12
	*	@param LIN03: 							(string)Product/Service ID
	*												Should match PO107 from 850/855
	*/
	public static function Generate856LIN_PO($lin03,$lin02=self::LIN02_UP ){
		$array = array("LIN");
		//lin 01
		$array[] = self::$COUNT;
		self::$COUNT++;
		//lin 02
		$array[] = $lin02;
		//lin 03
		$array[] = $lin03;
		return new LIN($array);
	}
}
?>