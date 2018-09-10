<?php
/**
* manage EDI LOG and EDI ERROR LOG
*/
require_once('Milk_EDIConfig.php');
require_once(Milk_EDIConfig::MilkObject_path);
class Milk_EDI846Log extends MilkObject
{

	const TABLE 					= 'amazon_order_edi_846_log';
	CONST CLASS_NAME 				= 'Milk_EDI846Log';
	CONST INIT_DEFAULT_FUNC 		= 'initWithDBData';

	CONST STATUS_CREATE 			= '1';
	CONST STATUS_846_SAVED			= '2';
	CONST STATUS_SEND 				= '3';
	CONST STATUS_997_RECEIVED 		= '4';

 	private $id;
	private $user_account;
	private $path;
	private $status;
	private $create_time;
	private $last_update;
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
	public function getCreate_time()
	{
		return $this->create_time;
	}
	public function getLast_update()
	{
		return $this->last_update;
	}
	public function getStatus()
	{
		return $this->status;
	}
	public function getPath()
	{
		return $this->path;
	}
	public function getStatusString()
	{
		switch ($this->status) {
			case self::STATUS_CREATE:
				return 'Create';
				break;
			case self::STATUS_SEND:
				return 'Send';
				break;
			case self::STATUS_997_RECEIVED:
				return 'received';
				break;
			default:
				break;
		}
	}
	public  function initWithDBData($arr)
	{
		$this->id 							= $arr['id'];
		$this->user_account 				= $arr['user_account'];
		$this->create_time 					= $arr['create_time'];
		$this->status 						= $arr['status'];
		$this->update_time 					= $arr['update_time'];
		$this->path 						= $arr['path'];
	}
	public function initWithID($id)
	{
		$sql 	= "SELECT * FROM ".self::TABLE." WHERE id = '$id'";
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
	*												[create_time]	{opt}
	*												[last_update] 	{opt}
	*												[id] 			{opt}
	* 												[order_by]		{opt} desc/
	*	@return (EDILog obj array) 			if find any
	*			(bool) 						false if does not find any
	*/
	public function get846LogByCondition($condition)
	{
		$sql 			= "SELECT * FROM ".self::TABLE." WHERE 1=1";
		if (array_key_exists('user_account', $condition)) {
			$sql 		.= " AND user_account ='".$condition['user_account']."'";
		}
		if (array_key_exists('create_time', $condition)) {
			$sql 		.= " AND create_time ='".$condition['create_time']."'";
		}
		if (array_key_exists('last_update', $condition)&&array_key_exists('date_end', $condition)) {
			$sql 		.= " AND (last_update BETWEEN '".$condition['last_update']."' AND '".$condition['date_end']."')";
		}
		if (array_key_exists('id', $condition)) {
			$sql 		.= " AND id = '".$condition['id']."'";
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
	*	Insert into 846 log
	*	@param 	Tran									(dbhelper obj)
	*	@param 	user_account							(string)
	*	@param 	status									(const)
	*	@return 										(bool)
	*/
	public static function create846Log(&$Tran,$user_account,$status = self::STATUS_CREATE)
	{
		$time = GeneralDateHelper::GetCurDatetime();
		$query = "INSERT INTO ".self::TABLE."
			 (
			 user_account,
			 create_time,
			 status
			) VALUES (
			'$user_account',
			'$time',
			'$status'
			)";
		$result = self::queryInsertWithTran($query,$Tran);
		return $result;
	}
	/**
	*	Insert into 846 log
	*	@param 	id										(string)
	*	@return 										(bool)
	*/
	public static function CheckIfIDExist($id)
	{
		$sql = "SELECT * FROM ".self::TABLE." WHERE id = '$id'";
		return self::checkExist($sql);
	}
	/*
		update EDI log state insert order state
		State :					1 : successfully transfer to orders and orders detail
								0 : successfully insert into EDI tablesl, however not insert into orders and details yet.
								-1: Invalid item sku
								-2: Fail to insert into orders and orders deatil(CallAPI function error)
	*/
	public static function Update846logStatus(&$Tran,$id,$status,$path = '')
	{
		$query = "UPDATE ".self::TABLE." SET status = '$status' ";
		if (!empty($path)) {
			$query.=", path = '$path' ";
		}
		$query .= "WHERE id = '$id'";
		$result = self::queryInsertWithTran($query,$Tran);
		return $result;
	}
	public static function getDirectoryById($id)
	{
		$sql 		= "SELECT * FROM ".self::TABLE." WHERE id = $id";
		$result 	= self::querySelect($sql);
		if ($result) {
			$path 	= $result[0]['path'];
			$dir 	= substr($path, 0, strlen($path)-3);
			return 	$dir;
		}else{
			return false;
		}
	}
}
/**
*	CREATE TABLE `amazon_order_edi_846_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_account` varchar(10) NOT NULL,
  `path` varchar(20) NOT NULL,
  `create_time` varchar(20) NOT NULL,
  `update_time` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

*/
?>