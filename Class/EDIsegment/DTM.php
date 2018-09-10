<?php 
/**
* DTM Date/Time Reference- Ready To Ship
  		Optional 	
*/
class DTM extends Segment
{
	/*DTM 01*/
	const DTM01_ZZZ = "ZZZ"; 				//amazon DTM 01,READY TO SHIP
	const DTM01_011 = "011"; 				//amazon DTM 01,Shipped
	const DTM01_017 = "017";				//amazon DTM 01,Estimated Delivery
	CONST DTM01_63 	= '063'; 				//anazon DTM 01,Do Not Deliver After
	CONST DTM01_64  = '064'; 				//Do Not Deliver Before
	CONST DTM01_166 = '166';

	/*DTM 01 END*/ 	
	/*DTM 04*/
	const DTM04_GM = "GM"; 					//amazon DT 04
	/*DTM 04 END*/
	function __construct($array)
	{
		parent::__construct($array);
	}
	public function IsDTMarray($array)
	{
		if ($array[0]=='DTM') {
			return true;
		}else{
			return false;
		}
	}
	public function getDTM01()
	{
		return $this->getValueOfIndex(1);
	}
	public function getDTM02()
	{
		return $this->getValueOfIndex(2);
	}
	/*
		dtm 01:							(string) Date/Time Qualifier
		dtm 04:							(string) Time Code
	*/
	public static function GenerateDTM_RTS($date,$time,$dtm04="")
	{
		return self::GenerateDTM(self::DTM01_ZZZ,$date,$time,$dtm04);
		
	}
	public static function GenerateDTM_Shipped($date,$time,$dtm04="")
	{
		return self::GenerateDTM(self::DTM01_011,$date,$time,$dtm04);
		
	}
	/*
		dtm 01: 							(const) Date/Time Qualifier
		dtm 02: 							(string)[CCYYMMDD] Date
		dtm 03: 							(string) Time
		dtm 04: 							(string )Time Code
												GM Greenwich Mean Time
												UT Universal Time Coordinate
	*/
	public static function GenerateDTM($dtm01,$dtm02,$dtm03,$dtm04=self::DTM04_GM){
		$array = array("DTM");
		//dtm 01
		$array[] = $dtm01;
		//dtm 02
		$array[] = $dtm02;
		//dtm 03
		$array[] = $dtm03;
		//dtm 04
		$array[] = $dtm04;
		return new DTM($array);
	}
	/*
	 AMAZON X12  
	*/
	 public static function GenerateDTM_X12($dtm01,$dtm02){
		$array = array("DTM");
		//dtm 01
		$array[] = $dtm01;
		//dtm 02
		$array[] = $dtm02;
		return new DTM($array);
	}
	/**
	*	846 dtn 
	*	see  dropship x12 846 
	*/
	public static function Generate846DTM()
	{
		$array = array("DTM");
		//dtm 01
		$array[] = DTM::DTM01_166;
		//dtm 02
		$array[] = Milk_EDIfunctionl::ccyymmdd();
		//dtm 03
		$array[] = Milk_EDIfunctionl::hhmm();
		return new DTM($array);
	}
}
?>