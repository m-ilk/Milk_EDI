<?php
/**
* manage EDI LOG and EDI ERROR LOG
*/
require_once('Milk_EDIConfig.php');
require_once(Milk_EDIConfig::MilkObject_path);
class EDILog extends MilkObject
{

	const AMAZON_LOG_TABLE 			= 'amazon_order_edi_log';
	CONST CLASS_NAME 				= 'EDILog';
	CONST INIT_DEFAULT_FUNC 		= 'initWithDBData';
	/*LOG STATE TYPE*/
	const ISCREATE 					= '1';
	const ISSEND 					= '2';
	const ISRECEIVED997 			= '3';
	const FAIL2CREATE 				= '-1';
	const FAIL2SEND 				= '-2';
	const FAIL2ACK 					= '-3';			//997 ak1 is either A or E 
	/*END*/
	/*Orders STATE TYPE*/
	const ISINSERT 					= '1';
	const TEST_CASE 				= '-9';
	const PROCESS_ON_VENDOR 		= '-8';
	CONST STATE_NOTINSERT 			= '0';
	/*LOG COLUMN*/
	const LOG_COLUMN_855 			= 'state_855';
	const LOG_COLUMN_855_ID 		= '855_GS06';
	const LOG_COLUMN_856 			= 'state_856';
	const LOG_COLUMN_856_ID 		= '856_GS06';
	/*END*/
	CONST ORDER_TYPE_PO 			= 'PO';
	CONST ORDER_TYPE_DS 			= 'DS';

