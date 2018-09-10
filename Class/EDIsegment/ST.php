<?php 
/**
//require EDIfunctionl php file
	850,855,856 ST sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class ST extends Segment
{	
	/*ST 01*/
	const ST850 ="850";					//850 edi
	const ST855 ="855";					//855 edi
	const ST856 ="856";					//856 edi
	const ST846 ="846";					//846 edi
	/*ST 01 end*/

	public function __construct($array)
	{	
		if (sizeof($array)!=3) {
			throw new Exception("invalid ST init array size");
		}
		parent::__construct($array);

	}
	public function getSTarray(){
		return parent::getData();
	}
	public function getST02(){
		return parent::getValueOfIndex(2);
	}
	/**
	*	@param ST 01				:(const)[3/3] Transaction Set Identifier Code
	*	@param ST 02				:(String)[4/9]	Transaction Set Control Number
	*								This field will be a unique control number representing the ST - SE transaction.
	*/
	public static function GenerateNewSTBy($st01,$st02){
		$array = array("ST");
		//ST 01
		$array[] = $st01;
		//ST 02
		$array[] = Milk_EDIfunctionl::NumString4Digit($st02);;
		return new ST($array);
	}
}
?>