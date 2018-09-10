<?php 
/**
* EDi object class
	All return does not contain Segment object
*/
require_once(dirname(__FILE__) . "/Milk_EDIConfig.php");
require_once(dirname(__FILE__) . "/Milk_EDIfunctionl.php");
require_once(Milk_EDIConfig::MilkObject_path);
class EDIProduct extends MilkObject
{
	CONST TABLE 						= 'amazon_order_edi_product';
	CONST CLASS_NAME 					= 'EDIProduct';
	CONST DEFAULT_INIT_FUNC 			= 'initWithDBData';
	//db
	private $sku;
	private $UPC;
	private $ASIN;
	private $title;
	private $warehouse;
	private $warehouse_name;
	private $current_quantity;
	private $target_quantity;
	private $last_update;
	private $user_account;
	function __construct()
	{
		
	}
	public function getSku()
	{
		return $this->sku;
	}
	public function getAsin()
	{
		return $this->ASIN;
	}
	public function getUpc()
	{
		return $this->UPC;
	}
	public function getCurrent_quantity()
	{
		return $this->current_quantity;
	}
	public function getTarget_quantity()
	{
		return $this->target_quantity;
	}
	public function getUser_account()
	{
		return $this->user_account;
	}
	/**
	*	init 846 object by upc 
	*	@param upc
	*	@return (boolean)
	*/
	public function initWithUpc($upc)
	{
		$sql 	= "SELECT * FROM ".self::TABLE." WHERE UPC = '$upc'";
		$result = self::querySelect($sql);
		if ($result) {
			$this->initWithDBData($result[0]);
			return true;
		}else{
			return false;
		}
	}
	/**
	*	init by query result array
	*	@param arr 						(array)
	*	@return void
	*/
	public function initWithDBData($arr)
	{
		$this->sku 						= $arr['SKU'];
		$this->UPC 						= $arr['UPC'];
		$this->ASIN 					= $arr['ASIN'];
		$this->title 					= $arr['title'];
		$this->warehouse 				= $arr['warehouse'];
		$this->warehouse_name 			= $arr['warehouse_name'];
		$this->current_quantity 		= $arr['current_quantity'];
		$this->target_quantity 			= $arr['target_quantity'];
		$this->last_update 				= $arr['last_update'];
		$this->user_account 			= $arr['user_account'];
	}
	/**
	*	init by given values
	*	used for adding new product from controller
	*	@param all string
	*	@return void
	*/
	public function initWithInputs($upc,$asin,$sku,$target,$user_account)
	{
		$this->sku 						= $sku;
		$this->UPC 						= $upc;
		$this->ASIN 					= $asin;
		$this->target_quantity 			= $target;
		$this->user_account 			= $user_account;
		//@to-do title;user_account;
	}
	/**
	*	get products by condition array
	*	@param array 					(array)
	*										sku
	*										upc
	*										asin
	*										user_account
	*	@return false|(EID846 array)
	*/
	public static function getProductsByCondition($array)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE 1=1 ";
		if (array_key_exists('user_account', $array)) {
			$sql.=" AND user_account = '".$array['user_account']."'";
		}
		if (array_key_exists('upc', $array)) {
			$sql.=" AND upc = '".$array['upc']."'";
		}
		if (array_key_exists('asin', $array)) {
			$sql.=" AND asin = '".$array['asin']."'";
		}
		if (array_key_exists('sku', $array)) {
			$sql.=" AND sku = '".$array['sku']."'";
		}
		$result = self::querySelect($sql);
		if ($result) {
			return self::initWithObjectsArray(self::CLASS_NAME,self::DEFAULT_INIT_FUNC,$result);
		}else{
			return false;
		}
	}
	/**
	*	check if upc exist
	*	@param upc 							(string)
	*/
	public static function checkIfUpcExist($upc)
	{
		$sql 		= "SELECT * FROM ".self::TABLE." WHERE UPC = '$upc'";
		return self::checkExist($sql);

	}
	/**
	*	update product based on product upc
	*	@param upc 							(string)
	*	@param updates						(array)
	*											[sku]
	*											[asin]
	*											[target]
	*											[last_update]
	*											[current]
	*	@param Tran 						(transaction object)
	*/
	public static function updateProductByUPC($upc,$updates,&$Tran)
	{

		$sql 		= "UPDATE ".self::TABLE." SET title = title";
		if (array_key_exists('sku', $updates)) {
			$sql	.= " , SKU = '".$updates['sku']."'";
		}
		if (array_key_exists('asin', $updates)) {
			$sql	.= " , ASIN = '".$updates['asin']."'";
		}
		if (array_key_exists('target', $updates)) {
			$sql	.= " , target_quantity = '".$updates['target']."'";
		}
		if (array_key_exists('last_update', $updates)) {
			$sql	.= " , last_update = '".$updates['last_update']."'";
		}
		if (array_key_exists('current', $updates)) {
			$sql	.= " , current_quantity = '".$updates['current']."'";
		}
		$sql 		.= " WHERE UPC = '$upc'";
		$result 	= self::queryInsertWithTran($sql,$Tran);
		return $result;
	}
	/**
	*	create a new product
	*
	*/
	public function createProduct()
	{
		$sql = "INSERT INTO ".self::TABLE."(
			SKU,
			UPC,
			ASIN,
			target_quantity,
			user_account
		)VALUES(
			'$this->sku',
			'$this->UPC',
			'$this->ASIN',
			'$this->target_quantity',
			'$this->user_account'
		)";
		$result = self::queryInsert($sql);
		return $result;
	}
	public static function convertUPC2sku($upc)
	{
		$sql 		= "SELECT * FROM ".self::TABLE." WHERE UPC = '$upc'";
		$result 	= self::querySelect($sql);
		if ($result) {
			$mapping = new EDIProduct;
			$mapping ->initWithDBData($result[0]);
			return $mapping->getSku();
		}else{
			return false;
		}
	}
	public static function convertASIN2sku($asin)
	{
		$sql 		= "SELECT * FROM ".self::TABLE." WHERE ASIN = '$asin'";
		$result 	= self::querySelect($sql);
		if ($result) {
			$mapping = new EDIProduct;
			$mapping ->initWithDBData($result[0]);
			return $mapping->getSku();
		}else{
			return false;
		}
	}
	public static function convertSku2Upc($sku)
	{
		$sql 		= "SELECT * FROM ".self::TABLE." WHERE sku = '$sku'";
		$result 	= self::querySelect($sql);
		if ($result) {
			$mapping = new EDIProduct;
			$mapping ->initWithDBData($result[0]);
			return $mapping->getUpc();
		}else{
			return false;
		}
	}
	/**
	*	get need to send 846 info products
	*/
	public static function getNeed2UpdateProducts()
	{
		$sql 		= "SELECT * FROM ".self::TABLE." WHERE is_used = 1 AND current_quantity != target_quantity";
		$result 	= self::querySelect($sql);
		return self::initWithObjectsArray(self::CLASS_NAME,self::DEFAULT_INIT_FUNC,$result);
	}
	public function updateLastID($lastid, &$Tran)
	{
		$sql 		= "UPDATE ".self::TABLE." SET last_846_id = '$lastid' WHERE sku = '$this->sku' AND  user_account = '$this->user_account'";
		$result 	= self::queryInsertWithTran($sql,$Tran);
		return $result;
	}
	public static function updateProductQuantityBylast846Id($_846_id,&$Tran)
	{
		$sql 		= "UPDATE ".self::TABLE." SET current_quantity = target_quantity WHERE last_846_id = '$_846_id'";
		$result 	= self::queryInsertWithTran($sql,$Tran);
		return $result;
	}
}
/*
CREATE TABLE `amazon_order_edi_product` (
  `SKU` varchar(20) NOT NULL,
  `UPC` varchar(20) NOT NULL,
  `ASIN` varchar(20) NOT NULL,
  `title` varchar(20) NOT NULL,
  `warehouse` varchar(20) NOT NULL,
  `warehouse_name` varchar(20) NOT NULL,
  `current_quantity` varchar(20) NOT NULL,
  `target_quantity` varchar(20) NOT NULL,
  `last_update` varchar(20) NOT NULL,
  `user_account` varchar(10) NOT NULL,
  `last_846_id` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

*/
?>