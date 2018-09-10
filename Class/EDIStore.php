<?php
/**
* michael lee production
*/
require_once(Milk_EDIConfig::MilkObject_path);
class EDIStore extends MilkObject
{
	CONST TABLE = 'amazon_order_edi_store';
	
	private $store;								//amazon store code
	private $user_account; 						//erp system store code
	private $path;								//edi file received path
	private $send_to;							//used for as2 trasaction
	private $send_from;							//used for as2 transaction
	private $vendor_code; 						//amazon vendor code
	function __construct()
	{

	}
	public function getStore()
	{
		return $this->store;
	}
	public function getUser_account()
	{
		return $this->user_account;
	}

	public function getPath()
	{
		return $this->path;
	}
	public function getSend_from(){
		return $this->send_from;
	}
	public function getSend_to()
	{
		return $this->send_to;
	}
	public function getVendor_code()
	{
		return $this->vendor_code;
	}
	public function initWithDB($array)
	{
		$this->store 			= $array['store'];
		$this->user_account 	= $array['user_account'];
		$this->path 			= $array['path'];
		$this->send_to 			= $array['send_to'];
		$this->send_from 		= $array['send_from'];
		$this->vendor_code 		= $array['vendor_code'];
	}
	/**
	*	init with erp store code
	*	@param user_account 			(string)
	*	@return 						(boolean)
	*/
	public function initWIthUserAccount($user_account)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE user_account = '$user_account'";
		$result = self::querySelect($sql);
		if ($result) {
			$this->initWithDB($result[0]);
			return true; 
		}else{
			return false;
		}
	}
	/**
	*	init with amazon store code
	*	@param store_code 				(string)
	*	@return 						(boolean)
	*/
	public function initWithStoreName($store){
		$sql = "SELECT * FROM ".self::TABLE." WHERE store = '$store'";
		$result = self::querySelect($sql);
		$one= $result[0];
		if ($one) {
			$this->store = $one['store'];
			$this->user_account = $one['user_account'];
			$this->path = $one['path'];
			return true;
		}else{
			return false;
		}
	}
	/**
	*	search edi store by erp store code
	*	@param code 					(string)
	*	@return 						(edistore array)
	*/
	public static function getEDIStoreByCode($code='')
	{
		$sql = "SELECT * FROM ".self::TABLE;
		if (!empty($code)) {
			$sql.= " WHERE user_account = '$code'";
		}
		$result = self::querySelect($sql);
		$array = array();
		for ($i=0; $i < sizeof($result) ; $i++) { 
			$row = $result[$i];
			$store = new EDIStore();
			$store->initWithDB($row);
			$array[]=$store;
		}
		return $array;
	}
	/**
	*	check if a stroe exist by amazon store code
	*	@param store_code 					(string)
	*	@return 							(boolean)
	*/
	public static function checkStoreExistByStoreCode($store_code)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE store = '$store_code'";
		return self::checkExist($sql);
	}
	/**
	*	check if a stroe exist by erp store code
	*	@param store_code 					(string)
	*	@return 							(boolean)
	*/
	public static function checkStoreExistByUser_account($user_account)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE user_account = '$user_account'";
		return self::checkExist($sql);
	}
}
?>