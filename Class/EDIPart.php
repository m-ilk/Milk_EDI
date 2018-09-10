<?php 
/**
*	Michael lee production
* 	EDi Part class
*	All return does not contain Segment object
*/
require_once(dirname(__FILE__) . "/Milk_EDIfunctionl.php");
require_once(dirname(__FILE__) . "/Milk_EDIConfig.php");
require_once(Milk_EDIConfig::MilkObject_path);

class EDIPart extends MilkObject
{
	private $data;					//(2d array)	DO NOT PUT key -> value array 
	private $separator;				//(String)		column separator;
	private $line;					//(String)		row separator;
	function __construct($array,$separator,$line)
	{
		$this->data = $array;
		$this->separator = $separator;
		$this->line = $line;
	}
	public function getPart()
	{
		return $this->data;
	}
	public function getSeparator()
	{
		return $this->separator;
	}
	public function getLine()
	{
		return $this->line;
	}
	/**
	*	get first row that has index 0 value equal to given $value
	*	@param name:					(string) segement name
	*	@return							(array)
	*/
	public function getFirstArrayByIndex0($value)
	{
		for ($i=0; $i <sizeof($this->data) ; $i++) { 
			if ($this->data[$i][0]==$value) {
				return $this->data[$i];
			}
		}
	}
	/**
	*	get number of segement with index 0 value equal to $name
	*	@param name 					(string)
	*	@return 						(int)
	*/
	public function getNumOfSegmentsWithName($name)
	{
		$count  =0;
		for ($i=0; $i <sizeof($this->data) ; $i++) { 
			$header = $this->data[$i][0];
			if (strlen($header)>strlen($name)) {
				$header = substr($header, 0,strlen($name));
			}
			if ($this->data[$i][0]==$name) {
				$count++;
			}
		}
		return $count;
	}
	/**
	*	convert data to array
	*	@return 						(string)
	*/
	public function DataToTxt()
	{
		return Milk_EDIfunctionl::Array2EDItext($this->data,$this->separator,$this->line);
	}
}
?>