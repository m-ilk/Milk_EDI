<?php 
require_once(dirname(__FILE__) . "/Segment.php");
/**
*	Segments that are only used in EDI 856 
		BSN
		HL
		PRF
		LIN
		SN1
		MAN
		SLN

		TD1
		DTM
*/
/**
*856 segment
Beginning Segment for Ship Notice 
*/
class BSN extends Segment 
{
	/*BSN01*/
	const DEFAULT01 					= "00";				//amazon original
	const BSN01_05  					= "05";				//amazon replace
	/*BSN05*/
	const DEFAULT05 					= "ZZZZ";			//amazon default
	CONST BSN05_856 					= "0001";
	/*BSN06*/
	const BSN0672 						= "72";				//amazon If the entire shipment has been rejected then use '72'
	const BSN06AS 						= "AS";				//amazon If the part or all of the shipment has been sent then use 'AS'

	/*BSN07*/
	const BSN07NOR 						= "NOR";				//amazon: will indicate any or all of the items have been shipped.
	const BSN07REJ 						= "REJ";				//amazon: will indicate the the entire shipment has been cancelled.
	private $BSNarray;
	public function __construct($array)
	{
		if (sizeof($array)<6) {
			throw new Exception("invalid BSN init array size");
		}
		parent::__construct($array);
	}
	/*
		bsn02					:(string)[2/30] Shipment Identification
									Vendor's internal shipment identification number.
	*/
	public static function GenerateBSN($bsn02){
		$array =array("BSN");
		//bsn 01
		$array[] = self::DEFAULT01;
		//bsn 02
		$array[] = $bsn02;
		//bsn 03
		$array[] = Milk_EDIfunctionl::ccyymmdd();
		//bsn 04
		$array[] = Milk_EDIfunctionl::hhmmss();
		//bsn 05
		$array[] = self::DEFAULT05;
		//bsn 06
		$array[] = self::BSN06AS;
		//bsn 07
		$array[] = self::BSN07NOR;
		return new BSN($array);
	}
	public static function GenerateBSN_PO($bsn02)
	{
		$array =array("BSN");
		//bsn 01
		$array[] = self::DEFAULT01;
		//bsn 02
		$array[] = $bsn02;
		//bsn 03
		$array[] = Milk_EDIfunctionl::ccyymmdd();
		//bsn 04
		$array[] = Milk_EDIfunctionl::hhmmss();
		//bsn 05
		$array[] = self::BSN05_856;
		return new BSN($array);
	}
}
/**
	856 HL sgement
*/
class HL extends Segment
{	
	/* $hl03*/
	const HL03_O = "O";
	const HL03_I = "I";
	const HL03_P = "P";
	const HL03_S = "S";
	/*hl03 end*/
	function __construct($array)
	{	
		if (sizeof($array)<3) {
			var_dump($array);
			throw new Exception("invalid HL init array size");
		}
		parent::__construct($array);
	}
	/*
		HL01:				(int) 	Hierarchical ID Number
		hl02:				(int)   Hierarchical Parent ID Number
	*/
	public static function GenerateHL_S($hl01,$hl02=""){
		return self::GenerateHl($hl01,$hl02,self::HL03_S);
	}
	public static function GenerateHL_O($hl01,$hl02=""){
		return self::GenerateHl($hl01,$hl02,self::HL03_O);
	}
	public static function GenerateHL_I($hl01,$hl02=""){
		return self::GenerateHl($hl01,$hl02,self::HL03_I);
	}
	public static function GenerateHL_P($hl01,$hl02=""){
		return self::GenerateHl($hl01,$hl02,self::HL03_P);
	}
	public static function GenerateHl($hl01,$hl02,$hl03){
		$array = array("HL");
		//HL01
		$array[] = (string)$hl01;
		//HL02 
		$array[] = (string)$hl02;
		//HL 03
		$array[] = $hl03;
		return new HL($array);
	}
}


/**
	856 PRF sgement
*/
class PRF extends Segment
{	

