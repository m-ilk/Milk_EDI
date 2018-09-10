<?php
/**
* 	currently only allow quantity changed
*/
class Item846 extends EDIPart
{	
	private $lin;
	private $sdq;
	private $ctp; 					//@to-do
	private $cur; 	 				//@to-do
	private $dtm; 
	private $quantity;
	function __construct()
	{

	}
	public function getQTY()
	{
		return $this->qty;
	}
	public function getLIN()
	{
		return $this->lin;
	}
	public function getQuantity()
	{
		return $this->quantity;
	}
	/**
	*	init function for only quantity update
	*	@param sku 						(string)
	*	@param quantity 				(string)
	*/
	public function initWithInput($sku,$quantity,$warehouse)
	{
		$this->lin 				= LIN::Generate846LIN($sku);
		$this->sdq 				= SDQ::GenerateSDQ($warehouse,$quantity);
		$this->quantity 		= $quantity;
	}
	public function Convert846Item2Array()
	{
		$array 				= array();
		$array[] 			= $this->lin;
		if (isset($this->ctp)) {
			$array[] 		= $this->ctp;
		}
		if (isset($this->cur)) {
			$array[] 		= $this->cur;
		}
		$array[] 			= $this->sdq;
		if (isset($this->dtm)) {
			$array[] 		= $this->dtm;
		}
		return $array;
	}
}



?>

