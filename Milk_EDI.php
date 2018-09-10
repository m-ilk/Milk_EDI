<?php
/**
*	michael lee production
* 	1.anaylzie Amazon EDI files and transfer it to other format
	2.Manange amazon_order_edi_useraccount && amazon_edi_log table
	3.Transfer purchase order to Orders table
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
foreach (scandir(dirname(__FILE__).'/Class/') as $filename) {
	$file_parts 								= pathinfo($filename);
	if (isset($file_parts['extension'])) {
		$ext 										= $file_parts['extension'];
		if ($ext == 'php') {
			$path = dirname(__FILE__) . '/Class/' . $filename;
		    if (is_file($path)) {
		        require_once($path);
		    }
		}
	}
}
require_once(Milk_EDIConfig::SEND_FELE_CLASS);
require_once(Milk_EDIConfig::MilkObject_path);
class Milk_EDI extends MilkObject
{
	/*AMAZON */
	const AMAZON_DS_COLUMN 						= '^';  	//amazon ds column separator 
	const AMAZON_DS_LINE 						= '~';		//amazon ds line separator
	CONST AMAZON_PO_COLUMN 						= '*';
	CONST AMAZON_PO_LINE 						= '~';
	/*AMAZON END*/
	const Enviroment 							= ISA::TEST15;//test or production

	/**
	*	scan all edi files in target folder and create edi log and edi po,if possible
	*	@param path 							(string) scan $path and insert all edi file in $path
	* 	@return array 							(array) ['ack'] 1 success
	*															0 no edi to insert
	* 															-1 error
	*													['msg']	
	*													['details'] array of insert result
	*													['ds_850']	dropship 850 count
	*													['ds_997'] 	dropship 997 count
	*													['ds_fail']	dropship insert fail count
	*	 												['po_850'] 	purchase order 850 count
	*/
	public static function Receiver($path)
	{
		$full_path 									= Milk_EDIConfig::EDI_PATH.'/'.$path;
		$array 										= array();
		if (!file_exists($full_path)) {
			$array['ack'] 							= '-1';
			$array['msg'] 							= 'file path not exist '.$full_path;
			return $array;
		}
		$files 										= scandir($full_path);
		if (!isset($files)||empty($files)) {
			$array['ack'] 							= '0';
			$array['msg'] 							= 'empty message';
			return $array;
		}
		$ds_850 									= 0;
		$ds_997 									= 0;
		$ds_fail 									= 0;

		$po_850 									= 0;
		$po_997 									= 0;
		$po_fail 									= 0;
		$details 									= array();
		for ($i=0; $i < sizeof($files) ; $i++) {
			$once 									= $files[$i];
			$ext 									= pathinfo($once, PATHINFO_EXTENSION);
			try {
				if ($ext == Milk_EDIConfig::PAYLOAD_EXT) {
					$temp 							= array();
					$temp['file_name'] 				= $once;
					$type = EDIObj::CheckEDIFileType($full_path.'/'.$once,self::AMAZON_DS_COLUMN,self::AMAZON_DS_LINE);
					if ($type) {
						//DS edi
						switch ($type) {
							case '850':
								$temp['EDI_type'] 	= '850';
								$temp['type'] 		= 'dropship';
								$result 			= self::InsertDs850($full_path.'/'.$once);
								$temp['msg'] 		= $result['msg'];
								if ($result['ack']=='1') {
									$ds_850++;
								}else{
									$ds_fail++;
								}
								break;
							case '997':
								$temp['EDI_type'] 	= '997';
								$temp['type'] 		= 'dropship';
								$result 			= self::InsertDS997($full_path.'/'.$once);
								$temp['error'] 		= $result['error'];
								$temp['msg'] 		= $result['msg'];
								if ($result['ack']=='1') {
									$ds_997++;
								}else{
									$ds_fail++;
								}
								break;
							default:
								$ds_fail++;
								$temp['error'] 		= 'Not implement edi type: '.$type;
								break;
						}
					}else{
						//PO edi
						$type = EDIObj::CheckEDIFileType($full_path.'/'.$once,self::AMAZON_PO_COLUMN,self::AMAZON_PO_LINE);
						switch ($type) {
							case '850':
								$temp['msg'] 		= 'AMAZON Purchase Order';
								$result 			= self::InsertPo850($full_path.'/'.$once);
								$po_850++;
								break;
							case '997':
								$temp['EDI_type'] 	= '997';
								$temp['type'] 		= 'purchase order';
								$result 			= self::InsertPO997($full_path.'/'.$once);
								if ($result['ack']=='1') {
									$po_997++;
								}else{
									$po_fail++;
								}
								$temp['error'] 		= $result['error'];
								$temp['msg'] 		= $result['msg'];
								break;
							default:
								$temp['error'] 		= 'Not implement EDI file type:'.$type;
								$ds_fail++;
								break;
						}
					}
					$details[] 						= $temp;
				}else{
					//not edi palyload file
				}
			} catch (Exception $e) {
				Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_RECEIVER_FAIL,$path,$e->getMessage());
			}
		}
		$array['ack'] 								= '1';
		$array['msg'] 								= 'success';
		$array['ds_850'] 							= $ds_850;
		$array['ds_997'] 							= $ds_997;
		$array['ds_fail'] 							= $ds_fail;
		$array['po_850'] 							= $po_850;
		$array['po_997'] 							= $po_997;
		$array['po_fail'] 							= $po_fail;
		$array['details'] 							= $details;
		return $array;
	}
	/**
	*	insert purchase order from file to db
	*	@param 	filename 						(string) 	file name 
	*	@return $output 						(array) 	[ack]
	*														[message]
	*														[result] insertintoDB result 
	*/
	public static function InsertPo850($filename)
	{
		self::initDb_helper();
	    $Tran       							= self::getTransaction();
	    self::beginTransaction($Tran);
	    $output 								= array();
	    $output['ack'] 							= '';
	    $output['msg'] 							= '';
	    try {
	    	if (!file_exists($filename)) {
			    throw new Exception("$filename file not exist ");
			}		
			$Obj850         					= new Milk_EDI850();
			$flag_850 							= $Obj850->initWithPOInput($filename,self::AMAZON_PO_COLUMN,self::AMAZON_PO_LINE);
			if (!$flag_850) {
				throw new Exception("fail to init 850 with path: ".$filename);
			}
			$Obj850         					->setOriginalPath($filename);
			$store 								=  new EDIStore();
			$result 							= $store->initWithStoreName(trim($Obj850->getISA()->getISA08()));
			if ($result) {
				$useraccount 					= $store->getUser_account();
				$Obj850  						->setUser_account($useraccount);
			}else{
				throw new Exception("Fail to find userstore By ISA08 : ".$Obj850->getISA()->getISA08());
			}
			$result 							= $Obj850 ->InsertIntoDB(Milk_EDI850::TYPE_PO,$Tran,$filename);
			if ($result&&isset($result['fail'])&&$result['fail']=='0') {
				$path_array 					= explode('/', $filename);
				$name 							= $path_array[sizeof($path_array)-1];
				$prefix 						= substr($name, 0,strlen($name)-strlen(Milk_EDIConfig::PAYLOAD_EXT)-1);
				if (EDIObj::MoveOriginalFiles(Milk_EDIConfig::EDI_RECEIVE_PATH,$prefix)) {
					$output['ack'] 				= '1';
					$output['msg'] 				= 'success';
					$output['result'] 			= $result;
				}else{
					$output['ack'] 				= '1';
					$output['msg'] 				= 'successfully insert to DB, however fail to move origianl files. Check error log';
					$output['result'] 			= $result;
				}
				self::commitTransaciton($Tran);
			}else{
				$output['ack'] 					= '-1';
				$output['msg'] 					= 'Fail to insert PO';
				self::rollbackTransaction($Tran);
			}
	    } catch (Exception $e) {
	    	$output['msg'] 						= $e->getMessage();
			$output['ack'] 						= '-1';
			self::rollbackTransaction($Tran);
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_850FAIL,$orderID,$e->getMessage());
	    }
		return $output;
	}
	/**
	*	insert Drop ship from file to db
	*	@param 	filename 						(string) 	file name {full path}
	*	@return $output 						(array) 	[ack]
	*														[message]
	*														[result] insertintoDB result 
	*/
	public static function InsertDs850($filename)
	{
		self::initDb_helper();
	    $Tran       							= self::getTransaction();
	    self::beginTransaction($Tran);
	    $output 								= array();
	    $output['ack'] 							= '';
	    $output['msg'] 							= '';
		try {
			$Obj850 							= new Milk_EDI850();
			$flag_850 							= $Obj850 ->initWithDSInput($filename,self::AMAZON_DS_COLUMN,self::AMAZON_DS_LINE);
			if (!$flag_850) {
				throw new Exception("fail to init 850 with path: ".$filename);
			}
			$Obj850 							->setOriginalPath(Milk_EDIConfig::EDI_RECEIVE_PATH.'/'.$filename);
			$store 								=  new EDIStore();
			$result 							= $store->initWithStoreName(trim($Obj850->getISA()->getISA08()));
			if ($result) {
				$useraccount 					= $store->getUser_account();
				$Obj850  						->setUser_account($useraccount);
			}else{
				throw new Exception("Fail to find userstore By ISA08 : ".$Obj850->getISA()->getISA08());
			}
			if (!$Obj850->HeaderCheck()) {
				$insert_id 						= $Obj850->InsertIntoDB(Milk_EDI850::TYPE_DS,$Tran,$filename);
				if ($insert_id){
					$path_array 				= explode('/', $filename);
					$name 						= $path_array[sizeof($path_array)-1];
					$prefix 					= substr($name, 0,strlen($name)-strlen(Milk_EDIConfig::PAYLOAD_EXT)-1);	
					//TO-DO check if all table is inserst correctly
					if (EDIObj::MoveOriginalFiles(Milk_EDIConfig::EDI_RECEIVE_PATH,$prefix)) {
						$output['ack'] 			= '1';
						$output['msg'] 			= 'success';
					 }else{
					 	$output['ack'] 			= '1';
					 	$output['msg'] 			= 'successfully insert to DB, however fail to move origianl files. Check error log';
					 }
					 self::commitTransaciton($Tran);
				}else{
					$output['ack'] 				= '-1';
					$output['msg'] 				= 'Fail to insert To DB';
					self::rollbackTransaction($Tran);
				}
			}else{
				$path_array 					= explode('/', $filename);
				$name 							= $path_array[sizeof($path_array)-1];
				$prefix 						= substr($name, 0,strlen($name)-strlen(Milk_EDIConfig::PAYLOAD_EXT)-1);
				if (EDIObj::MoveOriginalFiles(Milk_EDIConfig::EDI_RECEIVE_PATH,$prefix)) {
					$output['ack'] 				= '1';
					$output['msg'] 				= 'this Dr 850 has already exist, successfully move to original file folder';
				}else{
					$output['ack'] 				= '-1';
					$output['msg'] 				= 'this Dr 850 has already exist, fail to move to original file folder';
				}
				
			}
		} catch (Exception $e) {
			$output['msg'] 						= $e->getMessage();
			$output['ack'] 						= '-1';
			self::rollbackTransaction($Tran);
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_850FAIL,$orderID,$e->getMessage());
		}
		return $output;
	}
	/**
	*	Insert One order from EDI 850 to ERP system
	*	@param ponum 								(string)po number
	*/
	public static function InsertOneOrder($ponum)
	{
		try {
			$body850 							= Milk_BODY::getBodyObjByPOnum($ponum);
			$order 								= new EDIWithERP();
			$result 							= $order->InsertPO2Orders($body850);
		} catch (Exception $e) {
			$result 							= array();
			$result['ack'] 						= '-1';
			$result['msg'] 						= $ponum.' has fail to insert';
			$result['error'] 					= $e->getMessage();
		}
		return $result;
	}
	/**
	*	Generate DS order 855 file , save the at the same directory as the po's 850 file , and send the file to amazon
	*	@param $orderID 									(string)
	*	@return check Generate855andSend()
	*/
	public static function Reject850AndSend855($orderID){
		return self::Generate855andSend($orderID,BAK::BAK02_REJECT);		
	}
	/**
	*	Generate DS order 855 file , save the at the same directory as the po's 850 file , and send the file to amazon
	*	@param $order_id 									(string)order code in edi_log table
	*	@return check Generate855andSend()
	*/
	public static function Accept850AndSend855($orderID)
	{
		return self::Generate855andSend($orderID,BAK::BAK02_ACCEPT);
	}
	/**
	*	fully accept or reject 855
	*	@param _855_type 									(BAK02 CONST)
	*	@return (array) 									[ack]
	*														[msg]
	*														[error]
	*/
	public function Generate855andSend($orderID,$_855_type)
	{
		$output 								= array(
				'ack' 							=>0,
				'msg' 							=>'',
				'error' 						=>''
			);
		try {
			$edilog 							= new EDILog;
			$find 								= $edilog->initWithOrderCode($orderID);
			if (!$find) {
				throw new Exception("Can not find $orderID in order log");
			}
			$ponum 								= $edilog->getPo_number();
			$useraccount 						= $edilog->getUser_account();
			$EDIStore 							= new EDIStore();
			$store_flag 						= $EDIStore->initWIthUserAccount($useraccount);
			if (!$store_flag) {
				throw new Exception("Fail to find store with useraccount: $useraccount");
			}
			$obj850 							= Milk_EDI850::Get850ObjByPOnum($ponum,self::AMAZON_DS_COLUMN,self		::AMAZON_DS_LINE);
			if ($_855_type == BAK::BAK02_ACCEPT) {
				$ack 							= ACK::ACK01_IA;
			}else{
				$ack 							= ACK::ACK01_IR;
			}
			$result 							= Milk_EDI855::GenerateDS855FileBy850Obj($ponum,$obj850,$_855_type,$ack,self::Enviroment);
			if ($result) {
				$path 							= $result['path'];
				$gsid 							= $result['gsid'];
				$output['path'] 				= $path;
				$output['gsid'] 				= $gsid;
				EDILog::UpdateEDILog(EDILog::LOG_COLUMN_855,EDILog::ISCREATE,$ponum,$gsid);
				$result 						= self::SendFile($EDIStore->getSend_from(),$EDIStore->getSend_to(),$path); 
				if ($result) {
					EDILog::UpdateEDILog(EDILog::LOG_COLUMN_855,EDILog::ISSEND,$ponum);
					$output['ack'] 				= '1';
					$output['msg'] 				= 'success';
				}else{
					EDILog::UpdateEDILog(EDILog::LOG_COLUMN_855,EDILog::FAIL2SEND,$ponum);
					$output['ack'] 				= '-1';
					$output['msg'] 				= 'fail to send 855';
				}
				return $output;
			}else{
				EDILog::UpdateEDILog(EDILog::LOG_COLUMN_855,EDILog::FAIL2CREATE,$ponum);
				$output['ack'] 					= -1;
				$output['msg'] 					= 'fail to generate 855';
				return $output;
			}
			//ob_clean();
		} catch (Exception $e) {
			$output['ack'] 						= -1;
			$output['msg'] 						= 'fail';
			$output['error'] 					= $e->getMessage();
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_855FAIL,$orderID,$e->getMessage());
			return $output;
		}
	}
	/**
	*	accept po order with quantity given and send 855
	*	@param ponum 							(string)
	*	@param arr 								(array)
	*												[sku]
	*												[quantity]
	*	@return (array) 						
	*											[ack]
	*											[msg]
	*											[detail]
	*											error
 	*/
	public static function Accept850PO($ponum,$arr)
	{

		$output 								= array(
			'ack' 								=> '',
			'msg' 								=> '',
			'error' 							=> ''
			);
		$dp_result 						= self::initDb_helper();
    	$Tran 							= self::getTransaction();
    	self::beginTransaction($Tran);
		try {
			if (!AmazonPurchaseOrder::checkPonumAlreadyExist($ponum)) {
				throw new Exception("po number not exist");
			}
			if (AmazonPurchaseOrder::getErpStateByPonum($ponum)>AmazonPurchaseOrder::STATUS_CREATE) {
				throw new Exception("this order has already accept");
			}
		    //check accept quantity not exceed required quantity
		    if (!AmazonPurchaseOrder::checkNotOverRequiredQuantity($arr,$ponum)) {
		        throw new Exception("accept quantity is more than original quantity");
		    }
			
           	$Amazon 							= new AmazonPurchaseOrder();
			$init_flag 							= $Amazon->initWithPOnum($ponum);
			if (!$init_flag) {
				throw new Exception("Fail to Find PO: ".$ponum);
			}

			$result 							= $Amazon->Accept($arr,$Tran);
			if ($result) {
				self::GeneratePO855ByPonum($ponum);
				$output['ack'] 					= '1';
				$output['msg'] 					= 'Success';
				$output['detail'] 				= $result;
				self::commitTransaciton($Tran);
			}else{
				$output['ack'] 					= '-1';
				$output['msg'] 					= 'Fail';
				self::rollbackTransaction($Tran);
			}
	    } catch (Exception $e) {
	        AmazonPurchaseOrderErrorLog::createErrorLog($ponum,'Accept',$e->getMessage());
	        $output['ack'] 						= '-1';
	        $output['msg'] 						= 'Error';
	        $output['error'] 					= $e->getMessage();
	        self::rollbackTransaction($Tran);
	    }
	    return $output;
	}
	/**
	*	Generate And Send Po 855 obj
	*	@param ponum 							(string)
	*	@return (array) 	
	*											[ack]
	*											[msg]
	*											[error]
	*											[saved_path] optional
	*/
	public static function GeneratePO855ByPonum($ponum)
	{
		$output 									= array(
			'ack' 									=> '',
			'msg' 									=> '',
			'error' 								=> ''
			);
		try {
			//init
			$po 									= new AmazonPurchaseOrder();
			$flag 									= $po->initWithPOnum($ponum);
			if (!$flag) {
				throw new Exception("fail to find po number : $ponum");
			}
			$EDIStore 								= new EDIStore();
			$store_flag 							= $EDIStore->initWIthUserAccount($po->getUser_account());
			if (!$store_flag) {
				throw new Exception("Fail to find store with useraccount: $useraccount");
			}
			$_850 									= new Milk_EDI850();
			$flag_850 								= $_850->initWithPOInput($po->getSaved_path(),self::AMAZON_PO_COLUMN,self::AMAZON_PO_LINE);
			if (!$flag_850) {
				throw new Exception("Can not find EDI 850 file by Path :".$po->getSaved_path());
			}
			$EDILog  								= new EDILog;
			$flag_log								= $EDILog ->initWithPOnumAndUseraccount($ponum,$po->getUser_account());
			if (!$flag_log) {
				throw new Exception("Fail to find EDI log by ponum: ".$ponum);
			}
			//generate
			$BAK02 									= BAK::BAK02_AD;
			if ($po->checkAnyProductQuantityHasChanged()) {
				$BAK02 								= BAK::BAK02_AC;
			}
			$_855 									= new Milk_EDI855();
			$_855 									->initPO();
			$gsid 									= $EDILog->getID().Milk_EDI855::AMAZON_855GSID_POSTFIX;
			$_855 									->initControlNum($_850->getISA()->getISA13(),$gsid,$gsid,$EDIStore->getStore());
			$_855 									->GeneratePOHeader($po->getPonum(),$BAK02);
			$_855items 								= self::Generate855ItemByPOdetails($po->getDetails());
			$_855 									->setItems($_855items);
			$_855 									->GeneratePOFooter();
			$save_path 								= substr($po->getSaved_path(), 0,strlen($po->getSaved_path())-3);
			$flag_export 							= $_855->ExportPO855ToFile($ponum.'_855',$save_path);
			if (!$flag_export) {
				throw new Exception("Fail to export 855 edi file ".$ponum);
			}
			//var_dump($save_path.$ponum.'_855');
			//send
			$result = self::SendFile($EDIStore->getSend_from(),$EDIStore->getSend_to(),$save_path.$ponum.'_855');
			EDILog::UpdateEDILog(EDILog::LOG_COLUMN_855,EDILog::ISCREATE,$ponum,$gsid);
			if (!$result) {
				EDILog::UpdateEDILog(EDILog::LOG_COLUMN_855,EDILog::FAIL2SEND,$ponum);
				throw new Exception("Fail to send edi 855".$saved_path);
			}
			EDILog::UpdateEDILog(EDILog::LOG_COLUMN_855,EDILog::ISSEND,$ponum);
			$output['ack'] 							= '1';
			$output['msg'] 							= 'POnum :'.$ponum." 855 has sent";
			$output['saved_path'] 					= $saved_path;
		} catch (Exception $e) {
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_855FAIL,$ponum,$e->getMessage());
			$output['ack'] 							= '-1';
			$output['msg'] 							= 'fail';
			$output['error'] 						= $e->getMessage();
		}
		return $output;
	}
	/**
	*	generate 855item by accepted amazonpurchaseorderdetail
	*	@todo backorder is not implement
	*	@param podetails 						(AmazonPurchaseOrderDetail array)
	*	@return 								(item855 array)
	*/
	public static function Generate855ItemByPOdetails(array $POdetails)
	{
		$output 								= array();
		for ($i=0; $i < sizeof($POdetails) ; $i++) { 
			$one 								= $POdetails[$i];
			if (!is_a($one, 'AmazonPurchaseOrderDetail')) {
				throw new Exception("invalid podetail type");
			}
			$items855 							= new Item855();
			$po1 								= PO1::GeneratePoPO1($one->getQuantity(),$one->getCost(),$one->getItemID());
			//confirmed quantity
			$ack_array 							= array();
			if ($one->getQuantity()==$one->getErpAcceptQuantity()) {
				$ACK 							= ACK::GenerateAcceptPoAck(ACK::ACK01_IA,$one->getErpAcceptQuantity());
				$ack_array[] 					= $ACK;
			}else{
				$ACK 							= ACK::GenerateAcceptPoAck(ACK::ACK01_IQ,$one->getErpAcceptQuantity());
				$ack_array[] 					= $ACK;
				$ACK 							= ACK::GenerateRejectPoAck(ACK::ACK01_IR,$one->getQuantity()-$one->getErpAcceptQuantity());
				$ack_array[] 					= $ACK;
			}
			$items855 							->initWithPOdata($po1,$ack_array);
			$output[] 							= $items855;
		}
		return $output;
	}
	/**
	*	Confirm 850 quantity , generate 856 and send 
	*	@param ponum 								(string)
	*	@param arr 									(array)
	*	@return (array)
	*												[ack]
	*												[error]
	*												[msg]
	**/
	public static function Confirm850PO($ponum,$arr,$shipping_method,$package,$tracking)
	{
		$dp_result 						= self::initDb_helper();
    	$Tran 							= self::getTransaction();
    	self::beginTransaction($Tran);
    	$output 						= array(
    		'ack' 						=>'',
    		'error' 					=>'',
    		'msg' 						=>''
    	);
    	try {
    		if (!AmazonPurchaseOrder::checkPonumAlreadyExist($ponum)) {
	        echo "[{'ack':'0','msg':'po number not exist'"."}]";
	        return;
		    }
		    if (AmazonPurchaseOrder::getErpStateByPonum($ponum)!=AmazonPurchaseOrder::STATUS_ACCEPT) {
		        echo "[{'ack':'0','msg':'this order has is not in accept status'"."}]";
		        return;
		    }
		    //check accept quantity not exceed accept quantity
		    if (!AmazonPurchaseOrder::checkNotOverAcceptQuantity($arr,$ponum)) {
		        echo "[{'ack':'0','msg':'confirm quantity is more than accept quantity'"."}]";
		        return;
		    }
		    $amazonOrder = new AmazonPurchaseOrder();
	    	$amazonOrder->initWithPOnum($ponum);
	    	$result = $amazonOrder->Confirm($arr,$shipping_method,$package,$tracking,$Tran);
	    	if ($result) {
	    		$output['ack'] 			= '1';
	    		$output['msg'] 			= '';
	    		self::commitTransaciton($Tran);
	    	}else{
	    		$output['ack'] 			= '-1';
	    		$output['error']		= 'fail to confirm';
	    		self::rollbackTransaction($Tran);
	    	}
    	} catch (Exception $e) {
    		AmazonPurchaseOrderErrorLog::createErrorLog($ponum,'Confirm',$e->getMessage());
    		$output['ack'] 				= '-1';
    		$output['error'] 			= $e->getMessage();
    		$output['msg'] 				= 'fail';
    		self::rollbackTransaction($Tran);
    	}
    	return $output;
	}
	/**
	*	Generate & send 856 
	*	limitation: only allow 1 package and 1 shipment
	*	limitation: shipment id is edi log id
	*/
	public static function GeneratePO856ByPonum($ponum)
	{
		$output 						= array(
			'error' 					=>'',
			'ack' 						=>'',
			'msg' 						=>''
		);
		try{
			$po 						= new AmazonPurchaseOrder();
			$flag 						= $po->initWithPOnum($ponum);
			if (!$flag) {
				throw new Exception("fail to find po number : $ponum");
			}
			$EDIStore 					= new EDIStore();
			$store_flag 				= $EDIStore->initWIthUserAccount($po->getUser_account());
			if (!$store_flag) {
				throw new Exception("Fail to find store with useraccount: $useraccount");
			}
			$EDILog  					= new EDILog;
			$flag_log					= $EDILog ->initWithPOnumAndUseraccount($ponum,$po->getUser_account());
			if (!$flag_log) {
				throw new Exception("Fail to find EDI log by ponum: ".$ponum);
			}
			$array 						= array();
			//var_dump($po->getDetails());
			for ($i=0; $i < sizeof($po->getDetails()) ; $i++) {
				$all 					= $po->getDetails();
				$detail 				= $all[$i];
				$temp 					= array(
					'sku_type' 			=> $detail->getItemID_type(),
					'quantity' 			=> $detail->getErpConfirmQuantity(),
					'Amazon_sku' 		=> $detail->getItemID()
				);
				$array[] 				= $temp;
			}
			$package_array 				= array($array);

			$shipment 					= new Shipment(
				array(
				'shipmentID'			=>$EDILog->getID(), 	//limitation
				'POnum' 				=>$ponum,
				'totalCartoon' 			=>1,			//limitation
				'totalWeightLB' 		=>EDIWithERP::calculatePOorderTotalWeight($ponum),
				'shipMethod' 			=>$po->getShipping_method(),
				'tackingNumber' 		=>EDIWithERP::getTrackingNumberByAmazonPO($po),
				'shippedDate' 			=>$EDILog->getUpdate_time_Date(), 		//assume the last update time is shippedDate
				'shippedTime' 			=>$EDILog->getUpdate_time_Time(),
				'vendor' 				=>$EDIStore->getVendor_code(),
				'SFcity' 				=>Milk_EDIConfig::SF_CITY,
				'SFprovince' 			=>Milk_EDIConfig::SF_PROVINCE,
				'SFpostal' 				=>Milk_EDIConfig::SF_POSTAL,
				'SFcountry' 			=>Milk_EDIConfig::SF_COUNTRY,
				'STCode' 				=>$po->getAddress_code(),
				'packages'				=>$package_array
			),'PO');
		
			$_856 						= new Milk_EDI856;
			$_856 						->initPO856($shipment,$EDIStore->getStore(),self::AMAZON_PO_COLUMN,self::AMAZON_PO_LINE);
			$_856 						->Generate856Header_PO($EDILog->getID(),$EDILog->getID());
			$_856 						->Generate856Body_PO();
			$_856 						->Generate856Footer_PO();
			$_856 						->GenerateExist846ToEDIObj();
			$save_path 					= substr($po->getSaved_path(), 0,strlen($po->getSaved_path())-3);
			$flag_export 				= $_856->arr2file($ponum.'_856',$save_path);
			if (!$flag_export) {
				throw new Exception("Fail to export 856 edi file ".$ponum);
			}
			//var_dump($save_path.$ponum.'_856');
			//send
			$result = self::SendFile($EDIStore->getSend_from(),$EDIStore->getSend_to(),$save_path.$ponum.'_856');
			EDILog::UpdateEDILog(EDILog::LOG_COLUMN_856,EDILog::ISCREATE,$ponum,$EDILog->getID());
			if (!$result) {
				EDILog::UpdateEDILog(EDILog::LOG_COLUMN_856,EDILog::FAIL2SEND,$ponum);
				throw new Exception("Fail to send edi 856".$saved_path);
			}else{
				$output['ack'] 			= '1';
				$output['msg'] 			= 'Success';
			}
			EDILog::UpdateEDILog(EDILog::LOG_COLUMN_856,EDILog::ISSEND,$ponum);
		}catch (Exception $e) {
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_856FAIL,$ponum,$e->getMessage());

			$output['error'] 			= $e->getMessage();
			$output['msg'] 				= 'fail';
			$output['ack'] 				= '-1';
		}
		return $output;
	}
	
	/**
	*	generate 856 file based on Shipment object and send to amazon
	*	@param $shipment 						(Shipment obj)
	*	@return $output 						(array)
	*												[error]
	*												[ack]	1 	success
	*														-1  fail
	*												[msg]
	*												[gsid] 	{opt} gs06 used for edi log
	*												[path] 	{opt} 856 saved path
	*/
	public static function GenerateDS856AndSend($Shipment)
	{	
		$output 						= array(
			'error' 					=>'',
			'ack' 						=>'',
			'msg' 						=>''
		);
		try {
			if (!is_a($Shipment, "Shipment")) {
				/*ob_start();
				var_dump($Shipment);
				$error = ob_get_clean();*/
				throw new Exception("Invalid input type shipment, $error");
			}
			$ponum 						= $Shipment ->POnum;
			if (!isset($ponum)||empty($ponum)) {
				/*ob_start();
				var_dump($Shipment);
				$error = ob_get_clean();*/
				throw new Exception("empty purchase order number ,$error");
			}
			$obj850 					= Milk_EDI850::Get850ObjByPOnum($ponum,self::AMAZON_DS_COLUMN,self		::AMAZON_DS_LINE);
			if (!$obj850) {
				throw new Exception("fail to get obj850");
			}
			$body850 					= Milk_BODY::getBodyObjByPOnum($ponum);
			if (!$body850) {
				throw new Exception("fail to get body 850");
			}
			$saved_path 				= $body850->getBodyFolder();
			if (empty($saved_path)) {
				return false;
			}
			//erp order id
			$orderID 					= $Shipment->orderCode;	
			$useraccount 				= EDIWithERP::getStoreCodeByOrderID($orderID);
			if (!$useraccount||empty($useraccount)) {
				throw new Exception("Fail to get useraccount by order ID : $orderID");
			}
			$EDIStore 					= new EDIStore();
			$store_flag 				= $EDIStore->initWIthUserAccount($useraccount);
			if (!$store_flag) {
				throw new Exception("Fail to init EDIStore with useraccount : $useraccount");
			}

			$amazon_code 				= $EDIStore->getStore();
			$_856 						= new Milk_EDI856();
			$_856						->InitDS856($body850,$obj850,$Shipment,self::AMAZON_PO_COLUMN,self::AMAZON_PO_LINE);
			$_856						->Generate856_DS(self::Enviroment,$amazon_code);
			
			$path 						= $_856->arr2file("856_".$ponum,$saved_path);
			if ($path) {
				$gsid 					= $_856->getGS06();
				EDILog::UpdateEDILog(EDILog::LOG_COLUMN_856,EDILog::ISCREATE,$ponum,$gsid);
				$result 				= self::SendFile($EDIStore->getSend_from(),$EDIStore->getSend_to(),$path);
				if ($result) {
					$output['msg'] 		= 'success';
					$output['ack'] 		= '1';
					$output['path'] 	= $path;
					$output['gsid'] 	= $gsid;
					EDILog::UpdateEDILog(EDILog::LOG_COLUMN_856,EDILog::ISSEND,$ponum,$gsid);
				}else{
					EDILog::UpdateEDILog(EDILog::LOG_COLUMN_856,EDILog::ISSEND,$ponum,$gsid);
					throw new Exception("Successfully generate 856 ,however fail to send");
				}
			}else{
				EDILog::UpdateEDILog(EDILog::LOG_COLUMN_856,EDILog::FAIL2CREATE,$ponum);
				throw new Exception("fail to save 856 file");
			}
		} catch (Exception $e) {
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_856FAIL,$orderID,$e->getMessage());

			$output['error'] 			= $e->getMessage();
			$output['msg'] 				= 'fail';
			$output['ack'] 				= '-1';
		}
		return $output;
	}
	/**
	*	generate 846 file and send to amazon
	*	
	*	@param store_code 						(string)  	edi_stroe table store value
	*	@param items 							(EDIproduct array)
	*	@return $output 						(array)
	*												[ack] 1:success
	*													 -1:fail
	*												[msg]	
	*												[error] {opt}				
	*												[insert_id] {opt} only appear if success
	*/
	public static function Generate846AndSend($items,$useraccount)
	{
		$dp_result 							= self::initDb_helper();
    	$Tran 								= self::getTransaction();
    	self::beginTransaction($Tran);
    	$output 							= array(
    		'ack' 							=>'',
    		'error' 						=>'',
    		'msg' 							=>''
    	);
		try {
			if (EDIStore::checkStoreExistByUser_account($useraccount)) {
				$EDIStore 					= NEW EDIStore;
				$EDIStore 					->initWIthUserAccount($useraccount);
			}else{
				throw new Exception("fail to find edi store by store code");
			}
			if (!is_array($items)) {
				throw new Exception("items is not an array");
			}
			if (!Milk_EDI846Log::create846Log($Tran,$EDIStore->getUser_account())) {
				throw new Exception("fail to create edi 846 log");
			}
			$insert_id 						= $Tran ->insert_id;
			$_846 							= new Milk_EDI846();
			$_846 							->init();
			$_846 							->initHeader($EDIStore->getStore());
			$_846 							->Generate846Header($insert_id,$insert_id,$insert_id,$insert_id,Milk_EDIConfig::DEFAULT_WAREHOUSE);
			$items_846 						= array();
			for ($i=0; $i < sizeof($items) ; $i++) {
				$one 						= $items[$i];
				if (!is_a($one, 'EDIProduct')) {
					throw new Exception("invalid items type");
				}
				$item_846 					= new Item846;
				$item_846 					->initWithInput($one->getSku(),$one->getTarget_quantity(),Milk_EDIConfig::DEFAULT_WAREHOUSE);
				$items_846[] 				= $item_846;
			}
			$_846 							->setDetails($items_846);
			$_846 							->GenerateExist846ToEDIObj();
			$_846 							->GeneratePOFooter();
			$_846 							->GenerateExist846ToEDIObj();
			$name 							= '846';
			if (Milk_EDIfunctionl::Create846Directory($insert_id)) {
				$save_flag 					= $_846->arr2file($name,Milk_EDIConfig::EDI_846_FOLDER.$insert_id.'/');
				if (!$save_flag) {
					throw new Exception("fail to create EDI 846 file");
				}
				$saved_846 					= Milk_EDIConfig::EDI_846_FOLDER.$insert_id.'/846';
				$update_log 				= Milk_EDI846Log::Update846logStatus($Tran,$insert_id,Milk_EDI846Log::STATUS_846_SAVED,$saved_846);
				if (!$update_log) {
					throw new Exception("fail to update EDI 846 log to saved status");
				}
				$send_flag 					= self::SendFile($EDIStore->getSend_from(),$EDIStore->getSend_to(),$saved_846);
				if ($send_flag) {
					$update_log 			=  Milk_EDI846Log::Update846logStatus($Tran,$insert_id,Milk_EDI846Log::STATUS_SEND);
					if ($update_log) {
						for ($i=0; $i <sizeof($items) ; $i++) { 
							$one 			= $items[$i];
							$temp 			= $one->updateLastID($insert_id,$Tran);
							if (!$temp) {
								throw new Exception("fail to update item last_846_id");
							}
						}
						self::commitTransaciton($Tran);
						$output['msg'] 		= 'success';
						$output['ack'] 		= '1';
						$output['insert_id']= $insert_id;
					}else{
						throw new Exception("fail to update EDI 846 log to send status");
					}
				}else{
					throw new Exception("fail to send 846");
				}
			}else{
				throw new Exception("Fail to create folder: ".Milk_EDIConfig::EDI_846_FOLDER.$insert_id);
			}
		} catch (Exception $e) {
			self::rollbackTransaction($Tran);
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_846FAIL,$filename,$e->getMessage());
			$output['error'] 					= $e->getMessage();
			$output['msg'] 						= 'fail';
			$output['ack'] 						= '-1';
		}
		return $output;
	}
	/**
	*	update po status based on 997 file
	*	limitation: 997 content is not valid, as long as 997 is received , the program assumes that every thing goes right and will update edi log state
	* 	@param filename 							(string) file name {full path}
	*	@return (array) 							
	*												[ack]
	*												[msg]
	*												[error]
	*/
	public static function InsertDS997($filename)
	{
		$array 										= array(
			'ack' 									=>'',
			'msg' 									=>'',
			'error' 								=>''
		);
		$dp_result 									= self::initDb_helper();
    	$Tran 										= self::getTransaction();
    	self::beginTransaction($Tran);
		try {
			$file 									= EDIObj::file2arr($filename,self::AMAZON_DS_COLUMN,self::AMAZON_DS_LINE);
			$Obj997 								= new Milk_EDI997($file,self::AMAZON_DS_COLUMN,self::AMAZON_DS_LINE);
			$Obj997 								-> Generate();
			$EDItype 								= $Obj997 ->getAcknowledgeType();
			$EDIAK1ID 								= $Obj997 ->getAcknowledgeID();
			if ($EDItype == AK1::AK101_846) {
				$log_flag 							= Milk_EDI846Log::Update846logStatus($Tran,$EDIAK1ID,Milk_EDI846Log::STATUS_997_RECEIVED);
				if ($log_flag) {
					//to-do cehck 997 valid
					$product_flag 					= EDIProduct::updateProductQuantityBylast846Id($EDIAK1ID,$Tran);
					if ($product_flag) {
						$path 						= Milk_EDI846Log::getDirectoryById($EDIAK1ID);
						if ($path) {
							$index_flag 			= $Obj997->arr2file($EDIAK1ID.'_997',$path);
							if ($index_flag) {
								$path_array 		= explode('/', $filename);
								$name 				= $path_array[sizeof($path_array)-1];
								$prefix				= substr($name, 0,strlen($name)-strlen(Milk_EDIConfig::PAYLOAD_EXT)-1);
								if (EDIObj::MoveOriginalFiles(Milk_EDIConfig::EDI_RECEIVE_PATH,$prefix)) {
									$array['ack'] 	= '1';
									$array['msg'] 	= 'success';
								}else{
									throw new Exception("Fail to move 997");
								}
							}else{
								throw new Exception("Fail to save 997 in corresponding 846 folder");
							}
						}else{
							throw new Exception("Fail to get 846 file path");
						}
					}else{
						throw new Exception("Fail to update edi product");
					}
				}else{	
					throw new Exception("Fail to update 846 log");
				}
			}else{
				//855 856
				if ($ponum=EDILog::check997ID($EDItype,$EDIAK1ID)) {
					//TO-DO check 997 valid
					$array['ponum'] 				= $ponum;
				    $column 						= '';
				    if ($EDItype==AK1::AK101_855) {
			    		$column 					= EDILog::LOG_COLUMN_855;
			    	}elseif ($EDItype==AK1::AK101_856) {
			    		$column 					= EDILog::LOG_COLUMN_856;
			    	}
				    $log_check 						= EDILog::UpdateEDILog($column,EDILog::ISRECEIVED997,$ponum);
				    
				    if ($log_check) {
				    	$file_prefix 				= '';
				    	if ($EDItype==AK1::AK101_855) {
				    		$file_prefix 			= '855';
				    	}elseif ($EDItype==AK1::AK101_856) {
				    		$file_prefix 			= '856';
				    	}
				    	$saved_path 				= self::getDsEDI850BodyFilePath($ponum);
				    	$path_array 				= explode('/', $filename);
						$name 						= $path_array[sizeof($path_array)-1];
				    	$prefix 					= substr($name, 0,strlen($name)-strlen(Milk_EDIConfig::PAYLOAD_EXT)-1);
				    	if (file_exists(EDIObj::getCurrentDir().'/'.$saved_path.$file_prefix.'_'.$ponum.'_997')) {
				    		if (EDIObj::MoveOriginalFiles(Milk_EDIConfig::EDI_RECEIVE_PATH,$prefix)) {
				    			self::commitTransaciton($Tran);
								$array['ack'] 		= '1';
								$array['msg'] 		= '997 is already exist in the system, this 997 file has successfully moved to original files folder';
							 }else{
							 	throw new Exception("997 is already exist in the system, however fail to move origianl files");
							 }
				    	}else{
				    		$index_flag 			= $Obj997->arr2file($file_prefix.'_'.$ponum.'_997',$saved_path);
				    		if ($index_flag) {
								if (EDIObj::MoveOriginalFiles(Milk_EDIConfig::EDI_RECEIVE_PATH,$prefix)) {
									self::commitTransaciton($Tran);
									$array['ack'] 	= '1';
									$array['msg'] 	= 'success '.$saved_path;	
								 }else{
								 	throw new Exception("successfully insert to DB, however fail to move origianl files. Check error log $saved_path");
								 }
					    	}else{
					    		throw new Exception("Fail to saved in Files $saved_path");
					    	}
				    	}
				    }else{
				    	throw new Exception("fail to update log");
				    }
				}else{
					throw new Exception("Fail to get correspoding EDI file");
				}
			}
			
		} catch (Exception $e) {
			self::rollbackTransaction($Tran);
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_997FAIL,$filename,$e->getMessage());
			$array['ack'] 						= '-1';
			$array['error'] 					= $e->getMessage();
		}
		return $array;
	}
	public function InsertPO997($filename)
	{

		$array 									= array(
			'ack' 								=>'',
			'msg' 								=>'',
			'error' 							=>''
		);
		$dp_result 								= self::initDb_helper();
    	$Tran 									= self::getTransaction();
    	self::beginTransaction($Tran);
		try {
			$file = EDIObj::file2arr($filename,self::AMAZON_PO_COLUMN,self::AMAZON_PO_LINE);
			$Obj997 = new Milk_EDI997($file,self::AMAZON_PO_COLUMN,self::AMAZON_PO_LINE);
			$Obj997 -> Generate();
			$EDItype 							= $Obj997 ->getAcknowledgeType();
			$EDIAK1ID 							= $Obj997 ->getAcknowledgeID();
			if ($ponum=EDILog::check997ID($EDItype,$EDIAK1ID,'PO')) {
				if ($EDItype==AK1::AK101_855) {
		    		$column 					= EDILog::LOG_COLUMN_855;
		    	}elseif ($EDItype==AK1::AK101_856) {
		    		$column 					= EDILog::LOG_COLUMN_856;
		    	}
			    $log_check 						= EDILog::UpdateEDILog($column,EDILog::ISRECEIVED997,$ponum);
			    if ($log_check) {
			    	$file_prefix 				= '';
			    	if ($EDItype==AK1::AK101_855) {
			    		$file_prefix 			= '855';
			    	}elseif ($EDItype==AK1::AK101_856) {
			    		$file_prefix 			= '856';
			    	}
			    	$saved_path 				= self::getPoEDI850BodyFilePath($ponum);
			    	$path_array 				= explode('/', $filename);
					$name 						= $path_array[sizeof($path_array)-1];
			    	$prefix 					= substr($name, 0,strlen($name)-strlen(Milk_EDIConfig::PAYLOAD_EXT)-1);
			    	if (file_exists($saved_path.$file_prefix.'_'.$ponum.'_997')) {
			    		if (EDIObj::MoveOriginalFiles(Milk_EDIConfig::EDI_RECEIVE_PATH,$prefix)) {
			    			self::commitTransaciton($Tran);
							$array['ack'] 		= '1';
							$array['msg'] 		= '997 is already exist in the system, this 997 file has successfully moved to original files folder';
						 }else{
						 	throw new Exception("997 is already exist in the system, however fail to move origianl files");
						 }
			    	}else{
			    		$index_flag = $Obj997->arr2file($file_prefix.'_'.$ponum.'_997',$saved_path);
			    		if ($index_flag) {
							if (EDIObj::MoveOriginalFiles(Milk_EDIConfig::EDI_RECEIVE_PATH,$prefix)) {
								self::commitTransaciton($Tran);
								$array['ack'] 	= '1';
								$array['msg'] 	= 'success '.$saved_path;	
							 }else{
							 	throw new Exception("successfully insert to DB, however fail to move origianl files. Check error log 
							 		");
							 }
				    	}else{
				    		throw new Exception("Fail to saved in Files $saved_path");
				    	}
			    	}
			    }else{
			    	throw new Exception("fail to update log");
			    }
			}else{
				throw new Exception("Fail to get correspoding EDI file");
			}
		}catch (Exception $e) {
			self::rollbackTransaction($Tran);
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_997FAIL,$filename,$e->getMessage());
			$array['error'] 					= $e->getMessage();
		}
		return $array;
	}
	/**
	*	NEED TO BE implemenet function
	*	send EDI file from local to amazon 
	*	(default is using as2Secure library)
	*	@param 									(string)
	*	@param 									(string)
	*	@param 									(string) file path
	*/
	public function SendFile($partner_from,$partner_to,$file){	
		/**
		*@todo*/
		$params = array(
			'partner_from'  		=> $partner_from,
			'partner_to'    		=> $partner_to
		);
		$message 					= new AS2Message(false, $params);
		if (!file_exists($file)) {
			throw new Exception("file not exist");
		}
		$message 					->addFile($file);
		$message 					->encode();
		//AS2 
		$client 					= new AS2Client();
		$result 					= $client->sendRequest($message);
		echo "<pre>";
		var_dump($result["response"]);
		echo "</pre>";
		//var_dump($result["response"]);
		if ($result["response"]) {
			return true;
		}else{
			return false;
		}
	}
	/**
	*	send already generate or already exist file from local to amazon
	*	@param type:							(string) 850 856 855
	*	@param ponum: 							(string) purchase order number
	*	@return $ouput							(array) [msg]
	*													[ack]
	*/
	public static function sendEDIFileWithTypeAndPOnum($type,$ponum)
	{
		$output 					= array();
		$po_check 					= Milk_BODY::getBodySavedPathByPOnum($ponum);
		if ($po_check) {
			$path 					= substr($po_check, 0,strlen($po_check)-strlen($ponum)).$type."_".$ponum;
			if (!file_exists($path)) {
				$output['msg'] 		= "File: $path does not exist";
				$output['ack'] 		= '-1';
				return $output;
			}
			$edilog 				= new EDILog;
			$find 					= $edilog->initWithPOnum($ponum);
			if ($find) {
				throw new Exception("Fail to find po in system");
			}
			$store 					= new EDIStore();
			$result 				= $store->initWithUser_account($edilog->getUser_account());
			if (self::SendFile($store->getSend_from(),$store->getSend_to(),$path)) {
				$output['msg'] 		= "Successfully send";
				$output['ack'] 		= '1';
				return $output;
			}else{
				$output['msg'] 		= "Fail to send";
				$output['ack'] 		= '-1';
				return $output;
			}
		}else{
			$output['msg'] 			= "This purchase order does not exist in DB";
			$output['ack'] 			= '0';
			return $output;
		}
	}
	/**
	*	delete target file based on edi type and po number
	*	@param type:							(string) 850 856 855
	*	@param ponum: 							(string) purchase order number
	*	@return echo
	*/
	public static function deleteEDIfileWithTypeAndPOnum($type,$ponum)
	{
		$output 					= array();
		$po_check 					= Milk_BODY::getBodySavedPathByPOnum($ponum);
		if ($po_check) {
			$path 					= substr($po_check, 0,strlen($po_check)-strlen($ponum)).$type."_".$ponum;
			echo $path;
			if (!file_exists($path)) {
				echo "File: $path does not exist";
				return false;
			}
			if (unlink($path)) {
				echo "Successfully delete";
			}else{
				echo "Fail to delete";
			}
		}else{
			echo "This purchase order does not exist in DB";
		}
	}
	/**
	*	get target ponum EDI 850 path
	*	@param ponum 							(string)
	*/
	public static function getDsEDI850BodyFilePath($ponum)
	{
		$po_check = Milk_BODY::getBodySavedPathByPOnum($ponum);
		if ($po_check) {
			$path = substr($po_check, 0,strlen($po_check)-strlen($ponum));
			return $path;
		}else{
			throw new Exception("Can not get ponum file path");
		}		
	}
	public static function getPoEDI850BodyFilePath($ponum)
	{
		$po_check = AmazonPurchaseOrder::getSaved_pathByPonum($ponum);
		if ($po_check) {
			$path 	= substr($po_check, 0,strlen($po_check)-3);
			return $path;
		}else{
			throw new Exception("fail to find ponum: $ponum");
		}
	}
}
?>