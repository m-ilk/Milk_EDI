<?php
/**
* 	Current only allow aceept all or reject all
*/
class Item855 extends EDIPart
{	
	//dr 
	private $PO1850;
	private $PO1855;
	private $ACK;

	//ds
	private $po1; 							// [m]
	private $ctp;							// [c]
	private $ack;							// [m]
	private $dtm; 							// [c]
	function __construct()
	{

	}
	public function initWithDRdata($PO1,$ACK = null)
	{
		$this->PO1850 		= $PO1;
		$this->ACK 			= $ACK;
	}
	public function initWithPOdata($PO1,$ACK)
	{
		$this->po1 			= $PO1;
		$this->ack  		= $ACK;
	}
	public function getPO1()
	{
		if (isset($this->po1)) {
			return $this->po1;
		}else{
			throw new Exception("fail to get 855 po1");
		}
	}
	public function POItemToArray()
	{
		$array = array();
		$array[]= $this->po1;
		if (isset($this->ctp)) {
			$array[] = $this->ctp;
		}
		$array[]= $this->ack;
		if (isset($this->dtm)) {
			$array[] = $this->dtm;
		}
		return $array;
	}
	/*
		GET the po1 from 850
	*/
	public function get850PO1()
	{
		return $this->PO1850;
	}	
	public function getQuantityInPO1()
	{
		return $this->PO1850->getPO102();
	}
	/*
		Ackonwledge each po1. either accept, partically accept or cancel
	*/
	public function setAck($ack)
	{
		$this->ACK = $ack;	
	}
	public function GeneratePO1()
	{
		$this->PO1855 = $this->PO1850->GenerateNewPO1ByPO1();
	}
	/*
	
	*/
	public function GenerateAck($ack01,$quantity)
	{
		$ack29 = "";
		if ($ack01==ACK::ACK01_IA) {
			$ack29=ACK::ACK29_00;
		}else{
			$ack29=ACK::ACK29_03;
		}
		$this->ACK = ACK::GenerateACK($ack01,$quantity,$this->PO1850->getPO103(),$ack29,ACK::ACK04_068,Milk_EDIfunctionl::ccyymmdd());
	}
	public static function GenerateItem855($PO1,$ack01,$quantity)
	{
		$item = new Item855();
		$item ->initWithDRdata($PO1);
		$item->GenerateAck($ack01,$quantity);
		$item->GeneratePO1();
		return $item;
	}
	/*
		return an array of Segment Objects
	*/
	public function ObjToArray()
	{
		$array 				= array();
		$array[] 			= $this->PO1855;
		if (is_array($this->ACK)) {
			for ($i=0; $i < sizeof($this->ACK) ; $i++) { 
				$one 		= $this->PO1855[$i];
				$array[] 	= $one;
			}
		}else{
			$array[]= $this->ACK;
		}
		return $array;
	}

}



?>