	function __construct($array)
	{	
		if (sizeof($array)<1) {
			throw new Exception("invalid PRF init array size");
		}
		parent::__construct($array);
	}
	/**
	*	@param prf01:				(string)[1/22] 	Purchase Order Number
	*								same as 850 BEG03 
	*	@param prf06:				(string)[1/30] 	Contract Number
	*								Vendor Order Reference Number(orders table id)
	*/
	public static function GeneratePRF_856($prf01,$prf06){
		$array = array("PRF");
		//prf 01
		$array[] = $prf01;
		//prf 02
		$array[] = "";
		//prf 03
		$array[] = "";
		//prf 04
		$array[] = "";
		//prf 05
		$array[] = "";
		//prf 06
		$array[] = $prf06;
		return new PRF($array);
	}
	/**
	*	@param prf01:				(string)[1/22] 	Purchase Order Number
	*								same as 850 BEG03 
	*/
	public static function GeneratePRF_PO856($prf01)
	{
		$array = array("PRF");
		//prf 01
		$array[] = $prf01;
		return new PRF($array);
	}
}


/**
	* 856 SN1 Segment
	Item Detail
	To specify line-item detail relative to shipment
*/
class SN1 extends Segment
{
	/*SN 103*/
	const DEFAULTSN103 = "EA";					//Each 
	/*SN 103 END*/
	/*SN06*/
	const DEFAULTSN106 = "EA";					//Each 
	/*SN 106 END*/
	/*SN08*/
	const SN108_IA = "IA";						// Item Accepted, if part or all the items are sent.
	const SN108_IR = "IR";						//Item Rejected is all of the items are cancelled or unavailable for shipment.
	/*SN1 08 END*/	
	function __construct($array)
	{
		parent::__construct($array);
	}
	/*
		sn101:									(String) Assigned Identification
													Same as PO101 in the 850
													Same as PO101 in the 855
													Same as LIN01 in this 856
		quantity_s								(string) [SN103] Number of Units Shipped
		quantity_o 								(string) [SN105] Quantity Ordered
													po102
		sn108 									(const) Line Item Status Code
	*/
	public static function GenerateSN1($sn01,$quantity_s,$quantity_o,$sn108,$sn103 = self::DEFAULTSN103){
		$array = array("SN1");
		//SN1 01
		$array[] = $sn01;
		//SN1 02
		$array[] = $quantity_s;
		//SN1 03
		$array[] =$sn103;
		//SN1 04
		$array[] = "";
		//SN1 05
		$array[] = $quantity_o;
		//SN1 06
		$array[] = self::DEFAULTSN106;
		//SN1 07
		$array[] = "";
		//SN1 08
		$array[] = $sn108;
		return new SN1($array);
	}
	/**
	*	sn101
	*/
	public static function GenerateSN1_PO($sn101,$sn102)
	{
		$array = array("SN1");
		//SN1 01
		$array[] = $sn101;
		//SN1 02
		$array[] = $sn102;
		//SN1 03
		$array[] = self::DEFAULTSN103;
		return new SN1($array);
	}
}
/**
* MAN Marks and Numbers
  		Optional 	
*/
class MAN extends Segment
{
	/*MAN 01*/
	const MAN01_R ="R"; 					//amazon default
	/*MAN 01 END*/
	/*MAN 04*/
	const MAN04_CP = "CP";					//Carrier-Assigned Package ID Number(CP if Carrier-Assigned)
	const MAN04_SM = "SM";					//Shipper Assigned (SM if Shipper-Assigned.)
	/*MAN 04 END*/
	function __construct($array)
	{
		parent::__construct($array);
	}
	/*
		$man02								(string)[m][1/48] Marks and Numbers
												Vendor assigned package ID.MAN02 at Package Level must equal the MAN02 at Line Item Level	
		$man03 								(string)[o][1/48] Manifest Number: Required for LTL shipments	
		$man 04								(const)[m]Marks and Numbers Qualifier
		$man 05 							(string)[m]Marks and Numbers
												Carrier assigned package tracking ID.
	*/
	public static function GenerateMAN_HL_P($man02,$man04,$man05,$man03="")
	{
		$array = array("MAN");
		//man 01
		$array[] = self::MAN01_R;
		//man 02
		$array[] = $man02;
		//man 03
		$array[] = $man03;
		//man 04
		$array[] = $man04;
		//man 05
		$array[] = $man05;
		return new MAN($array);
	}
	public static function GenerateMAN_HL_I($man02)
	{
		$array = array("MAN");
		//man 01
		$array[] = self::MAN01_R;
		//man 02
		$array[] = $man02;
		
		return new MAN($array);
	}
}
/**
* MAN Marks and Numbers
  		Optional 	
*/
class SLN extends Segment
{
	
