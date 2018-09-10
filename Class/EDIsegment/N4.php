<?php 
/**
	850,855 N4 sgement
*/
require_once(dirname(__FILE__) . "/Segment.php");
class N4 extends Segment
{	
	
	function __construct($array)
	{	
		if (sizeof($array)<3) {
			throw new Exception("invalid PRF init array size");
		}
		parent::__construct($array);
	}
	/*
		City name
	*/
	public function getN401()
	{
		return $this->getValueOfIndex(1);
	}
	/*
		state or Province Code
	*/
	public function getN402(){
		return $this->getValueOfIndex(2);
	}
	/*
		Postal Code
	*/
	public function getN403(){
		return $this->getValueOfIndex(3);
	}
	/*
		Country COde
	*/
	public function getN404()
	{
		return $this->getValueOfIndex(4);
	}
	/*
		n401: 				(string)City Name

		N402:				(string)[2/2] 	State or Province Code
		N403:				(string)[3/15] 	Postal Code						
		N404:				(string)[2/3] 	Country Code
								
	*/
	public static function GenerateN4($n101,$n102,$n103,$n104){
		$array = array("N4");
		//n1 01
		$array[] = $n101;
		//prf 02
		$array[] = $n102;
		//prf 03
		$array[] = $n103;
		//prf 04
		$array[] = $n104;
		return new N4($array);
	}
}	
?>