 	private $id;
	private $user_account;
	private $po_number;
	private $create_time;
	private $state;
	private $update_time;
	private $note;
	private $state_855;
	private $_855_GS06;
	private $state_856;
	private $_856_GS06;
	private $order_code; 			//erp order id
	private $order_type; 			//DS or PO
	function __construct()
	{
		
	}
	public function getID()
	{
		return $this->id;
	}
	public function getUser_account()
	{
		return $this->user_account;
	}
	public function getPo_number()
	{
		return $this->po_number;
	}
	public function getCreate_time()
	{
		return $this->create_time;
	}
	public function getState()
	{
		return $this->state;
	}
	public function getStateString()
	{
		switch ($this->state) {
			case self::STATE_NOTINSERT:
				return 'Not Insert';
				break;
			case self::ISINSERT:
				return 'Already Insert';
				break;
			case self::TEST_CASE:
				return 'Testing';
				break;
			case self::PROCESS_ON_VENDOR:
				return '';
				break;
			default:
				break;
		}
	}
	public function getNote()
	{
		return $this->note;
	}
	public function getState_855()
	{
		return $this->state_855;
	}
	public function getState_855_string()
	{
		switch ($this->state_855) {
			case self::ISCREATE:
				return 'Is Create';
				break;
			case self::ISSEND:
				return 'Is Send';
				break;
			case self::ISRECEIVED997:
				return '997 Received';
				break;
			case self::FAIL2CREATE:
				return 'Fail to Create';
				break;
			case self::FAIL2SEND:
				return 'Fail to Send';
				break;
			case self::FAIL2ACK:
				return 'Fail to acknowledge';
				break;
			case '0':
				return 'Not send yet';
				break;
			default:
				break;
		}
	}
	public function get855_GS06()
	{
		return $this->_855_GS06;
	}
	public function getState_856()
	{
		return $this->state_856;
	}
	public function getState_856_string()
	{
		switch ($this->state_856) {
			case self::ISCREATE:
				return 'Is Create';
				break;
			case self::ISSEND:
				return 'Is Send';
				break;
			case self::ISRECEIVED997:
				return '997 Received';
				break;
			case self::FAIL2CREATE:
				return 'Fail to Create';
				break;
			case self::FAIL2SEND:
				return 'Fail to Send';
				break;
			case self::FAIL2ACK:
				return 'Fail to acknowledge';
				break;
			case '0':
				return 'Not send yet';
				break;
			default:
				break;
		}
	}
	public function get856_GS06()
	{
		return $this->_856_GS06;
	}
	public function getUpdate_time_Date()
	{
		$date = date_create_from_format('Y-m-j H:i:s',$this->update_time);
		return date_format($date,'Ymd');
	}
	public function getUpdate_time_Time()
	{
		$date = date_create_from_format('Y-m-j H:i:s',$this->update_time);
		return date_format($date,'Hi');
	}
	public function getOrder_code()
	{
		return $this->order_code;
	}
	public function getOrder_type()
	{
		return $this->order_type;
	}
	/**
	*	init a edi log object by mysql db return array
	*	@return array 						(array)
	*	@return void
	*/
	public  function initWithDBData($arr)
	{
		$this->id 							= $arr['id'];
		$this->user_account 				= $arr['user_account'];
		$this->po_number 					= $arr['po_number'];
		$this->create_time 					= $arr['create_time'];
		$this->state 						= $arr['state'];
		$this->update_time 					= $arr['update_time'];
		$this->note 						= $arr['note'];
		$this->state_855 					= $arr['state_855'];
		$this->_855_GS06 					= $arr['855_GS06'];
		$this->state_856 					= $arr['state_856'];
		$this->_856_GS06 					= $arr['856_GS06'];
		$this->order_code 					= $arr['order_code'];
		$this->order_type 					= $arr['order_type'];
	}
	/**
	* 	init edi log by erp order id
	*	@param order_code 					(string)erp order id
	*	@return 							(boolean) 
	*/
	public function initWithOrderCode($orderCode)
	{
		$sql 	= "SELECT * FROM ".self::AMAZON_LOG_TABLE." WHERE order_code = '$orderCode'";
		$result = self::querySelect($sql);
		if ($result) {
			$this->initWithDBData($result[0]);
			return true;
		}else{
			return false;
		}
	}
	/**
	*	init edi log by po number and user account 
	*	@param ponum 						(string) 
	*	@param user_acoount 				(string)
	*	@return 							(boolean)
	*/
	public function initWithPOnumAndUseraccount($ponum,$user_account)
	{
		$sql 	= "SELECT * FROM ".self::AMAZON_LOG_TABLE." WHERE po_number = '$ponum' AND user_account = '$user_account'";
		$result = self::querySelect($sql);
		if ($result) {
			$this->initWithDBData($result[0]);
			return true;
		}else{
			return false;
		}
	}
	/**
	*	@deprecated different user_account may have identical po number
	*	init edi log by po number and user account 
	*	@param ponum 						(string) 
	*	@param user_acoount 				(string)
	*	@return 							(boolean)
	*/
	public function initWithPOnum($ponum)
	{
		$sql 	= "SELECT * FROM ".self::AMAZON_LOG_TABLE." WHERE po_number = '$ponum'";
		$result = self::querySelect($sql);
		if ($result) {
			$this->initWithDBData($result[0]);
			return true;
		}else{
			return false;
		}
	}
	/**
	*	@param $condition 					(array)	[user_account] 	{opt}
	*												[date_start]	{opt}
	*												[date_end] 		{opt}
	*												[ponum] 		{opt}
	* 												[order_by]		{opt} desc/
	*	@return (EDILog obj array) 			if find any
	*			(bool) 						false if does not find any
	*/
	public function getEDILogByCondition($condition)
	{
		$sql 			= "SELECT * FROM ".self::AMAZON_LOG_TABLE." WHERE 1=1";
		if (array_key_exists('user_account', $condition)) {
			$sql 		.= " AND user_account ='".$condition['user_account']."'";
		}
		if (array_key_exists('ponum', $condition)) {
			$sql 		.= " AND po_number ='".$condition['ponum']."'";
		}
		if (array_key_exists('date_start', $condition)&&array_key_exists('date_end', $condition)) {
			$sql 		.= " AND (create_time BETWEEN '".$condition['date_start']."' AND '".$condition['date_end']."')";
		}
		if (array_key_exists('order_type', $condition)) {
			$sql 		.= " AND order_type = '".$condition['order_type']."'";
		}
		if (array_key_exists('order_by', $condition)) {
			$sql 		.= "  Order By ".$condition['order_by']." desc LIMIT 100";
		}
		$result 		= self::querySelect($sql);
		if ($result) {
			return self::initWithObjectsArray(self::CLASS_NAME,self::INIT_DEFAULT_FUNC,$result);
		}else{
			return false;
		}
	}
	/**
	*	get not insert to erp system's po number
	*	get state = 0 order logs
	*	@param $order_type 					(const) DS or PO
	*	@return array 						(array)
	*											[] (string) po number 
	*/
	public function getNotInsertPOnumberArray($order_type = self::ORDER_TYPE_DS)
	{
		$query 				= "SELECT * , ".EDILog::AMAZON_LOG_TABLE.".po_number as POnum FROM ".Milk_BODY::AMAZON_BODY_TABLE." LEFT JOIN ".EDILog::AMAZON_LOG_TABLE." ON ".Milk_BODY::AMAZON_BODY_TABLE.".po_number =".EDILog::AMAZON_LOG_TABLE.".po_number WHERE ".EDILog::AMAZON_LOG_TABLE.".state != ".EDILog::ISINSERT." AND ".EDILog::AMAZON_LOG_TABLE.".state != ".EDILog::TEST_CASE." AND order_type =  '$order_type'";
		$result 			= self::querySelect($query);
		$array 				= array();
		if (isset($result)&&sizeof($result)>0) {
			for ($i=0; $i < sizeof($result) ; $i++) { 
				$array[] 	= $result[$i]['POnum'];
			}
		}
		return $array;
	}
	/**
	*	Insert into EDI log
	*	@param 	Tran									(transaction)
	*	@param 	user_account							(string)
	*	@param 	po_number								(po_number)
	*	@param 	order_type								(const)
	*	@return 										(bool)
	*/
	public static function InsertEDILog(&$Tran,$user_account,$po_number,$order_type,$note="")
	{
		$time = GeneralDateHelper::GetCurDatetime();
		$query = "INSERT INTO ".self::AMAZON_LOG_TABLE."
			 (
			 user_account,
			 po_number,
			 create_time,
			 state,
			 update_time,
			 note,
			 order_type
			) VALUES (
			'$user_account',
			'$po_number',
			'$time',
			'0',
			'$time',
			'$note',
			'$order_type'
			)";
		$result = self::queryInsertWithTran($query,$Tran);
		if ($result) {
			return true;
		}else{
			return false;
		}
	}
	/**
	*	check if ponumber is already exist in log table
	*	@param ponum 								(string)
	*	@return 									(boolean)
	*/
	public static function CheckIfPonumExist($ponum)
	{
		$sql = "SELECT * FROM ".self::AMAZON_LOG_TABLE." WHERE po_number = '$ponum'";
		return self::checkExist($sql);
	}
	/**
	*	check if given order has been already inserted to erp by po number
	*	@param 										(string)
	*	@return 									(boolean)
	*/
	public static function CheckIfPonumInsertAlready($ponum)
	{
		$sql = "SELECT * FROM ".self::AMAZON_LOG_TABLE." WHERE po_number = '$ponum' AND state = '1'";
		return self::checkExist($sql);
	}
	/**
	*	update EDI log state insert order state
	*	@param tran 					(transaction)
	*	@param ponum 					(string)
	*	@param order_code 				(string) erp order id
	*	@param state :					1 : successfully transfer to orders and orders detail
	*									0 : successfully insert into EDI table, however not insert into orders and details yet.
	*									-1: Invalid item sku
	*									-2: Fail to insert into orders and orders deatil(CallAPI function error)
	* 	@return 						(boolean)
	*/
	public static function UpdateEDIlogERPState(&$Tran,$ponum,$state,$order_code = '')
	{
		$query 		= "UPDATE ".self::AMAZON_LOG_TABLE." SET state = '$state'";
		if (!empty($order_code)) {
			$query.=", order_code = '$order_code' ";
		}
		$query 		.= "WHERE po_number = '$ponum'";
		$result 	= self::queryInsertWithTran($query,$Tran);
		return $result;
	}
	/**
	*	update EDI log 855 and 856 state
	*	@param column 						(const) the column that will be updated
	* 	@param state 						(const) the value of that column
	*	@param po_number 					(string)
	*	@param gsid 						(gsid) used for 855's or 856's 997
	*	@return (boolean)
	*/
	public static function UpdateEDILog($column,$state,$po_number,$gsid= '0')
	{
		$query 			= "UPDATE ".self::AMAZON_LOG_TABLE." SET $column = '$state'";
		if ($state==self::ISCREATE) {
			if ($column==self::LOG_COLUMN_856) {
				$query 	.=" , 856_GS06 = '$gsid'";
			}elseif ($column==self::LOG_COLUMN_855) {
				$query 	.=" , 855_GS06 = '$gsid'";
			}
		}
		$query 			.=" WHERE po_number = '$po_number'";
		$result 		= self::queryInsert($query);
		return $result;
	}
	/**
	*	check if 997 is already insert in log;
	*	@param type 						(const)AK1 01
	*	@param id 							(string) id
	*	@param order_type 					(const) DS|PO
	*	@return 
	*	 	(po_number) if success 
	*	 	(false) if not find
	*/
	public static function check997ID($type,$id,$order_type='DS')
	{
		$column 		= '';
		if ($type==AK1::AK101_855) {
			$column 	= self::LOG_COLUMN_855_ID;
		}elseif ($type==AK1::AK101_856) {
			$column 	= self::LOG_COLUMN_856_ID;
		}
		$query 			= "SELECT * FROM ".self::AMAZON_LOG_TABLE." WHERE $column = '$id' AND order_type = '$order_type'";
		$result 		= self::querySelect($query);
		if ($result) {
			return $result[0]['po_number'];
		}else{
			return false;
		}
	}
	/**
	*	@todo
	*	get purchase order number by EDI 997 acknowledge type and gs id
	*	@param type 							(cost)AK1 01;
	*	@param id
	*	@return  							
	*/
	public static function getPOnumBy997Info($type,$gsid)
	{
		
	}
	/**
	*	get given erp order's po number by given erp order id
	*	state -9 is testing order
	*	@param order_code 						(string) orders table refrence_no_platform
	*	@return po_number 						(string) 
	*			false 							(boolean) can not be found
	*/
	public static function getPonumByOrderCode($order_code)
	{
		$query 			= "SELECT * FROM ".self::AMAZON_LOG_TABLE." WHERE order_code = '$order_code' and state !=-9";
		$result 		= self::querySelect($query);
		if ($result&&!empty($result)) {
			return $result[0]['po_number'];
		}else{
			return false;
		}
	}
}
?>