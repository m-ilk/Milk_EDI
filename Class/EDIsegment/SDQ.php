<?php 
/**
*	michael lee production
* 	required for 846
*	Destination Quantity
*/
require_once(dirname(__FILE__) . "/Segment.php");
class SDQ extends Segment
{
	CONST SDQ01_EA 							= 'EA'; //Each
	CONST SDQ02_92 							= '92'; //Assigned by Buyer or Buyer's Agent

	function __construct($array)
	{
		parent::__construct($array);
	}
	/*
		856 td5 generation
		SDQ 03:							(string) Identification Code
											his will be the Amazon code representing the warehouse that the order should ship from.
											This is usually a four character alpha code (e.g. ABCD).
		SDQ 04:							(string) Quantity
											Available Quantity
											If an item is out of stock, send "0", do not leave blank
	*/
	public static function GenerateSDQ($SDQ03,$SDQ04,$SDQ01= self::SDQ01_EA,$SDQ02=self::SDQ02_92)
	{
		$array = array("SDQ");
		//SDQ 01
		$array[] = $SDQ01;
		//SDQ 02
		$array[] = $SDQ02;
		//SDQ 03
		$array[] = $SDQ03;
		//SDQ 04
		$array[] = $SDQ04;
		return new SDQ($array);
	}
}
?>