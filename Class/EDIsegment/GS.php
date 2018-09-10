<?php 
/**
//require EDIfunctionl php file
	850,855,856 GS sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class GS extends Segment
{	
	/*GS01*/
	const GSO1PR						= "PR";					//purchase Order Acknowledgment(855)
	const GS01PO						= "PO";					//Purchase Order (850)
	const GS01SH						= "SH";					//Ship Notice/Manifest (856)
	CONST GS01IB						= "IB"; 				//Inventory Inquiry/Advice (846)
	/*GS01 end*/

	/*GS03 856 */
	const GS03AMAZON 					= "AMAZON";				//AMAZON 856 us store
	const GS03AMAZONDS 					= "AMAZONDS"; 			//AMAZON 855 
	/*GS03 856 end*/

	const DEFAULT07 					= "X";				//Responsible Agency Code(amazon)
	const GS08_850 						= "00410";			//amazon gs default 08
	const GS08_855 						= "004010";			//amazon gs default 08
	const GS08_856 						= "005010";			//amazon gs default 08
	public function __construct($array)
	{	
		if (sizeof($array)<6) {
			throw new Exception("invalid GS init array size");
		}
		parent::__construct($array);
	}
	public function getGSarray(){
		return parent::getData();
	}
	/*
		850 GS 03
		vendor ID
	*/
	public function getGS03(){
		return parent::getValueOfIndex(3);
	}
	/*
		850 GS 06
		Group Control Number
	*/
	public function getGS06(){
		return parent::getValueOfIndex(6);
	}
	/*
		gs01 							:(const)[2/2]Functional Identifier Code
		gs02							:(string)[2/15] Application Sender's Code, Vendor id# (from 850)
		gs03 							:(const)[2/15] Application Receiver's Code

	*/
	public function GenerateNewGSBy850GS($gs01,$gs03,$gs08 = self::GS08_850){
		$array = array("GS");
		//GS 01
		$array[] = $gs01;
		//GS 02
		$array[] = $this->getGS03();
		//GS 03
		$array[] = $gs03;
		//GS 04
		$array[] = Milk_EDIfunctionl::ccyymmdd();
		//GS 05
		$array[] = Milk_EDIfunctionl::hhmm();
		//GS 06
		$array[] = $this->getGS06();
		//GS 07
		$array[] = self::DEFAULT07;
		//GS 08
		$array[] = $gs08;
		return new GS($array);
	}
	public static function GenerateGS($gs01,$gs02,$gs03,$gs06,$gs08 = self::GS08_850)
	{
		$array = array("GS");
		//GS 01
		$array[] = $gs01;
		//GS 02
		$array[] = $gs02;
		//GS 03
		$array[] = $gs03;
		//GS 04
		$array[] = Milk_EDIfunctionl::ccyymmdd();
		//GS 05
		$array[] = Milk_EDIfunctionl::hhmm();
		//GS 06
		$array[] = $gs06;
		//GS 07
		$array[] = self::DEFAULT07;
		//GS 08
		$array[] = $gs08;
		return new GS($array);
	}
}
?>