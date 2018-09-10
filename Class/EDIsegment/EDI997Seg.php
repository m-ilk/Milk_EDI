<?php 
/**

	855 only sgements
*/
require_once(dirname(__FILE__) . "/Segment.php");
class AK1 extends Segment 
{	
	const AK101_855 = 'PR';
	const AK101_856 = 'SH';
	CONST AK101_846 = 'IB';
	public function __construct($array)
	{
		parent::__construct($array);
	}
	/*
		997 GS 01
		Functional Identifier Code
			AK101 is the functional ID found in the GS segment (GS01) in the functional group being acknowledged.
	*/
	public function getAK101(){
		return parent::getValueOfIndex(1);
	}
	/*
		997 GS 02
		Group Control Number
			AK102 is the functional group control number found in the GS segment in the functional group being acknowledged.
	*/
	public function getAK102(){
		return parent::getValueOfIndex(2);
	}
}

class AK9 extends Segment 
{	
	const AK901_A = 'A';			//accept
	const AK902_E = 'E';			//accept
	public function __construct($array)
	{
		parent::__construct($array);
	}
	/*
		Functional Group Acknowledge Code
			Code indicating accept or reject condition based on the syntax editing of the functional group
			If AK901 contains the value "A" or "E", then the transmitted functional group is accepted
	*/
	public function getAK901()
	{
		return parent::getValueOfIndex(1);
	}
}
?>