	function __construct($array)
	{
		parent::__construct($array);
	}
}
/**
*		TD1 Carrier Details
*  		Mandatory[HL S]
*/
class TD1 extends Segment
{
	/*td1 01*/
	const TD101_CTN 					= 'CTN';			//amazon 01 [hl s] flat
	const TD101_Z 						= "Z";					//hl p X12
	CONST TD101_PLT 					= 'PLT';
	/*td1 01 END*/
	/*td1 06*/
	const TD106_G 						= "G";				//amazon always flat
	const TD106_Z 						= "Z";				//amazon always X12
	/*td1 06 end*/
	/*td1 08*/
	const TD108_LB  					= "LB"; 					//Pound
	/*td1 08 ends*/
	function __construct($array)
	{
		parent::__construct($array);
	}
	/*	
		td1 06: 							(const) Weight Qualifier
												G Gross Weight
		td1 07:								(String) weight
		td1 08:								(const) Unit or Basis for Measurement Code
												GR Gram
												KG Kilogram
												LB Pound
												OZ Ounce - Av		

	*/
	public static function GenerateTD1_X12($td107,$td106=self::TD106_Z)
	{
		$array = array("TD1");
		//td1 01
		$array[] = "";
		//td1 02
		$array[] = "";
		//td1 03
		$array[] = "";
		//td1 04
		$array[] = "";
		//td1 05
		$array[] = "";
		//td1 06
		$array[] = $td106;
		//td1 07
		$array[] = $td107;
		//td1 08
		$array[] = self::TD108_LB;
		return new TD1($array);
	}
	/**
	*	@param td1 01 						(const)Packaging Code
	*											CTN Carton
	*	@param td1 02  						(string)Lading Quantity
	*
	*/
	public static function GenerateTD1($td101,$td102,$td107,$td108,$td106=self::TD106_DEFAULT)
	{
		$array = array("TD1");
		//td1 01
		$array[] = $td101;
		//td1 02
		$array[] = $td102;
		//td1 03
		$array[] = "";
		//td1 04
		$array[] = "";
		//td1 05
		$array[] = "";
		//td1 06
		$array[] = $td106;
		//td1 07
		$array[] = $td107;
		//td1 08
		$array[] = $td108;
		return new TD1($array);
	}
	public static function GenerateTD1_PO($td101,$td102,$td107,$td108 = self::TD108_LB)
	{
		$array = array("TD1");
		//td1 01
		$array[] = $td101;
		//td1 02
		$array[] = $td102;
		//td1 03
		$array[] = "";
		//td1 04
		$array[] = "";
		//td1 05
		$array[] = "";
		//td1 06
		$array[] = self::TD106_G;
		//td1 07
		$array[] = $td107;
		//td1 08
		$array[] = $td108;
		return new TD1($array);
	}
	/**
	*	PO order
	*	HL s section
	*	Pallet Count s the total number of pallets making up a shipment.
	*	@param quantity 							(string) td1 02
	*/
	public static function GenerateTD1_plt($quantity)
	{
		$array = array("TD1");
		//td1 01
		$array[] = TD1::TD101_PLT;
		//td1 02
		$array[] = $quantity;
		return new TD1($array);
	}
}
?>