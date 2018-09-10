<?php 
/**
* 
*/
class Segment 
{
	private $data;
	function __construct($data)
	{
		$this->data = $data;
	}
	public function getData()
	{
		return $this->data;
	}
	public function ToString($separator,$line){
		return Milk_EDIfunctionl::Array2EDILine($this->data,$separator,$line);
	}
	/*
		index 				:(int)
	*/
	public function getValueOfIndex($index){
		if (sizeof($this->data)>$index) {
			return $this->data[$index];
		}else{
			//throw new Exception("can not get index ".$index." from array: ".$this->data);
			return "";
		}
	}
	public function QueryValuesString()
	{
		$query = '';
		for ($i=1; $i <sizeof($this->data) ; $i++) { 
			$query .="'".$this->data[$i]."'";
			if ($i!=sizeof($this->data)-1) {
				$query.=",";
			}
		}
		return $query;
	}
	public function QueryValueStringWithOutIndex($index)
	{
		$query = '';
		for ($i=1; $i <sizeof($this->data) ; $i++) { 
			if ($i != $index) {
				$query .="'".$this->data[$i]."'";
				if ($i!=sizeof($this->data)-1) {
					$query.=",";
				}
			}
		}
		return $query;
	}
}
?>