<?php 
/**
//require EDIfunctionl php file
	850,855,856 CTT sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class CTT extends Segment
{	
	public function __construct($array)
	{	
		if (sizeof($array)!=3) {
			throw new Exception("invalid CTT init array size");
		}
		parent::__construct($array);

	}
	/*
		DR:
		CTT 01				:(string)[1/6][m] Number of Line Items
								Always the number of line items.
		CTT 02				:(String)[1/10][o]	Hash Total
								Always Qty shipped.

		PO::
		CTT 01				:(string)[1/6][n0] Number of Line Items
								This field will be the number of line the logical count of PO1 segments.
		CTT 02				:(String)[1/10][r]	Hash Total
								his field will be the sum of the value of quantities ordered 
	*/
	public static function GenerateCTT($ctt01,$ctt02){
		$array = array("CTT");
		//ST 01
		$array[] = $ctt01;
		//ST 02
		$array[] = $ctt02;
		return new CTT($array);
	}
	public function getCTT02()
	{	
		return $this->getValueOfIndex(2);
	}
}	
?>