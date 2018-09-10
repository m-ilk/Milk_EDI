<?php 
/**

	850,855,856 IEA sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class IEA extends Segment
{	
	const DEFAULTIEA01 = "1";						//always 1 set
	public function __construct($array)
	{	
		if (sizeof($array)!=3) {
			throw new Exception("invalid IEA init array size");
		}
		parent::__construct($array);

	}
	/*
		GE 02				:(string)[1/9][m]Group Control Number
								Same as GS06
	*/
	public static function GenerateIEA($IEA02){
		$array = array("IEA");
		//ST 01
		$array[] = self::DEFAULTIEA01;
		//ST 02
		$array[] = $IEA02;
		return new IEA($array);
	}

}
?>