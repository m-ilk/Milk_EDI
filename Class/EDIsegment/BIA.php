<?php 
/**
* 	used for edi 846
*/
require_once(dirname(__FILE__) . "/Segment.php");
class BIA extends Segment
{	
	CONST BIA01_00 						= '00'; //Original This will fully erase and update data for your inventory information
	CONST BIA01_25 						= '25'; //Incremental will only update the inventory information for the items in the feed.
	CONST BIA02_DD 						= 'DD'; //DS :Distributor Inventory Report
	CONST BIA02_DEFAULT 				= 'IP';	//po :

	public function __construct($array)
	{	
		if (sizeof($array)!=6) {
			throw new Exception("invalid BIA init array size");
		}
		parent::__construct($array);

	}
	public function getBIAarray(){
		return parent::getData();
	}
	public function getBIA02(){
		return parent::getValueOfIndex(2);
	}
	/**
	*	@param bia 01				:(const)[/2] 		Transaction Set Purpose Code
	*	@param bia 03				:(String)[1/30]		Reference Identification
	*								If the inventory feed is an "original" then this field is a unique ID number which relates to this particular inventory feed.
	* 								If the inventory feed is an "incremental" then this field is the unique ID for the "original" inventory feed, which this incremental feed relates to.
	* 	@param 
	*
	*
	*/
	public static function GenerateBIA($bia01,$bia03){
		$array = array("BIA");
		//bia 01
		$array[] = $bia01;
		//bia 02
		$array[] = self::BIA02_DD;
		//bia03
		$array[] = $bia03;
		//bia04
		$array[] = Milk_EDIfunctionl::ccyymmdd();
		//bia05
		$array[] = Milk_EDIfunctionl::hhmm();
		return new BIA($array);
	}
}
?>