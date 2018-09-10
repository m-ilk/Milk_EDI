<?php 
/**
* micahel lee production
*/

class Milk_EDIAddress extends MilkObject
{
	CONST TABLE 							='amazon_order_edi_address_mapping';

	private $address_code;
	private $address_code_quantity;
	private $amazon_identifier;
	private $name;
	private $address1;
	private $address2;
	private $city;
	private $state;
	private $zip;
	private $country;
	private $time;

	function __construct()
	{
		
	}
	public function getAddress_code()
	{
		return $this->address_code;
	}
	public function getAddress_code_quantity()
	{
		return $this->address_code_quantity;
	}
	public function getAmazon_identifier()
	{
		return $this->amazon_identifier;
	}
	public function getName()
	{
		return $this->name;
	}
	public function getAddress1()
	{

		return $this->address1;
	}
	public function getAddress2()
	{
		return $this->address2;
	}
	public function getCity()
	{
		return $this->city;
	}
	public function getState()
	{
		return $this->state;
	}
	public function getZip()
	{
		return $this->zip;
	}
	public function getCountry()
	{
		return $this->country;
	}

	public function initWithDBData($arr)
	{
		$this->address_code 				= $arr['address_code'];
		$this->address_code_quantity 		= $arr['address_code_quantity']; 
		$this->amazon_identifier 			= $arr['amazon_identifier']; 
		$this->name 						= $arr['name']; 
		$this->address1 					= $arr['address1']; 
		$this->address2 					= $arr['address2']; 
		$this->city 						= $arr['city']; 
		$this->state 						= $arr['state']; 
		$this->zip 							= $arr['zip']; 
		$this->country 						= $arr['country']; 
		$this->time 						= $arr['time'];
	}
	public function initWithAddress_code($address_code)
	{
		$sql 	= "SELECT * FROM ".self::TABLE." WHERE address_code = '$address_code'";
		$result = self::querySelect($sql);
		if ($result) {
			$this->initWithDBData($result[0]);
			return true;
		}else{
			return false;
		}
	}
	public static function checkAddress_codeExist($address_code)
	{
		$sql 	= "SELECT * FROM ".self::TABLE." WHERE address_code = '$address_code'";
		$result = self::querySelect($sql);
		return $result;
	}
}
?>