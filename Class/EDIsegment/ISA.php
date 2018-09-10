<?php 
/**
*	michael lee production
*	require EDIfunctionl.php file
*	850,855,856,846 ISA sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class ISA extends Segment
{	
	const DEFAULT01 			= "00";				//isa01
	const TENSPACE 				= '          '; 		//isa 02 04
	const DEFAULT03 			= "00";				//isa03
	
	/*ISA05*/
	const ISA05ZZ 				= "ZZ";				//mutually defined
	const ISA0501 				= '01'; 				//Duns (Dun & Bradstreet)
	const ISA0512 				= "00";				//Phone (Telephone Companies)
	/*ISA05 end*/
	
	const DEFAULT07 			= "ZZ";				//isa07
	/*ISA 08*/
	const DEFAULT08 			= "AMAZONDS";		//isa08 amazon default
	const ISA08_856 			= "AMAZON";			//AMAZON 856 ISA 08 US
	/*ISA 08 END*/
	const DEFAULT11 			= "U";
	const ISA11_856 			= "^";				//
	const DEFAULT12 			= "00401";			//AMAZON 850
	const ISA12_855 			= "00401";			//AMAZON 855
	const ISA12_856 			= "00401";			//AMAZON 856 
	CONST ISA12_846 			= "00401"; 			//AMAZON 846
	CONST ISA12_855_PO 			= '00400'; 			//AMAZON PO 855
	CONST ISA12_856_PO 			= '00501'; 			//AMAZON PO 856
	const DEFAULT14 			= "1";
	/* ISA 14 */
	const ISA14_1 				= '1';				//request ackowledgement
	const ISA14_0 				= '0'; 				//no request ackownledge
	/*ISA15*/
	const DEFAULT15 			= "P";
	const TEST15 				= "T";
	/*ISA15 end*/
	CONST ISA16_DEFAULT 		= '>';
	CONST ISA16_846 			= '+';
	/**
	*	@param $array 					(array)size of 17
	*/
	function __construct($array)
	{	
		if (sizeof($array)!=17) {
			throw new Exception("invalid ISA init array size");
		}
		parent::__construct($array);
	}
	function getISAarray(){
		return parent::getData();
	}
	/*
		amazon 850 ISA 06
		Interchange Sender ID
	*/
	public function getISA06(){
		return parent::getValueOfIndex(6);
		//return $this->ISAarray[6];
	}
	/*
		amazon 850 ISA 08
		Interchange Receiver ID
	*/
	public function getISA08(){
		return parent::getValueOfIndex(8);
	}
	/*
		amazon 850 ISA 13
		Interchange Control Number
		This field will be a unique control number representing the ISA - IEA transaction.
	*/
	public function getISA13(){
		return parent::getValueOfIndex(13);
	}
	/*
		isa 08:						(string)Interchange Receiver ID
		isa 12:					 	(string)Interchange Control Version Number
		isa 13:						(string)Interchange Control Number
		isa 14:						(string)[1/1]Acknowledgment Requested
										1 ack
										0 no ack
		isa 16:
		isa 11:						(string)[1/1]Repetition Separator (amazon 855 :U)
	*/
	public function GenerateNewISABy850ISA($isa08,$isa12,$isa13,$isa14,$isa15,$isa16,$isa11 = self::DEFAULT11){

		$array = array("ISA");
		//ISA 01
		$array[] = self::DEFAULT01;
		//ISA 02
		$array[] = self::TENSPACE;
		//ISA 03
		$array[] = self::DEFAULT03;
		//ISA 04
		$array[] = self::TENSPACE;
		//ISA 05
		$array[] = self::ISA05ZZ;
		//ISA 06
		$array[] = $this->getISA08();
		//ISA 07
		$array[] = self::DEFAULT07;
		//ISA 08
		$array[] = $isa08;
		//ISA 09
		$array[] = Milk_EDIfunctionl::yymmdd();
		//ISA 10
		$array[] = Milk_EDIfunctionl::hhmm();
		//ISA 11
		$array[] = $isa11;
		//ISA 12
		$array[] = $isa12;
		//ISA 13
		$array[] = $isa13;
		//ISA 14
		$array[] = $isa14;
		//ISA 15 
		$array[] = $isa15;
		//ISA 16
		$array[] = $isa16;
		return new ISA($array);
	}
	public static function GenerateISA($isa06,$isa08,$isa12,$isa13,$isa14,$isa15,$isa16,$isa11 = self::DEFAULT11)
	{
		$array = array("ISA");
		//ISA 01
		$array[] = self::DEFAULT01;
		//ISA 02
		$array[] = self::TENSPACE;
		//ISA 03
		$array[] = self::DEFAULT03;
		//ISA 04
		$array[] = self::TENSPACE;
		//ISA 05
		$array[] = self::ISA05ZZ;
		//ISA 06
		$array[] = $isa06;
		//ISA 07
		$array[] = self::DEFAULT07;
		//ISA 08
		$array[] = $isa08;
		//ISA 09
		$array[] = Milk_EDIfunctionl::yymmdd();
		//ISA 10
		$array[] = Milk_EDIfunctionl::hhmm();
		//ISA 11
		$array[] = $isa11;
		//ISA 12
		$array[] = $isa12;
		//ISA 13
		$array[] = $isa13;
		//ISA 14
		$array[] = $isa14;
		//ISA 15 
		$array[] = $isa15;
		//ISA 16
		$array[] = $isa16;
		return new ISA($array);
	}
}
?>