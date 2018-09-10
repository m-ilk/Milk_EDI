<?php 
/**
	855 ACK sgement
	$array:				size of 5
		(ACK01) 			:(const)[M] (2/2)above
		(ACK02) 			:(int)[M](1/15) Quantity
									input: -1 match with 850 PO1 quantity
		(ACK03) 			:(string)[M](2/2) Unit or Basis for Measurement Code
									CA Case
									EA Each
		(ACK04) 			:(string)[O]Date/Time Qualifier	
									068 Current Schedule Ship								
		(ACK05) 			:(string)[C]Date
									CCYYMMDD
									If you can provide Estimated ship dates per line item, you have to state these in ACK05 with ACK04 qualifier 068.

*/
require_once(dirname(__FILE__) . "/Segment.php");
class ACK extends Segment
{	
	/* ACK01*/
	const ACK01_BP = "BP"; 			//Item Accepted - Partial Shipment, Balance Backordered
	const ACK01_IA = "IA";			//Item Accepted
	const ACK01_IB = "IB";  		//Item Backordered
	const ACK01_IQ = "IQ"; 			//Item Accepted - Quantity Changed
	const ACK01_IR = "IR"; 			//Item Rejected
	const ACK01_R2 = "R2";			//Item Rejected, Invalid Item Product Number[hard reject]
	const ACK01_R3 = "R3";			//Item Rejected, Invalid Unit of Issue
	/* ACK01 end*/
	CONST ACK03_EA = 'EA'; 			//each
	
	const ACK04_068 = "068";		//Current Schedule Ship
	/*ACK29 */
	const ACK29_00 = "00";			//Shipping 100 percent of ordered product
	const ACK29_02 = "02";			//Canceled due to missing/invalid SKU
	const ACK29_03 = "03";			//Canceled out of stock
	const ACK29_04 = "04";			//Canceled due to duplicate Amazon Ship ID
	/*END more in x12 855*/
	function __construct($array)
	{	
		if (sizeof($array)<3) {
			throw new Exception("invalid ACK init array size");
		}
		parent::__construct($array);

	}
	public static function GenerateACK($ack01,$ack02,$ack03,$ack29,$ack04="",$ack05="")
	{
		$array = array("ACK");
		//ack 01
		$array[] = $ack01;
		//ack 02
		$array[] = $ack02;
		//ack 03
		$array[] = $ack03;
		//ack 04
		$array[] = "";
		//ack 05
		$array[] = "";
		for ($i=6; $i <29 ; $i++) { 
			$array[]="";
		}
		//ack 29
		$array[] = $ack29;
		return new ACK($array);
	}
	/**
	*	confirm quantity with out quantity changed
	*	@param ack 01 							(const) Item Status Code
	*	@param ack 02 							(string) quantity
	*/
	public static function GenerateAcceptPoAck($ack01,$ack02)
	{
		$ack1_array = array(
			'ACK',
			$ack01,
			$ack02,
			ACK::ACK03_EA,
			ACK::ACK04_068,
			Milk_EDIfunctionl::ccyymmdd()
		);
		$ACK 				= new ACK($ack1_array); 
		return $ACK;
	}
	/**
	*	confirm quantity with out quantity changed
	*	@param ack 01 							(const) Item Status Code
	*	@param ack 02 							(string) quantity
	*/
	public static function GenerateRejectPoAck($ack01,$ack02)
	{
		$ack1_array
		= array(
			'ACK',
			$ack01,
			$ack02,
		);
		$ACK 				= new ACK($ack1_array); 
		return $ACK;
	}
}
?>