<?php 
/**

	850,855,856 SE sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class SE extends Segment
{	
	public function __construct($array)
	{	
		if (sizeof($array)!=3) {
			throw new Exception("invalid SE init array size");
		}
		parent::__construct($array);

	}
	public function getSE02()
	{
		return $this->getValueOfIndex(2);
	}
	/*
		SE 01				:(string)[1/6][m] Number of Included Segments 
								(From ST to SE)
		SE 02				:(String)[1/10][m]	Transaction Set Control Number
								Same as ST02
	*/
	public static function GenerateSE($se01,$se02){
		$array = array("SE");
		//ST 01
		$array[] = $se01;
		//ST 02
		$array[] = $se02;
		return new SE($array);
	}

}
?>