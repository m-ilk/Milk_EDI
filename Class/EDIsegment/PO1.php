<?php 
require_once(dirname(__FILE__) . "/Segment.php");
/**
* 	850,855 PO1 segment
*/
class PO1 extends Segment
{
	const PO106_SK 				= "SK";					//Stock Keeping Unit (SKU)
	CONST PO106_UP 				= 'UP'; 				//upc 12
	/*PO1 03*/
	const PO103_EA 				= "EA";					//Each 
											//Always 'EA' This field is required.
	CONST PO105_NT 				= 'NT'; 				// Indicates a net unit price
	//private $PoArray;

	static $PO101_count 	  	= 0;
	function __construct($array){
		parent::__construct($array);
		// $this->PoArray = $array;
	}
	public static function IsPO1array($array)
	{
		if (!empty($array)&&$array[0]=='PO1') {
			return true;
		}else{
			return false;
		}
	}
	/*
		Assigned identification(from 850)
	*/
	public function getPO101(){
		return $this->getPO1IndexOf(1);
		//print_r($this->PoArray);
	}
	/*
		quantity Ordered 
	*/
	public function getPO102(){
		return $this->getPO1IndexOf(2);
	}
	/*
		Unit or Basis for Measurement Code
			CA 		:Case
			EA 		:Each
	*/
	public function getPO103(){
		return $this->getPO1IndexOf(3);
	}
	/*
		Unit Price;
			amazon 855 notice:
				This field will contain the cost price to Amazon and should match the cost price on the invoice. Please note this is a required field,
	*/
	public function getPO104(){
		return $this->getPO1IndexOf(4);
	}
	/*
		(2)Unit price code
			NT 			:Net
	*/
	public function getPO105(){
		return $this->getPO1IndexOf(5);
	}
	/*
		(2/2)Procuti/Service ID Qualifier
			BP 			:Buyer's Part Number
			EN 			:European Article Number (EAN) (2-5-5-1)
			IB 			:International Standard Book Number (ISBN)
			UK 			:U.P.C./EAN Shipping Container Code (1-2-5-5-1)
			UP 			:U.P.C. Consumer Package Code (1-5-5-1)
			VN 			:Vendor's (Seller's) Item Number
	*/
	public function getPO106(){
		return $this->getPO1IndexOf(6);
	}
	/*
		(1/48)Product/Service ID
			provide by 850 PO
	*/
	public function getPO107(){
		return $this->getPO1IndexOf(7);
	}
	/*
		
	*/
	public function getPO114()
	{
		return $this->getPO1IndexOf(14);
	}
	/*

	*/
	public function getPO115()
	{
		return $this->getPO1IndexOf(15);
	}
	/*
		index 		:(int) Maximum 15
	*/
	public function getPO1IndexOf($index){

		if (gettype ($index)!="integer"||$index>15) {
			throw new Exception("PO1IndexOf wrong index; index: ".$index);
		}
		return $this->getValueOfIndex($index);
		
	}
	/*
		Generate 855 PO1 by 850 PO1
	*/
	public function GenerateNewPO1ByPO1()
	{
		$array = array("PO1");
		//PO1 01
		$array[] = $this->getPO101();
		//PO1 02
		$array[] = $this->getPO102();
		//PO1 03
		$array[] = self::PO103_EA;
		//PO1 04
		$array[] = "";
		//PO1 05
		$array[] = "";
		//PO1 06
		$array[] = self::PO106_SK;
		//PO1 07
		$array[] = $this->getPO107();
		return new PO1($array);
	}
	/**
	*	genereate purchase order po1 for edi 855
	*	@param po1 02 								(string) quantity
	*	@param po1 04 								(string) price
	*	@param po1 06 								(string) item id type default upc
	*	@param po1 07 								(string) item id upc or asin
	*/
	public static function GeneratePoPO1($po102,$po104,$po107,$po106 = PO1::PO106_UP)
	{
		$po1_array 			= array(
			'PO1',
			PO1::$PO101_count,
			$po102,
			PO1::PO103_EA,
			$po104,
			PO1::PO105_NT,
			$po106,
			$po107
		);
		PO1::$PO101_count++;
		$PO1 				= new PO1($po1_array);
		return $PO1;
	}
}
/*
for ($i=0; $i <$this->PoArray ; $i++) { 
			$current = $this->PoArray[$i];
			if ($current[0]=="PO1") {
				return $current[$index];
			}
		}
*/
?>