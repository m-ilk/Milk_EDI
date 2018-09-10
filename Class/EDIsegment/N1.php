<?php 
/**
	856 N1 sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class N1 extends Segment
{	
	/*N1 01*/
	const N101_SF = "SF"; 					//Ship From
	const N101_ST = "ST";					//Ship To
	const N101_BT = "BT";					//Bill to party
	CONST N101_WH = 'WH'; 					//Warehouse
	CONST N101_ZZ = 'ZZ'; 					//ZZ
	/*N1 01 END*/
	/*N1 03*/
	const N103_92 = "92";					//Assigned by Buyer or Buyer's Agent
	const N103_15 = "15";					//Standard Address Number (SAN)
	function __construct($array)
	{	
		if (sizeof($array)<3) {
			throw new Exception("invalid N1 init array size");
		}
		parent::__construct($array);
	}
	public function getN102(){
		return $this->getValueOfIndex(2);
	}
	public function getN104(){
		return $this->getValueOfIndex(4);
	}
	/*
		n101: 				(const)
		N102:				(string)[1/60] 	Name
								same as 850 BEG03 
		N104:				(string)[2/80] 	Contract Number
								Vendor Order Reference Number(orders table id)
	*/
	public static function GenerateN1($n101,$n102,$n103,$n104){
		$array = array("N1");
		//n1 01
		$array[] = $n101;
		//prf 02
		$array[] = $n102;
		//prf 03
		$array[] = $n103;
		//prf 04
		$array[] = $n104;
		return new N1($array);
	}
	public static function IsN1_STarray($array)
	{
		if ($array[0]=="N1"&&$array[1]=='ST') {
			return true;
		}else{
			return false;
		}
	}
	public static function IsN1_SFarray($array){
		if ($array[0]=="N1"&&$array[1]=='SF') {
			return true;
		}else{
			return false;
		}
	}
	public static function Generate846N1($Warehouse)
	{
		$array = array("N1");
		//n1 01
		$array[] = self::N101_ZZ;
		//prf 02
		$array[] = $Warehouse;
		//prf 03
		$array[] = self::N103_92;
		//prf 04
		$array[] = $Warehouse;
		return new N1($array);
	}
}	
?>