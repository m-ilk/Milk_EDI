<?php 
/**

	850 only sgements
*/

require_once(dirname(__FILE__) . "/Segment.php");
class BEG  extends Segment 
{	
	public function __construct($array)
	{
		parent::__construct($array);
	}
	/*
		850 BEG 03
		Purchase Order Number
	*/
	public function getBEG03()
	{
		return parent::getValueOfIndex(3);
	}
}
/**
* 850 CTP 
*/
class CTP extends Segment
{
	
	function __construct($array)
	{
		parent::__construct($array);
	}
	public function getCTP02()
	{
		return parent::getValueOfIndex(2);
	}
	public function getCTP03()
	{
		return parent::getValueOfIndex(3);
	}
}

?>