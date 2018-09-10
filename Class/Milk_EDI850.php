<?php
/*
	michael lee production
	Manage received EDI 850 && Manage EDI 850 table
	only include amazon_order_edi && amazon_order_edi_detail
*/
require_once(dirname(__FILE__) . "/EDIsegment/N1.php");
require_once(dirname(__FILE__) . "/EDIsegment/N4.php");
require_once(dirname(__FILE__) . "/EDIsegment/PO1.php");
require_once(dirname(__FILE__) . "/EDIsegment/GS.php");
require_once(dirname(__FILE__) . "/EDIsegment/EDI850Seg.php");
require_once(dirname(__FILE__) . "/EDIsegment/Require_once.php");
require_once(dirname(__FILE__) . "/EDIObj.php");
require_once(dirname(__FILE__) . "/EDIPart.php");
require_once(dirname(__FILE__) . "/Milk_EDIfunctionl.php");
require_once(dirname(__FILE__) . "/Milk_EDI850BODY.php");
require_once(dirname(__FILE__) . "/AmazonPurchaseOrder.php");
class Milk_EDI850 extends EDIObj{
	const AMAZON_TABLE 						= 'amazon_order_edi_header';
	
	CONST TYPE_DS 							= 'ds';//Dropship 		
	CONST TYPE_PO 							= 'po';//Purchase Order
	
	private $BODY_array;					//BODY obj array
	private $original_path; 				//850 file saved path
	private $user_account; 					//

