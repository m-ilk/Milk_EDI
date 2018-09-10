<?php
/**
* michael lee production
* amazon PO
*/
require_once('Milk_EDI850BODY.php');
require_once('AmazonPurchaseOrderDetail.php');
require_once('Milk_EDIConfig.php');
require_once(Milk_EDIConfig::MilkObject_path);
class AmazonPurchaseOrder extends MilkObject
{
	CONST TABLE 						= 'amazon_purchase_order';

	CONST STATUS_CREATE 				='1';
	CONST STATUS_ACCEPT 				= '2';
	CONST STATUS_CONFIRMED 				= '3';
	CONST STATUS_CANCELLED 				= '-1';
	CONST STATUS_ERROR 					= '0';

	CONST RECEIVER_NAME 				= 'Amazon Vendor Central';
	CONST COUNTRY_CODE 					= 'US';

	private $id;
	private $ponum;
	private $shipwindow_start;
	private $shipwindow_end;
	private $path;								//edi file path
	private $order_code;						//erp order code;
	private $create_time;
	private $erp_status;
	private $shipping_method;
	private $user_account;
	private $details;							//amazon purchase order detial
	//EDI PO code
	private $address_code;
	private $saved_path;
	private $tracking;
	private $packages;
	function __construct()
	{
		
	}
	public function getPonum()
	{
		return $this->ponum;
	}
	public function getStatus()
	{
		return $this->status;
	}
	public function getCreattime()
	{
		return $this->create_time;
	}
	public function getShipwindow_start()
	{
		return $this->shipwindow_start;
	}
	public function getShipwindow_end()
	{
		return $this->shipwindow_end;
	}
	public function getDetails()
	{
		return $this->details;
	}
	public function getErpStatus()
	{
		return $this->erp_status;
	}
	public function getShipping_method()
	{
		return $this->shipping_method;
	}
	public function getErpStatusString()
	{
		switch ($this->erp_status) {
			case self::STATUS_CREATE:
				return 'Create';
				break;
			case self::STATUS_ACCEPT:
				return 'Accept';
				break;
			case self::STATUS_CONFIRMED:
				return 'Confirm';
				break;
			case self::STATUS_ERROR:
				return 'Error';
				break;
			default:
				return 'Cancel';
				break;
		}
	}
	public function getUser_account()
	{
		return $this->user_account;
	}
	public function getSaved_path()
	{
		return $this->saved_path;
	}
	public function getAddress_code()
	{
		return $this->address_code;
	}
	public function getTracking()
	{
		return $this->tracking;
	}
	public function getPackage()
	{
		return $this->packages;
	}
	/**
	*	init a amazonpurchase order object by po number
	*	@param ponum 							(string)
	*	@return boolean
	*/
	public function initWithPOnum($ponum)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE ponum='$ponum'";
		$result= self::querySelect($sql);
		if ($result) {
			$this->initWithDBData($result[0]);
			$details = AmazonPurchaseOrderDetail::getDetailsByPonum($ponum);
			$this->details = $details;
			return true;
		}else{
			return false;
		}
	}
	/**
	*	init a amazonpurchaseorder object by mysql db return array
	*	@return $array 							(array)
	*	@return void
	*/
	public function initWithDBData($array)
	{
		$this->ponum 				= $array['ponum'];
		$this->shipwindow_start 	= $array['shipwindow_start'];
		$this->shipwindow_end 		= $array['shipwindow_end'];
		$this->create_time 			= $array['create_time'];
		$this->erp_status 			= $array['erp_status'];
		$this->shipping_method 		= $array['shipping_method'];
		$this->address_code 		= $array['address_code'];
		$this->user_account 		= $array['user_account'];
		$this->saved_path  			= $array['saved_path'];
		$this->tracking 			= $array['tracking_number'];
		$this->packages 			= $array['packages'];
	}
	/**
	*	init a amazon purchase order file based on edi 850 body object
	*	@param body 							(BODY) edi 850 body object
	*	@param user_account 					(string)
	*	@return void
	*/
	public function initWithEDI850Bodyfile($body,$user_account)
	{
		if (is_a($body, 'Milk_BODY')) {
			$this->ponum 						= $body->getBEG()->getBEG03();
			$this->status 						= '';
			$dtms 								= $body->getDtms();
			for ($i=0; $i <sizeof($dtms) ; $i++) { 
				$one 							= $dtms[$i];
				switch ($one->getDTM01()) {
					case DTM::DTM01_63:
						$this->shipwindow_end 	= $one->getDTM02();
						break;
					case DTM::DTM01_64:
						$this->shipwindow_start = $one->getDTM02();
						break;
					default:
						break;
				}
			}
			$this->backorder 					= '';
			$this->expectedShip 				= '';
			$this->user_account 				= $user_account;
			$po1s 								= $body->getPO1s();
			$this->details 						= $this->generateDetailsByPO1Segments($po1s);
			$this->address_code 				= $body->getN1()->getN104();
			//var_dump(sizeof($po1s));
		}else{
			throw new Exception("Invalid input parameter");
		}
	}
	/**
	*	generate amazonpurchaseorderdetail objects based on edi 850 po1 segments
	*	@param po1 								(array po1)
	*	@return  output 						(amazonpurchasedetail array)
	*/
	public function generateDetailsByPO1Segments($po1s)
	{
		$output									= array();
		$ponum 									= $this->ponum;
		for ($i=0; $i < sizeof($po1s) ; $i++) {
			$po1 								= $po1s[$i];
			$detail 							= new AmazonPurchaseOrderDetail;
			$result 							= $detail->initwithPO1($po1,$ponum);
			$output[] 							= $detail;
		}
		return $output;
	}
	/**
	*	delete
	*/
	public function validAcceptExcelInputFormat($filearr)
	{
		$firstrow = $filearr[0];
		if (sizeof($firstrow)!=13) {
			return false;
		}
		return true;
	}
	/**
	*	confirm a po (dose not sent 856)
	*	@param $arr 								(array)
	*														[quantity]
	*														[sku]
	*	@param shipping_method 						(const)
	*	@param package 								(string)
	*	@param package_ch 							(string)
	*	@param Tran 								(transaction)
	* 	@return	return 
			1 :						success;
			-1:						;
			-2:						fail to update confirm quantity;
			-4:						fail to update batch status or package num;						
	*/
	public function Confirm($arr,$shipping_method,$package,$tracking,&$Tran){
		/*check if details size mnatch with arr size*/
		if (!self::detailArraySizeCheck($arr)) {
			throw new Exception("confirm details size does not match with original order");
		}
		//update purchase order detail
		for ($i=0; $i <sizeof($arr) ; $i++) { 
			$one = $arr[$i];
			$quantity =$one['quantity'];
			$sku =$one['sku'];
			$thisdetail = $this->getDetailBySku($sku);			
			$update_order_detail = $thisdetail->setConfirmQuantity($quantity,$Tran);
			if ($update_order_detail==0) {
				//out of stock 
			}elseif (!$update_order_detail) {
				throw new Exception("confirm array size does not match with array size");
			}
		}
		//update order
		$order_status_update = 	$this->ConfirmUpdate($shipping_method,$Tran,$package,$tracking);
		if (!$order_status_update) {
			throw new Exception("fail to update order status");
		}
		$result = EDIWithERP::Confirm850PO($this->ponum,$arr,$package,$tracking,$Tran);
		if (!$result) {
			throw new Exception("Fail to update ERP");
		}
		return true;
	}
	/**
	*	accept a po (does not sent 855)
	*	@param 	$arr 				(array)
	*									[sku]
	*									[quantity]
	*	@param 	$Tran; 				(transaciton)
	*	@return
	*		1 :						success
	*		-1:						accept arry does not match with detail array size
	*		-2:						inventory not enough;
	*		-5:						fail to update erp state
	*		-6:						product inventory quantity does not match with inventory batch location quantity
	*		-7:						
	*/
	public function accept($arr,&$Tran)
	{
		//check if details size match with accept array size
		if (!self::detailArraySizeCheck($arr)) {
			return -1;
		}
		$result 					= EDIWithERP::Accept850PO($this->ponum,$arr,$Tran);
		
		if ($result<1) {
			return $result;
		}

		//update erp_state and erp_accept_quantity
		for ($i=0; $i < sizeof($arr) ; $i++) {
			$item 					= $arr[$i];
			$update_erp = AmazonPurchaseOrderDetail::updateERPAcceptQuantityByPonumAndSku($this->ponum,$item['sku'],$item['quantity'],$Tran);
			if (!$update_erp) {
				throw new Exception("Fail to update accept quantity: po: ".$this->ponum,' sku: '.$arr['sku']. ' quantity: '.$arr['quantity']);
			}
		}
		$update_order 				= self::updateErpStateByPonum($this->ponum,self::STATUS_ACCEPT,$Tran);
		if ($update_order) {
			return 1;
		}else{
			throw new Exception("Fail to Update po status: ".$this->ponum);
		}
	}
	/**
	*	check if confirm or accept products count match with original po products count
	*	@param $arr 							(array)
	*	@return boolean
	*/
	public function detailArraySizeCheck($arr)
	{
		if (!is_array($arr)) {
			return false;
		}
		if (sizeof($arr)!=sizeof($this->details)) {
			return false;
		}
		return true;
	}
	/**
	*	check if request product quantity has been changed from the original order
	* 	used for generate 855 bak 02
	*	@return Boolean
	*/
	public function checkAnyProductQuantityHasChanged()
	{
		if (!isset($this->details)) {
			return false;
		}
		$flag  									= true;
		for ($i=0; $i < sizeof($this->details) ; $i++) { 
			$one 								= $this->details[$i];
			if ($one->getQuantity()!=$one->getErpAcceptQuantity()) {
				$flag 							= false;
				break;
			}
		}
		return $flag;
	}
	/**
	*	check if accept or confirm quantity is more than original request quantity 
	*	@param $arr 							(array)
	*												[sku]
	*												[quantity]
	*	@return boolean
	*/
	public static function checkNotOverRequiredQuantity($arr,$ponum){
		$result = true;
		for ($i=0; $i < sizeof($arr) ; $i++) { 
			$quantity = $arr[$i]['quantity'];
			$sku = $arr[$i]['sku'];
			$required_quantity = AmazonPurchaseOrderDetail::getRequiredQuantityBySkuAndPonum($ponum,$sku);
			if (!$required_quantity) {
				$result= false;
			}elseif ($required_quantity<$quantity) {
				$result = false;
				break;
			}
		}
		return $result;
	}
	/**
	*	check if confirm quantity is less than or equal to accept quantity
	*	@param $arr 								(array)
	*													[sku]
	*			 										[quantity]
	*	@return boolean
	*/
	public static function  checkNotOverAcceptQuantity($arr,$ponum){
		$result = true;
		for ($i=0; $i < sizeof($arr) ; $i++) { 
			$quantity = $arr[$i]['quantity'];
			$sku = $arr[$i]['sku'];
			$accept_quantity = AmazonPurchaseOrderDetail::getAcceptQuantityBySkuAndPonum($ponum,$sku);
			if ($accept_quantity<$quantity) {
				$result = false;
				break;
			}
		}
		return $result;
	}
	/**
	*	get po amaazonpurchaseorder object by ponumber
	*	@param $ponum 								(array)
	*													if ponum is empty, this function will return all po
	*	@return $array 								(amazonpurchaseorder array)
	*/
	public static function searchByPonum($ponum='')
	{
		$query = "SELECT * FROM ".self::TABLE;
		if (!empty($ponum)) {
			$query.= " WHERE ponum = '$ponum'";
		}
		$query .= " ORDER BY create_time DESC";
		$result = self::querySelect($query);
		$array = array();
		for ($i=0; $i < sizeof($result) ; $i++) { 
			$one = new AmazonPurchaseOrder();
			$one->initWithDBData($result[$i]);
			$array[] = $one;
		}
		return $array;
	}
	/**
	*	check if given po number is already exist in the system
	*	@param ponum 								(string)
	*	@return boolean
	*/
	public static function checkPonumAlreadyExist($ponum)
	{
		$sql= 'SELECT * FROM '.self::TABLE." WHERE ponum = '$ponum'";
		return self::checkExist($sql);
	}
	/**
	*	insert this amazonpurchaseorder object to database
	*	@param $rrab 						(transaction)
	*	@return array 						(array)
	*										[ack]  	1 success
	*												0 exist
	*												-1 fail
	*										[detail] {opt} only apply if order has correctly insert to db
	*													result of amazonpurchaserorderdetail insert
	*/
	public function insert2Local(&$tran)
	{
		$time 					= GeneralDateHelper::GetCurDatetime();
		$output 				= array();
		$output['ack'] 			= '';
		if (!self::checkPonumAlreadyExist($this->ponum)) {
			$sql="INSERT INTO ".self::TABLE." (
			ponum,
			shipwindow_start,
			shipwindow_end,
			create_time,
			erp_status,
			user_account,
			address_code
			) VALUES (
			'$this->ponum',
			'$this->shipwindow_start',
			'$this->shipwindow_end',
			'$time',
			".self::STATUS_CREATE.",
			'$this->user_account',
			'$this->address_code'
			)";
			$result 			= self::queryInsertWithTran($sql,$tran);
			if ($result) {
				$this->id 		= $tran ->insert_id;
				$output['ack'] 	= '1';
			}else{
				$output['ack'] 	= '-1';
			}
		}else{
			$output['ack'] 		= '0';
		}
		if ($result) {
			$result = array();
			for ($i=0; $i < sizeof($this->details) ; $i++) { 
				$one = $this->details[$i];
				$temp = $one->insert2Local($tran);
				$result[] = $temp;
			}
			$output['details'] 	= $result;
		}
		return $output;
	}
	/**
	*	update db erp_status based on given po number
	*	@param ponum 							(string)
	*	@param erp_status 						(const) status
	*	@param tran 							(transaction)
	*	@return query result 
	*/
	public static function updateErpStateByPonum($ponum,$erp_status,&$tran)
	{
		$sql 	= "UPDATE ".self::TABLE." SET erp_status = '$erp_status' WHERE ponum = '$ponum'";
		$result = self::queryUpdateWithTran($sql,$tran);
		return $result;
	}
	/**
	*	update this po original 850 file saved path
	*	@param tran								(transaction)
	*	@param saved_path 						(string)
	* 	@return query result
	**/
	public function update850SavedPath(&$Tran,$saved_path)
	{
		$sql 	= "UPDATE ".self::TABLE." SET saved_path = '$saved_path' WHERE id = '".$this->id."'";
		$result = self::queryInsertWithTran($sql,$Tran);
		return $result;
	}
	/**
	*	update this po to confirm status and update shipping method
	* 	@param shipping_method 					(const)
	*	@param tran 							(transaction)
	*	@return query result
	*/
	public function ConfirmUpdate($shipping_method,&$tran,$package,$tracking)
	{	
		$sql = "UPDATE ".self::TABLE." SET ".
				"erp_status = '".self::STATUS_CONFIRMED."',".
				"shipping_method ='$shipping_method',tracking_number = '$tracking',packages = '$package' 
				WHERE ponum = '$this->ponum'
				";
		$result = self::queryUpdateWithTran($sql,$tran);
		return $result;
	}
	/**
	*	get a given po Erp status based on po number 
	*	@param ponum 							(string)
	*	@return const erp_status|false
	*/
	public static function getErpStateByPonum($ponum)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE ponum = '$ponum'";
		$result = self::querySelect($sql);
		if ($result&&sizeof($result)>0) {
			return $result[0]['erp_status'];
		}else{
			return false;
		}
	}
	/**
	* get this po's products by given erp sku
	*	@param sku 							(string)
	*/
	public function getDetailBySku($sku){
		if (!isset($this->details)) {
			return false;
		}
		for ($i=0; $i <sizeof($this->details) ; $i++) { 
			$one = $this->details[$i];
			if ($one->getErpsku()==$sku) {
				return $one;
			}
		}
		return NULL;
	}
	/**
	*	get edi save file path by order's po number
	*	@param ponum 						(string)
	*	@return (false|string)
	*/
	public static function getSaved_pathByPonum($ponum)
	{
		$sql		= "SELECT * FROM ".self::TABLE." WHERE ponum = '$ponum'";
		$result 	= self::querySelect($sql);
		if ($result) {
			return 	$result[0]['saved_path'];
		}else{
			return false;
		}
	}
}
/*
CREATE TABLE amazon_purchase_order (
  id int(11) NOT NULL AUTO_INCREMENT,
  ponum VARCHAR(20) NOT NULL COMMENT 'purchase order number',
	address VARCHAR(100) NOT NULL COMMENT 'address',
	order_status VARCHAR(10) NOT NULL COMMENT 'status',
	shipwindow_start VARCHAR(20) NOT NULL COMMENT '',
	shipwindow_end VARCHAR(20) NOT NULL COMMENT '',
	backorder VARCHAR(20) NOT NULL COMMENT '',
	expectedShip VARCHAR(20) NOT NULL COMMENT '',
	create_time VARCHAR(30) NOT NULL COMMENT '',
  PRIMARY KEY (id),
	INDEX(ponum)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
*/
?>