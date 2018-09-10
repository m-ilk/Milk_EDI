<?php
/**
* manage EDI LOG and EDI ERROR LOG
*/
require_once('Milk_EDIConfig.php');
require_once(Milk_EDIConfig::MilkObject_path);
class Milk_EDIErrorLog extends MilkObject
{
	const TABLE 						= 'amazon_order_edi_error_log';
	/*ERROR LOG TYPE*/
	const ERRORLOG_FAIL2MOVE 			= '1';
	const ERRORLOG_FAIL2CREATE 			= '2';
	const ERRORLOG_FAIL2FINDSKU 		= '3';
	const ERRORLOG_FAIL2FINDACCOUNT 	= '4';
	const ERRORLOG_855FAIL 				= '5';
	const ERRORLOG_856FAIL 				= '6';
	const ERRORLOG_850FAIL 				= '7';
	const ERRORLOG_997FAIL 				= '8';
	const ERRORLOG_INSERTORDER_FAIL 	= '9';
	CONST ERRORLOG_846FAIL 				= '10';
	CONST ERRORLOG_RECEIVER_FAIL 		= '-1';
	function __construct()
	{
		
	}
	/**
	*	@param type 						(const)
	*										1. fail to move from incoming folder to Files folder
	*										2. fail to create
	*										3. fail to find ERPSKU: msg is AMAZON SKU
	*									                        path is po number
	*										4. fail to find user_account
	*									  						msg is amazon user
	*										5. fail to generate 855 or send 855
	*									   						path order id
	*									   						msg : error
	*										6. fail to generate 856 or send 856
	*										7. 850 failure
	*										8. 997 failure
	*	@param path 						(string)original path
	*	@param msg 							(string)optional
	*	@return true|false
	*/
	public static function ErrorLog($type,$path,$msg='')
	{
		$time 			= GeneralDateHelper::GetCurDatetime();
		$msg 			= Milk_EDI::sql_string($msg);
		$query 			= "INSERT INTO ".self::TABLE."(type,file_path,msg,create_time) VALUES ('$type','$path','$msg','$time')";
		self::initDb_helper();
		$Tran 			= self::getTransaction();
		self::beginTransaction($Tran);
		$result 		= self::queryInsertWithTran($query,$Tran);
		if ($result) {
			self::commitTransaciton($Tran);
			return true;
		}else{
			self::rollbackTransaction($Tran);
			throw new Exception("Warning: EDI Fail to add Log, filename: ".$path);
		}
	}
}
?>