	public function __construct(){
		
	}
	/**
	*	init edi Dropship 850 by edi file
	*	@param filename 					(string) edi 850 file path
	*	@param seperator 					(string) column seperator
	*	@param line 						(string) line seperator
	*	@return boolean
	*/
	public function initWithDSInput($filename,$seperator = Milk_EDI::AMAZON_DS_COLUMN,$line =Milk_EDI::AMAZON_DS_LINE)
	{
		$data 		= EDIObj::file2arr($filename,$seperator,$line);
		if ($data) {
			parent::__construct($seperator,$line,$data);
			return true;
		}else{
			return false;
		}
	}
	/**
	*	init edi PurchaseOrder 850 by edi file
	*	@param filename 					(string) edi 850 file path
	*	@param seperator 					(string) column seperator
	*	@param line 						(string) line seperator
	*	@return boolean
	*/
	public function initWithPOInput($filename,$seperator = Milk_EDI::AMAZON_PO_COLUMN,$line = Milk_EDI::AMAZON_PO_LINE)
	{
		$data 		= EDIObj::file2arr($filename,$seperator,$line);
		if ($data) {
			parent::__construct($seperator,$line,$data);
			return true;
		}else{
			return false;
		}
	}
	/**
	*	set 850 file store path
	*	@param value 						(string) path
	*/
	public function setOriginalPath($value)
	{
		$this->original_path = $value;
	}
	public function getOriginalPath()
	{
		if (isset($this->original_path)) {
			return $this->original_path;
		}else{
			return "";
		}
	}
	public function getBODY_array()
	{
		return $this->BODY_array;
	}
	public function setUser_account($user_account)
	{
		$this->user_account = $user_account;
	}
	/**
	*	generate DS 850 file orders by raw data
	*	@return $array 						(Body array)850 body array
	*/
	public function GenerateDSBODY_array()
	{
		$array 				= $this->GenerateBODY_array();
		$body 				= array();
		for ($i=0; $i < sizeof($array); $i++) {
			$temp 			= new Milk_BODY($array[$i],parent::getSeparator(),parent::getLine());
			$reult 			= $temp ->initDSBody();
			$body[] 		= $temp;
		}
		$this->BODY_array 	= $body;
		return $array;
	}
	/**
	*	generate PO 850 file orders by raw data
	*	@return $array 						(Body array)850 body array	
	*/
	public function GeneratePOBody_array()
	{
		$array 				= $this->GenerateBODY_array();
		$body 				= array();
		for ($i=0; $i < sizeof($array) ; $i++) { 
			$temp 			= new Milk_BODY($array[$i],parent::getSeparator(),parent::getLine());
			$result 		= $temp ->initPOBody();
			$body[] 		= $temp;
		}
		$this->BODY_array 	= $body;
		return $array;
	}
	/**
	*	One 850 file can contain multiple purchase order.
	*	each oder is one element in STSE_array
	*		the size of STSE_array should match GE 01
	*	@return $array 						(array array)
	*/
	public function GenerateBODY_array()
	{	
		$array 			= array();
		$data 			= parent::getEDIarray();
		for ($i=0; $i <sizeof($data) ; $i++) { 
			$header = $data[$i][0];
			if (strlen($header)>2) {
				$header = substr($header, 0,2);
			}
			$element = $data[$i];
			if ($header=="ST") {
				$one =array();
				$one[] = $element;
			}elseif($header=="SE"){
				$one[] = $element;
				$array[] = $one;
			}elseif ($header=="GE") {
				break;
			}else{
				if (isset($one)) {
					$one[]=$element;
				}
			}
		}
		return $array;
	}
	/**
	*	get ISA segment from raw data
	*	@return 							(ISA|array) 
	*/
	public function getISA(){
		$ISAarray = $this->getFirstArrayByName("ISA");
		if (isset($ISAarray)) {
			return new ISA($ISAarray);
		}else{
			throw new Exception("can not find ISA in 850");
			return array();
		}
	}
	/**
	*	get GS segment from raw data
	*	@return 							(GS|array) 
	*/
	public function getGS(){
		$GSarray = $this->getFirstArrayByName("GS");
		if (isset($GSarray)) {
			return new GS($GSarray);
		}else{
			return array();
		}
	}
	/**
	*	get GE segment from raw data
	*	@return 							(GE|array) 
	*/
	public function getGE(){
		$GSarray = $this->getFirstArrayByName("GE");
		if (isset($GSarray)) {
			return new GE($GSarray);
		}else{
			return array();
		}
	}
	/**
	*	get GE 06,Group control number
	*	@return 							(string|null) 
	*/
	public function getGS06(){
		$GS = $this->getGS();
		if (isset($GS)>6) {
			return $GS->getGS06();
		}else{
			return null;
		}
	}
	/**
	* @param type 						(const) po | ds
	* @param Tran 						(transaction obj)
	* @param filename 					(string) 850 origianl fiile path
	*	
	* @return 	
	*		false 						if insert fail
	*		last_insert_id 				table last insert id
	*		exception
	*/
	public function InsertIntoDB($type,&$Tran,$filename){
		switch ($type) {
			case self::TYPE_PO:
				return $this->InsertPo2Local($filename,$Tran);
				break;
			case self::TYPE_DS:
				$result = $this->InsertDs2Local($Tran,$filename);
				return $result;
				break;
			default:
				throw new Exception("No such type");
				break;
		}
	}
	/**
	*	Insert 850 PO edi to Database
	*	@param filename 					(string) 850 original file path
	*	@param $tran 						(transaction)
	*	@return exception
	*	@return $output 					(array)
	*											[success] 	(int) number of order success insert
	*											[fail]		(int) number of order fail to insert
	*											[exist] 	(int) number of order already exist
	*/
	public function InsertPo2Local($filename,&$Tran)
	{
		$temp							= $this ->GeneratePOBody_array();
		$user_account   				= $this ->user_account;
		$bodys        					= $this ->getBODY_array();
		$output 						= array();
		$output['ack'] 					= '1';
		$success 						= 0;
		$fail 							= 0;
		$exist 							= 0;
		for ($i=0; $i < sizeof($bodys) ; $i++) { 
		    $one        				= $bodys[$i];
		    $po         				= new AmazonPurchaseOrder;
		    $temp       				= $po ->initWithEDI850Bodyfile($one,$user_account);
		    $result     				= $po->insert2Local($Tran);
		    if ($result&&isset($result['ack'])) {
		    	switch ($result['ack']) {
		    		case '1':
		    			$date = Milk_EDIfunctionl::yymmdd();
		    			if (Milk_EDIfunctionl::CreateDirectory($date)) {
		    				$log_flag 		= EDILog::InsertEDILog($Tran,$user_account,$po->getPonum(),EDILog::ORDER_TYPE_PO);
			    			if (!$log_flag) {
			    				throw new Exception("Fail to Create edi Log");
			    			}
			    			$insert_id 		= $Tran ->insert_id;
			    			if (!Milk_EDIfunctionl::CreateDirectory($date."/".$insert_id)) {
			    				throw new Exception("Fail to Create Directory ".$date."/".$insert_id);
			    			}
			    			$newpath 		= Milk_EDIConfig::SAVED_PATH.$date."/".$insert_id.'/850';
			    			$move_flag 		= Milk_EDIfunctionl::MoveToTargetFolder($filename,$newpath);
			    			if (!$move_flag) {
			    				throw new Exception("Fail to move edi 850 to save path, with PO num: ".$po->getPonum());
			    			}
			    			$update_flag 	= $po ->update850SavedPath($Tran,$newpath);
			    			if (!$update_flag) {
			    				throw new Exception("Fail to update update edi 850 save path, with PO num: ".$po->getPonum());
			    			}
		    			}else{
		    				throw new Exception("Fail to Create Directory");
		    			}
		    			$success++;
		    			break;
		    		case '0':
		    			$exist++;
		    			break;
		    		case '-1':
		    			$fail++;
		    			break;
		    		default:
		    			throw new Exception("unrecognized insert order return :".$result['ack']);
		    			break;
		    	}
		    }
		    if (!$result) {
		        $output = false;
		        break;
		    }
		    $output['success'] 		= $success;
		    $output['exist'] 		= $exist;
		    $output['fail'] 		= $fail;
		}
		return $output;
	}
	/**
	*	Insert 850 DS edi to Database
	*	@param filename 					(string) 850 original file path
	*	@param $tran 						(transaction)
	*	@return exception
	*	@return true 					
	*/
	private function InsertDs2Local(&$Tran,$filename)
	{	
		$user_account = $this->user_account;
		$query  = "INSERT INTO ".self::AMAZON_TABLE."(
			isa_auth_quali,
			isa_auth,
			isa_sec_quali,
			isa_sec,
			isa_interchange_sender_quali,
			isa_interchange_sender_id,
			isa_interchange_receiver_quali,
			isa_interchange_receiver_id,
			isa_interchange_date,
			isa_interchange_time,
			isa_std_iden,
			isa_interchange_ver,
			isa_interchange_num,
			isa_ack_req,
			isa_usage_indi,
			isa_separator,";
		$query.= "
			gs_funct_id,
			gs_app_sender,
			gs_app_receiver,
			gs_date,
			gs_time,
			gs_group_control,
			gs_res,
			gs_ver,";
		$query.="ge_count,";
		$query.="path,
			create_time,
			user_account,
			state";
		$query .="	) VALUES ( ";
		$query .= $this->getISA()->QueryValuesString().",";
		$query .= $this->getGS()->QueryValuesString().",";
		$query .= $this->getGE()->getGE01().",";
		$query .= "'".Milk_EDI::sql_string($this->getOriginalPath())."','".GeneralDateHelper::GetCurDatetime()."','$user_account','0'";
		$query .=")";
		$result = self::queryInsertWithTran($query,$Tran);
		$insert_id 		= $Tran ->insert_id;
		$date = Milk_EDIfunctionl::yymmdd();
		if ($result) {
			//move file to 850 files
			if (Milk_EDIfunctionl::Create850FilesDirectory($date)) {
				$newpath 		= Milk_EDIConfig::EDI_850_FOLDER.$date."/".$insert_id;
				$move_flag 		= Milk_EDIfunctionl::MoveToTargetFolder($filename,$newpath);
				if (!$move_flag) {
					throw new Exception("fail to move edi850 to ".$newpath);
				}
				$update_flag 	= $this->UpdateTableState($Tran,'1',$newpath);
				if (!$update_flag) {
					throw new Exception("fail to update po newpath: $newpath");
				}
			}else{
				throw new Exception("Fail to create directory :".$date);	
			}
			$result =$this->InsertBodyArrayDB($Tran,$insert_id,$user_account,$filename);
			if ($result) {
				return true; 
			}else{
				throw new Exception("fail to insert body array to DB");
			}
			return true;
		}else{
			throw new Exception("fail to insert amazon header edi file");
		}
	}
	/**
	*	insert 850 Bodys objects to DB
	*	only used for DS order
	*	@param tran 							(transaction)
	*	@param header_id 						(string) amazon header table insert id
	*	@param user_account 					(string) 
	*	@param filename  						(string) 850 original file path
	*/
	public function InsertBodyArrayDB(&$Tran,$header_id,$user_account,$filename)
	{
		$this->GenerateDSBODY_array();
		$body = $this->getBODY_array();
		$flag = true;
		for ($i=0; $i <sizeof($body) ; $i++) { 
			$bodyObj = $body[$i];
			if ($flag) {
				$flag = $bodyObj ->InsertBodyDB($Tran,$header_id,$user_account,$filename);
			}
		}
		return $flag;
	}
	/**
	*	Check if 850Obj is already insert into header table by isa13
	* 	@return bolean
	*/
	public function HeaderCheck()
	{
		$user_account   			= $this->user_account;
		$query 		= "SELECT * FROM ".self::AMAZON_TABLE." WHERE isa_interchange_num ='".$this->getISA()->getISA13()."' AND user_account = '$user_account' ";
		$result 	= self::querySelect($query);
		if ($result) {
			return true;
		}else{
			return false;
		}
	}
	/**
	*	@deprecated make suere EDIstore.php is required before using this function
	*	@return false|(string) user_account
	*/
	public function getUser_accountByCode(){
		$code = trim($this->getISA()->getISA08());
		$store = new EDIStore();
		$result = $store->initWithStoreName($code);
		if ($result) {
			return $store->getUser_account();
		}else{
			return false;
		}
	}
	/**
	*	update state and path
	*	@param tran 					(transaction)
	*	@param value 					(string) state
	*	@param path 					(string) path
	*	@return boolean
	*/
	public function UpdateTableState(&$Tran,$value,$path)
	{
		$query = "UPDATE ".self::AMAZON_TABLE." SET path = '$path',state = '$value' WHERE isa_interchange_num = '".$this->getISA()->getISA13()."'";
		$result = self::queryInsertWithTran($query,$Tran);
		return $result;
	}
	/**
	*	only init data, separator and line
	*	@param ponum 					(string) po number
	*	@param separator 				(string) column separator
	*	@param line 					(string) line ssparator
	*	@return false| Body
	*/
	public static function Get850ObjByPOnum($ponum,$separator,$line)
	{
		$query= "SELECT *,".Milk_BODY::AMAZON_BODY_TABLE.".id AS po_id FROM ".Milk_BODY::AMAZON_BODY_TABLE." LEFT JOIN ".self::AMAZON_TABLE." on ".Milk_BODY::AMAZON_BODY_TABLE.".header_id = ".self::AMAZON_TABLE.".id WHERE po_number = '$ponum'";
		//echo "$query";
		$result = self::querySelect($query);
		if ($result&&sizeof($result)>0) {
			$filepath = $result[0]['path'];
			$obj850 = new Milk_EDI850();
			if ($filepath[0]=='/') {
				$obj850->initWithDSInput($filepath,$separator,$line);
			}else{
				$obj850->initWithDSInput(Milk_EDIConfig::EDI_PATH.'/'.$filepath,$separator,$line);
			}
			return $obj850;
		}else{
			return false;
		}
	}
	/**
	*	get 850 file path by po number
	*	@param ponum 						(string)
	*	@return exception
	*	@return path 						(string)
	*/
	public static function Get850FilePathByPOnum($Ponum)
	{
		$query = "SELECT * FROM ".Milk_BODY::AMAZON_BODY_TABLE." LEFT JOIN ".self::AMAZON_TABLE." ON ".Milk_BODY::AMAZON_BODY_TABLE.".header_id = ".self::AMAZON_TABLE.".id WHERE ".Milk_BODY::AMAZON_BODY_TABLE.".po_number = '$Ponum'";
		$result= self::querySelect($query);
		if ($result) {
			return $result[0]['path'];
		}else{
			throw new Exception("no such purchase order in ERP system yet");
		}
	}	
}

