<?php 
/**

	850,855,856 GE sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class GE extends Segment
{	
	const DEFAULTGE01 = "1";						//always 1 set
	public function __construct($array)
	{	
		if (sizeof($array)!=3) {
			throw new Exception("invalid GE init array size");
		}
		parent::__construct($array);

	}
	public function getGE01()
	{
		return parent::getValueOfIndex(1);
	}
	/*
		GE 02				:(string)[1/9][m]Group Control Number
								Same as GS06
	*/
	public static function GenerateGE($ge02){
		$array = array("GE");
		//ST 01
		$array[] = self::DEFAULTGE01;
		//ST 02
		$array[] = $ge02;
		return new GE($array);
	}

}
?>