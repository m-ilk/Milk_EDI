<?php
/**
* 	michael lee production
*	amazon po detail
*/
require_once('Milk_EDIConfig.php');
require_once(Milk_EDIConfig::MilkObject_path);
require_once(dirname(__FILE__).'/EDIsegment/PO1.php');
class AmazonPurchaseOrderDetail extends MilkObject
{
	CONST TABLE 					='amazon_purchase_order_detail';

	CONST WAREHOUSE_LV 				= '12';

	CONST STATUS_START 				= '1';
	CONST STATUS_ACCEPT 			= '2';
	CONST STATUS_CONFIRM 			= '3';
	CONST STATUS_SHIPPED 			= '4';
	CONST STATUS_CANCEL 			= '-1';
	private $erpsku;
	private $asin;
	private $quantity;
	private $cost;
	private $ponum;
	private $create_time;
	private $erp_status;
	private $erp_accept_quantity;
	private $erp_confirm_quantity;
	//EDI PO 
	private $itemID;										//po0107
	private $itemID_type;								//po0106
	function __construct()
	{
		
	}

	public function getErpsku()
	{
		return $this->erpsku;
	}
	public function getAsin()
	{
		return $this->asin;
	}
	public function getQuantity()
	{
		return $this->quantity;
	}
	public function getCost()
	{
		return $this->cost;
	}
	public function getStatus()
	{
		return $this->erp_status;
	}
	public function getErpAcceptQuantity(){
		return $this->erp_accept_quantity;
	}
	public function getErpConfirmQuantity(){
		return $this->erp_confirm_quantity;
	}
	public function getItemID()
	{
		return $this->itemID;
	}
	public function getItemID_type()
	{
		return $this->itemID_type;
	}
	public function getPonum()
	{
		return $this->ponum;
	}
	/**
	*	convert detail status from db status to readable status
	*	@param status 						(const) 
	*	@return  							(string)
	*/
	public function ConvertStatus($status){
		if ($status==AmazonPurchaseOrderDetail::STATUS_START) {
			return 'start';
		}elseif ($status==AmazonPurchaseOrderDetail::STATUS_ACCEPT) {
			return 'accept';
		}elseif ($status==AmazonPurchaseOrderDetail::STATUS_CONFIRM) {
			return 'confirm';
		}elseif ($status==AmazonPurchaseOrderDetail::STATUS_SHIPPED) {
			return 'shipped';
		}
	}
	/**
	*delete
	*/
	public function initWithAcceptExcelData($array,$ponum)
	{
		$this->ponum 						= $ponum;
		$this->erpsku 						= $array[3];
		$this->asin 						= $array[4];
		$this->quantity 					= $array[11];
		$this->cost 						= $array[12];
		$this->itemID 						= '0';
	}
	//delete
	public function initWithExcelData($array,$ponum)
	{
		$this->erpsku 						= $array[3];
		$this->asin 						= $array[4];
		$this->quantity 					= $array[12];
		$this->cost 						= $array[17];
		$this->ponum 						= $ponum;
	}
	/**
	*	init a amazonpurchaseorderdetail object by mysql db return array
	*	@return $array 							(array)
	*	@return void
	*/
	public function initWithDBData($array)
	{
		$this->ponum 						= $array['ponum'];
		$this->erpsku 						= $array['erpsku'];
		$this->asin 						= $array['asin'];
		$this->quantity 					= $array['quantity'];
		$this->cost 						= $array['cost'];
		$this->create_time 					= $array['time'];
		$this->erp_status 					= $array['erp_status'];
		$this->erp_accept_quantity 			= $array['erp_accept_quantity'];
		$this->erp_confirm_quantity 		= $array['erp_confirm_quantity'];
		$this->itemID 						= $array['item_id'];
		$this->itemID_type 					= $array['item_id_type'];
	}
	/**
	*	init a amazon purchase order detail by po1 object
	*	@param po1 							(po1 obj)
	*	@param ponum 						(string)
	*	@return true
	*/
	public function initwithPO1($po1,$ponum)
	{
		$this->ponum 						= $ponum;
		$sku 								= EDIProduct::convertUPC2sku($po1->getPO107());
		if ($sku) {
			$this->erpsku 					= $sku;
		}else{
			$sku 							= EDIProduct::convertASIN2sku($po1->getPO107());
			if (!$sku) {
				throw new Exception("unable to find sku: ".$po1->getPO107());
			}else{
				$this->erpsku 				= $sku;
			}
		}
		$this->itemID 						= $po1->getPO107();
		$this->quantity 					= $po1->getPO102();
		$this->cost 						= $po1->getPO104();
		$this->itemID_type 					= $po1->getPO106();
		return true;
	}
	/**
	*	get all given po number's detail
	*	@param ponum 						(string)
	*	@return amazonpurchaseorderdetail array | false
	*/
	public static function getDetailsByPonum($ponum)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE ponum= '$ponum'";
		$db_result = self::querySelect($sql);
		//print_r($db_result);
		if ($db_result) {
			$result = array();
			for ($i=0; $i < sizeof($db_result) ; $i++) { 
				$one =  new AmazonPurchaseOrderDetail();
				$one->initWithDBData($db_result[$i]);
				$result[] = $one;
			}
			return $result;
		}else{
			return false;
		}
	}
	
	/**
	*	insert this amazon purchase order detail to local
	*	@param tran 						(transaction)
	*	@return (int)1 success
	*				 0 already insert
	*				-1 fail
	*/
	public function insert2Local(&$tran)
	{
		if (!self::checkSkuExistInOrder($this->erpsku,$this->ponum)) {
			$time = GeneralDateHelper::GetCurDatetime();
			$sql = "INSERT INTO ".self::TABLE."(
			ponum,
			erpsku,
			asin,
			quantity,
			cost,
			time,
			erp_status,
			item_id,
			item_id_type
			)VALUES(
			'$this->ponum',
			'$this->erpsku',
			'$this->asin',
			'$this->quantity',
			'$this->cost',
			'$time',
			'".self::STATUS_START."',
			'$this->itemID',
			'$this->itemID_type'
			)";
			$result = self::queryInsertWithTran($sql,$tran);
			//var_dump($result);
			if ($result) {
				return 1;
			}else{
				return -1;
			}
		}else{
			return 0;
		}
	}
	/**
	*	update this detail's confirm quantity
	*	@param quantity 				(string)
	*	@param tran 					(transaction)
	*	@return 0 						0 quantity was accept at the first place it would not be insert in batch
	*	@return true 					success
	*/
	public function setConfirmQuantity($quantity,&$tran)
	{
		$update_flag 			= $this->updateConfirmQuantity($quantity,$tran);
		if ($this->erp_accept_quantity == 0) {
			return 0;
		}
		$result 				= EDIWithERP::Confirm850POdetail($this,$quantity,$tran);
		return $result;
	}
	/**
	*	see setConfirmQuantity()
	*/
	public function updateConfirmQuantity($quantity,&$tran)
	{
		$sql = "UPDATE ".self::TABLE." SET erp_status = '".self::STATUS_CONFIRM."',erp_confirm_quantity ='$quantity' WHERE ponum = '".$this->ponum."' AND erpsku = '".$this->erpsku."'";
		$result = self::queryUpdateWithTran($sql,$tran);
		return $result;
	}
	/**
	*	check if a order has product of given sku
	*	@param sku 							(string)
	*	@param ponum 						(string)
	*	@return boolean
	*/
	public static function checkSkuExistInOrder($sku,$ponum)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE erpsku ='$sku' AND ponum='$ponum'";
		$result = self::checkExist($sql);
		return $result;
	}
	/**
	*	get po's given product's request quantity
	*	@param ponum 						(string)
	*	@param sku 							(string)
	*	@return int|false
	*		false not exist
	*/
	public static function getRequiredQuantityBySkuAndPonum($ponum,$sku){
		$sql = "SELECT * FROM ".self::TABLE." WHERE ponum = '$ponum' AND erpsku = '$sku'";
		$result = self::querySelect($sql);
		if (!$result) {
			return false;
		}else{
			return (int)$result[0]['quantity'];
		}
	}
	/**
	*	get po's given products's accept quantity
	*	@param ponum 						(string)
	*	@param sku 							(string)
	*	@return int|false
	*			flase not exist
	*/
	public static function getAcceptQuantityBySkuAndPonum($ponum,$sku){
		$sql = "SELECT * FROM ".self::TABLE." WHERE ponum = '$ponum' AND erpsku = '$sku'";
		$result = self::querySelect($sql);
		if (!$result) {
			return false;
		}else{
			return (int)$result[0]['erp_accept_quantity'];
		}
	}
	/**
	*	update this detail's confirm quantity
	*	@param quantity 				(string)
	*	@param sku 						(string)
	*	@param ponum 					(string)
	*	@param tran 					(transaction)
	*	@return query result
	*/
	public static function updateERPAcceptQuantityByPonumAndSku($ponum,$sku,$quantity,&$tran){
		$sql = "UPDATE ".self::TABLE." SET 
					erp_status = '".self::STATUS_ACCEPT."',
					erp_accept_quantity ='$quantity' 
				WHERE ponum = '$ponum' AND erpsku = '$sku'";
		$result = self::queryUpdateWithTran($sql,$tran);
		return $result;
	}
}
/*
CREATE TABLE amazon_purchase_order_detail (
  id int(11) NOT NULL AUTO_INCREMENT,
  ponum VARCHAR(20) NOT NULL COMMENT 'purchase order number',
	erpsku VARCHAR(50) NOT NULL COMMENT 'new sku or old sku',
	asin VARCHAR(25) NOT NULL COMMENT '',
	quantity VARCHAR(10) NOT NULL COMMENT '',
	accept_quantity VARCHAR(10) NOT NULL COMMENT '',
	expected_quantity VARCHAR(10) NOT NULL COMMENT '',
	received_quantity VARCHAR(10) NOT NULL COMMENT '',
	outstanding_quantity VARCHAR(10) NOT NULL COMMENT '',
	cost VARCHAR(25) NOT NULL COMMENT '',
	time VARCHAR(25) NOT NULL COMMENT '',
  PRIMARY KEY (id),
	INDEX(ponum)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
*/
?>