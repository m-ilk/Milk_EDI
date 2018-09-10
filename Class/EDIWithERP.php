<?php 
/**
* 	michael lee production
*	Import edi into erp system
*/
require_once('Milk_EDIConfig.php');
//require_once(dirname(__FILE__) .  Milk_EDIConfig::ERP_ORDER_CLASS);

class EDIWithERP 
{	
	function __construct()
	{

	}
	/**
	* 	@param BODY 						(BODY)
	*	@return array 						(array)
	*										[ack]	1 for success
	*										[msg]
	*/
	public function InsertPO2Orders($Body)
	{
		$array 							= array();
		if (is_a($Body,'Milk_BODY')) {
			$ponum 						= $Body->getPONum();
			/**
			*
			*/
			//update edilog erp order id and state
			$update 					= EDILog::UpdateEDIlogERPState($Tran,$ponum,$state,$order_code);
			if ($update) {
				$array['ack'] 			= '1';
				$array['msg'] 			= $ponum.' has successfully insert';
				return $array;
			}else{
				$array['ack'] 			= '0';
				$array['msg'] 			= $ponum.' has fail to insert';
				return $array;
			}
		}else{
			throw new Exception("Parameter is not a BODY type");
		}
	}

	/**
	*	if given sku is amazon asin and return mapping sku
	*	if given sku is erp sku
	*	@param sku 								(string)
	*	@param store_code 						(string)
	*	@return 								(string)
	*/
	public static function CheckProductSku($sku,$store_code)
	{
		return '';
	}
	/**
	* 	@param 									(Shipment)
	*	@return 								(string)
	*/
	public static function getPrf06ByShipment($shipment)
	{
		if (is_a($shipment, 'Shipment')) {
			$refer_array 				= explode('-', $shipment->orderCode);
			if (sizeof($refer_array)<2) {
				throw new Exception("invalid order code");
			}else{
				$prf06 					= $refer_array[1];
				return $prf06;
			}
		}else{
			throw new Exception("Input of getPrf06ByShipment is not a shipment obj");
		}
	}
	/**
	*	@param 									(string) order id
	*	@return 								(string) user_account
	*/
	public static function getStoreCodeByOrderID($orderID){
		$user_account = '';
		return $user_account;
	}
	/**
	*	@param 									(string)amazon_method
	*/
	public static function ConvertAMAZON2ERPShippmentMethod($Amazon_method)
	{
		return '';
	}
	/**
	*	Accept 850 PO
	*	@param $ponum 							(string)
	*	@param $arr 							(array) items
	*	@param $tran 							(DBobject)
	*/
	public function Accept850PO($ponum,array $arr,&$Tran)
	{
		return true;
	}
	/**
	*	@param ponum 							(string)
	*	@param arr 								(array)
	*	@param package 							(string)
	*	@return 								(boolean)
	*/
	public function Confirm850PO($ponum,array $arr,$package,&$Tran)
	{
		return true;
	}
	/**
	*	confirm 850 PO detail
	*	@param detail 							(AmazonPurchaseOrderDetail)
	*	@param quantity 						(string) confirm quantity
	*	@param tran 							(transaction)
	*/
	public static function Confirm850POdetail(AmazonPurchaseOrderDetail $detail,$quantity,&$tran)
	{
		return true;
	}
	/**
	*	@return
	*		1				only need to create batch of lv
	*		2 				need to create batch for both lv and ch
	*		0				not enough inventory
	*/
	public function checkQuantity($arr)
	{
		$result = 1;
		return $result;
	}
	/**
	*	used for po order
	*	@param 									(string) ponum
	*	@return 								(int)
	*/
	public static function calculatePOorderTotalWeight($ponum)
	{
		return 10.0;
	}
	/**
	*	@param po 								(AmazonPurchaseOrder) 
	*	@return 								(string)
	*/
	public static function getTrackingNumberByAmazonPO(AmazonPurchaseOrder $po)
	{
		return $po->getTracking();
	}
}

?>