/**
*	EDI 850 Body order's product
* 	PO1 loop class && Manage detail table
*/
class ITEM extends EDIPart
{
	private $po1;
	private $ctp;
	private $msg;
	private $refArray;
	private $data_array;
	const ITEM_START_SEGMENT 					= "PO1";
	const ITEM_END_SEGMENT 						= "CTT";

	const AMAZON_DETAIL_TABLE 					= 'amazon_order_edi_detail';
	function __construct($array,$separator,$line)
	{
		parent::__construct($array,$separator,$line);
		if (!isset($array)||sizeof($array)<1) {
			throw new Exception("Invalid PO1loop array");
		}
		for ($i=0; $i <sizeof($array) ; $i++) { 
			if ($array[$i][0]=="PO1") {
				$this->po1 = new PO1($array[$i]);
			}elseif ($array[$i][0] =="CTP") {
				$this->ctp = new CTP($array[$i]);
			}
		}
	}
	public function getPO1(){
		return $this->po1;
	}
	public function getCTP(){
		return $this->ctp; 
	}
	/**
	*	get ctp 03
	*	@return (float) 0.0| (string) price
	*/
	public function getPrice2Customer(){
		$ctp = $this->getCTP();
		if (isset($ctp)) {
			return $this->getCTP()->getCTP03();
		}else{
			return 0.0;
		}
	}
	/*po1 07*/
	public function getSkU()
	{
		return $this->getPO1()->getPO107();
	}
	/*po1 02*/
	public function getQuantity()
	{
		return $this->getPO1()->getPO102();
	}
	/*po1 04*/
	public function getUnitPrice()
	{
		return $this->getPO1()->getPO104();
	}
	/**
	* convert array to string
	* @param separator 						(string)
	* @param line 							(string)
	* @return text 							(string)	
	*/
	public function DataArrayToText($separator,$line)
	{
		$text = "";
		if (isset($this->data_array)) {
			$text = Milk_EDIfunctionl::Array2EDItext($this->data_array,$separator,$line);
		}
		return $text;
	}
	/**
	*	Insert table into local
	*	@param tran 						(transaction)
	*	@param header_id 					(string)
	*	@param ponum 						(string)
	*	@param separator 					(string)
	*	@param line 						(string)
	*	@param user_account 				(string)
	*	@return query result
	*/
	public function InsertIntoDetail(&$Tran,$header_id,$ponum,$seperator,$line,$user_account)
	{
		$po1 = $this->getPO1();
		if (!$po1) {
			return false;
		}
		$ERPSKU = EDIWithERP::CheckProductSku($po1->getPO107(),$user_account);
		if (!$ERPSKU) {
			EDIErrorLog::ErrorLog(EDIErrorLog::ERRORLOG_FAIL2FINDSKU,$ponum,$po1->getPO107());
			$ERPSKU = '';
		}
		$query = "INSERT INTO ".self::AMAZON_DETAIL_TABLE." (
			po_id,
			header_id,";
		$query.= "
			po1_id,
			po1_qty,
			po1_unit,
			po1_unit_price,
			po1_basis_unit,
			po1_bp_id_quali,
			po1_bp_id,
			po1_vp_id_quali,
			po1_vp_id,";
		if (isset($this->ctp)) {
		$query.="
			ctp_price_iden,
			ctp_unit_price,";
		}
		$query.="
			po1_section,
			ERPSKU
			) VALUES (";
		$query.="'$ponum',
			'$header_id',
			'".$po1->getPO101()."',
			'".$po1->getPO102()."',
			'".$po1->getPO103()."',
			'".$po1->getPO104()."',
			'".$po1->getPO105()."',
			'".$po1->getPO106()."',
			'".$po1->getPO107()."',
			'".$po1->getPO114()."',
			'".$po1->getPO115()."',";
		if (isset($this->ctp)) {
			$ctp = $this->ctp;
		$query.="'".$ctp->getCTP02()."',
			'".$ctp->getCTP03()."',";
		}
		$query.= "'".Milk_EDI::sql_string($this->DataArrayToText($seperator,$line))."',";
		$query.= "'$ERPSKU'";
		$query.=")";
		$result = self::queryInsertWithTran($query,$Tran);
		return $result;
	}
	/**
	*	get db array by ponum and sku
	*	@param $sku 							(string)AMAZON sku
	*	@param ponum 							(string) po number
	*	@return false|array
	*/
	public static function getPO1RowbyPOnumAndSKU($ponum,$sku)
	{
		$query 	= "SELECT * FROM ".self::AMAZON_DETAIL_TABLE." WHERE po_id ='$ponum' AND po1_bp_id = '$sku'";
		$result = self::querySelect($query);
		if ($result) {
			return $result[0];
		}else{
			return false;
		}
	}
	/**
	* 	get amazon sku by erp sku and po number
	*	@param erpsku 							(string)
	*	@param ponum 							(string)
	*	@return false|string
	*/
	public static function getEDISKUbyERPSKUAndPOnum($ERPSKU,$Ponum)
	{
	
		$query = "SELECT * FROM ".self::AMAZON_DETAIL_TABLE." WHERE ERPSKU = '$ERPSKU' AND po_id ='$Ponum'";
		$result = self::querySelect($query);
		if ($result) {
			return $result[0]['po1_bp_id'];
		}else{
			return false;
		}
	}
}
/**
* 	Amazon order's receiver name, address , etc
*/
class RECEIVER extends EDIPart
{					
	private $n1; 							//(N1) 
	private $n3array;						//(N3 array)
	private $n4; 							//(N4)
	private $TD5;							// shipping method
	/**
	*	@param $array 						(array)
	*	@param separator 					(string) column separator 
	*	@param line 						(string) line separator
	*/
	function __construct($array,$separator,$line)
	{
		$n1array = $array[0];
		if (!N1::IsN1_STarray($array[0])) {
			throw new Exception("Invalid n1 st array when init Receiver");
		}
		$this->n1 = new n1($array[0]);
		$this->n3array = array();
		$this->n4 = null;

		for ($i=1; $i <sizeof($array) ; $i++) { 
			if ($array[$i][0]=="N3") {
				$this->n3array[] =$array[$i];
			}elseif ($array[$i][0]=='N4') {
				$this->n4 = new N4($array[$i]);
			}elseif ($array[$i][0]=='TD5') {
				$this->TD5=$array[$i];
			}
		}
		parent::__construct($array,$separator,$line);
	}
	/**
	*	GET N1 02
	*/
	public function getName()
	{
		return $this->n1->getN102();
	}
	public function getN1()
	{
		return $this->n1;
	}
	/**
	*	conbine n3 segement in to a single line address
	*	@return address 					(string)
	*/
	public function getN3Address()
	{

		$address = "";
		for ($i=0; $i <sizeof($this->n3array) ; $i++) { 
			$array = $this->n3array[$i];
			$fl=array_shift($array);
			$address .= Milk_EDIfunctionl::Array2EDILine($array," "," ");
		}
		return $address;
	}
	public function getTD5_method()
	{
		if (isset($this->TD5)&&is_array($this->TD5)&&sizeof($this->TD5)>3) {
			return $this->TD5[3];
		}else{
			return "";
		}
	}
	public function getN4city()
	{
		return $this->n4->getN401();
	}
	public function getN4province()
	{
		return $this->n4->getN402();
	}
	public function getN4postal()
	{
		return $this->n4->getN403();
	}
	public function getN4country()
	{
		return $this->n4->getN404();
	}
}
?>