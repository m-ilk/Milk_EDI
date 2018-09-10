<?php 
/**
//require EDIfunctionl php file
	850,REF CTT sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class REF extends Segment
{	
	const REF01_CN = "CN"; 			//Carrier's Tracking/Airbill Number
	const REF01_Z = "Z";	 		//X12 Mutually Defined
	const REF08_LB = "LB";			//X12 Pound
	public function __construct($array)
	{	
		if (sizeof($array)<3) {
			throw new Exception("invalid REF init array size");
		}
		parent::__construct($array);

	}
	/*
		REF 01						:(string)[2/3][m] Reference Identification Qualifier
										
		REF 02						:(String)[1/50][m]Reference Identification
										rackingnumber
	*/
	public static function GenerateREF($REF01,$REF02){
		$array = array("REF");
		//ref 01
		$array[] = $REF01;
		//ref 02
		$array[] = $REF02;
		return new REF($array);
	}
	public static function GenerateREF_X12($REF07){
		$array = array("REF");
		//ref 01
		$array[] = self::REF01_Z;
		//ref 02
		$array[] = "";
		//ref 03
		$array[] = "";
		//ref 04
		$array[] = "";
		//ref 05
		$array[] = "";
		//ref 06
		$array[] = "";
		//ref 07
		$array[] = $REF07;
		//ref 08
		$array[] = self::REF08_LB;
		return new REF($array);
	}
}	
?>