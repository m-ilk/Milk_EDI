<?php
/**
*	michael lee production
* 	Manage EDI 850 file from ST to SE, manage body table
*	each body is a order
*/
require_once(dirname(__FILE__) . "/EDIPart.php");
require_once(dirname(__FILE__) . "/EDIsegment/TD5.php");
require_once(dirname(__FILE__) . "/EDIsegment/EDI850Seg.php");
require_once(dirname(__FILE__) . "/EDIsegment/EDI856Seg.php");
class Milk_BODY extends EDIPart
{
	const AMAZON_BODY_TABLE 					= "amazon_order_edi_body";
	private $header_id;
	private $Receiver;								//Receiver Obj
	private $Items;									//Item Obj Array
	private $header_path;							//850 stored path
	private $query_result;							//query result after insert into body table.
	private $body_path;
													//used when inserting Orders table
	private $po_id;									//body table id;
	private $user_account;							//platform user code
	//PO segments
	private $dtms;
	private $po1s;
	private $n1;
	function __construct($array,$separator,$line)
	{
		parent::__construct($array,$separator,$line);
	}
	public function getUser_account(){
		return $this->user_account;
	}
	public function setPo_id($po_id)
	{
		$this->po_id = $po_id;
	}
	public function getPo_id()
	{
		return $this->po_id;
	}
	public function setHeader_id($value)
	{
		$this->header_id = $value;
	}
	/**
	*	get 850 file saved path
	*/
	public function getHeader_path()
	{
		return $this->header_path;
	}
	public function setHeader_path($path)
	{
		$this->header_path = $path;
	}
	public function setquery_result($query)
	{
		$this->query_result = $query;
	}
	public function getquery_result()
	{
		return $this->query_result;
	}
	public function getDtms()
	{
		return $this->dtms;
	}
	public function getPO1S()
	{
		return $this->po1s;
	}
	public function getN1()
	{
		return $this->n1;
	}
	public function getReviver()
	{
		return $this->Receiver;
	}
	public function initDSBody()
	{
		$this->GenerateReceiver();
	}
	public function getBodyPath()
	{
		return $this->body_path;
	}
	public function getBodyFolder()
	{
		$array 			= explode('/', $this->body_path);
		$size 			= sizeof($array);
		$last 			= $array[$size-1];
		$length 		= strlen($last);
		$string 		= substr($this->body_path, 0, strlen($this->body_path)-$length);
		return $string;
	}
	/**
	*	init PO order 
	*	@return true;
	*/
	public function initPOBody()
	{
		$this->dtms 	= $this->generateDtm();
		$this->po1s 	= $this->generatePO1s();
		$this->n1 		= $this->generateN1();
		return true;
	}
	/**
	*	find and generate po1s objects from raw data
	*	@return po1s 						(po1 array)
	*/
	public function generatePO1s()
	{
		$body 			= parent::getPart();
		$po1s 			= array();
		for ($i=0; $i < sizeof($body); $i++) { 
			if (PO1::IsPO1array($body[$i])) {
				$temp 	= new PO1($body[$i]);
				$po1s[] = $temp;
			}
		}
		return $po1s;
	}
	/**
	*	find and generate dtm objects from raw data
	*	@return dtm 						(dtm array)
	*/
	public function generateDtm()
	{
		$body 			= parent::getPart();
		$dtms 			= array();
		for ($i=0; $i <sizeof($body) ; $i++) { 
			if (DTM::IsDTMarray($body[$i])) {
				$temp 	= new DTM($body[$i]);
				$dtms[] = $temp;
			}
		}
		return $dtms;
	}
	/**
	*	find and generate n1 objects from raw data
	*	@return n1 	 						(n1 array)
	*/
	public function generateN1()
	{
		$body 			= parent::getPart();
		$N1 			= null;
		for ($i=0; $i <sizeof($body) ; $i++) { 
			if (N1::IsN1_STarray($body[$i])) {
				$temp 	= new N1($body[$i]);
				$N1 	= $temp;
			}
		}
		return $N1;
	}
	/**
	*	generate the total reven from a order
	*	@return total 						(float)
	*/
	public function getOrderTotalRevenue()
	{
		$total 				= 0.0;
		$items 				= $this->getItemObjArray();
		foreach ($items as $item) {
			$quantity 		= $item->getQuantity();
			$unityprice 	= $item->getUnitPrice();
			$totalprice 	= intval($quantity)  * floatval($unityprice);
			$total 			+= $totalprice;
		}
		return $total;
	}
	/**
	*	generate Receiver object from this body object
	*	@return 							(RECEIVER)
	*/
	public function GenerateReceiver()
	{	
		$array 				=array();
		$body 				=parent::getPart();
		$flag 				= false;
		for ($i=0; $i <sizeof($body) ; $i++) { 
			if (N1::IsN1_STarray($body[$i])) {
				$array[] 	= $body[$i];
				$flag 		= true;
			}else if (TD5::IsTD5array($body[$i])) {
				$array[] 	= $body[$i];
				$flag 		= false;
				break;
			}else if ($flag) {
				$array[] 	= $body[$i];
			}
		}
		$this->Receiver = new RECEIVER($array,parent::getSeparator(),parent::getLine());
	}
	/**
	*	insert a body object into database
	*	insert edi log
	*	move edi file to corresponding folder
	*	insert items objects into database
	*	@param tran 							(transaction)
	*	@param header_id 						(string) header table id
	*	@param user_account 					(string) 
	*	@param filename 						(string) original 850 file path
	*	@return  exception|boolean
	*/
	public function InsertBodyDB(&$Tran,$header_id,$user_account,$filename)
	{
		if (self::CheckDuplicatePOBody($this->getBEG()->getBEG03())) {
			throw new Exception("This purchase order is already exist.po number:".$this->getBEG()->getBEG03());
		}
		$query ="INSERT INTO ".self::AMAZON_BODY_TABLE."( 
			header_id,
			st_trans_set_id,
			st_trans_set_num,
			";
		$query.="beg_trans_set_id,
			beg_po_type,
			po_number,
			beg_po_date,";
		$query.="
			ctt_hash,";
		$query.="
			n1_ST_name,
			n1_ST_n3,
			n1_ST_n4_city,
			n1_ST_n4_province,
			n1_ST_n4_postal,
			n1_ST_n4_country,
			td5_shippment_method,";
		$query.="
			n1_SF_code,
			";
		$query.="
			se_segment,
			se_trans_set_control,
			total_revenue,
			user_account";
		$query.=") VALUES (";
		$query.="'$header_id',";
		$query.= $this->getST()->QueryValuesString().",";
		$query.= $this->getBEG()->QueryValueStringWithOutIndex(4).",";
		$query.= $this->getCTT()->getCTT02().",";
		$query.= "'".Milk_EDI::sql_string($this->getReviver()->getName())."','".Milk_EDI::sql_string($this->getReviver()->getN3Address()) ;
		$query.="','".$this->getReviver()->getN4city()."','".$this->getReviver()->getN4province()."','".$this->getReviver()->getN4postal()."','".$this->getReviver()->getN4country()."','";
		$query.= $this->getReviver()->getTD5_method()."',";
		$query.= "'".$this->getN1SF_code()."',";
		$query.= $this->getSE()->QueryValuesString().",'";
		$query.= $this->getOrderTotalRevenue()."','";
		$query.= $user_account."'";
		$query.=")";		
		$result 				= self::queryInsertWithTran($query,$Tran);
		if ($result) {
			//insert edi log
			$date 				= Milk_EDIfunctionl::yymmdd();
			$po_number 			= $this->getPONum();
			$log_flag 			= EDILog::InsertEDILog($Tran,$user_account,$po_number,EDILog::ORDER_TYPE_DS);
			$insert_id 			= $Tran ->insert_id;
			if (!Milk_EDIfunctionl::CreateDirectory($date)||!Milk_EDIfunctionl::CreateDirectory($date."/".$insert_id)) {
				throw new Exception("Fail to Create Directory ".$date."/".$insert_id);
			}
			//update and move edi 850 file saved path
			$newpath 			= Milk_EDIConfig::SAVED_PATH.$date."/".$insert_id.'/850';
			$move_flag 			= Milk_EDIfunctionl::MoveToTargetFolder($filename,$newpath);
			if (!$move_flag) {
				throw new Exception("Fail to copy original 850 into Files folder $filename");	
			}
			$this->header_path 	= Milk_EDIConfig::SAVED_PATH.$date."/".$insert_id."/";
			//update and move edi 850 body file to saved path
			$path 				= $this->header_path.$this->getPONum();
			$save_flag 			= $this->SavePOFileInPath($path);
			if ($save_flag) {
				$update_flag 	= $this->UpdatePath($Tran,$path);
				if (!$update_flag) {
					throw new Exception("fail to update po path, po: ".$this->getPONum());
				}
			}else{
				throw new Exception("fail to save po in target directory: ".$path);
			}
			$flag 				= $this->InsertDetailDB($Tran,$header_id,$user_account);
			if (!$flag) {
				return $flag;
			}else{
				return true; 
			}
		}else{
			throw new Exception("Fail to insert 850 body");
		}
		return $result;
	}
	/**
	*	@return 										(ST)
	*/
	public function getST()
	{
		$STarray = $this->getFirstArrayByIndex0("ST");
		if (isset($STarray)) {
			return new ST($STarray);
		}else{
			return new ST(array());
		}
	}
	/**
	*	@return 										(BEG|array)
	*/
	public function getBEG(){
		$BEGarray = $this->getFirstArrayByIndex0("BEG");
		if (isset($BEGarray)) {
			return new BEG($BEGarray);
		}else{
			return array();
		}
	}
	/**
	*	@return 										(CTT|array)
	*/
	public function getCTT()
	{
		$BEGarray = $this->getFirstArrayByIndex0("CTT");
		if (isset($BEGarray)) {
			return new CTT($BEGarray);
		}else{
			return array();
		}
	}
	/**
	*	@return 										(SE|array)
	*/
	public function getSE()
	{
		$SEGarray = $this->getFirstArrayByIndex0("SE");
		if (isset($SEGarray)) {
			return new SE($SEGarray);
		}else{
			return array();
		}
	}
	/**
	*	get n1 ship form code
	*	@return 										(string)
	*/
	public function getN1SF_code()
	{
		$n1array;
		$body 					= parent::getPart();
		$flag 					= false;
		for ($i=0; $i <sizeof($body) ; $i++) { 
			if (N1::IsN1_SFarray($body[$i])) {
				$n1array 		= $body[$i];
			}
		}
		if ($n1array) {
			return Milk_EDIfunctionl::getValueOfIndex($n1array,2);
		}else{
			return "";
		}
	}
	/**
	*	get BEG 03 .Purchase order number
	*	@return string
	*/
	public function getPONum(){
		//TO-DO optimize 
		$BEG 					= $this->getBEG();
		if (isset($BEG)) {
			return $BEG->getBEG03();
		}else{
			return "";
		}
	}
	/**
	*	insert this order's items into DB
	*	@param tran 						(transaction)
	*	@param header_id 					(string) insert id
	*	@param user_account 				(string)
	*	@return boolean | exception
	*/
	private function InsertDetailDB(&$Tran,$header_id,$user_account)
	{
		$array 					= $this->getItemObjArray();
		$flag 					= true;
		if (isset($array)) {
			for ($i=0; $i <sizeof($array) ; $i++) { 
				$po1loop 		= $array[$i];
				if ($flag) {
					$flag 		= $po1loop ->InsertIntoDetail($Tran,$header_id,$this->getPONum(),parent::getSeparator(),parent::getLine(),$user_account);
				}
			}
			return $flag;
		}else{
			throw new Exception("Empty PO1 section");
		}
	}
	/**
	*	conver this body to text and store in path location 
	*	@param path 						(string) store location
	*	@return 							(boolean)
	*/
	public function SavePOFileInPath($path)
	{
		$txt 					= $this->DataToTxt();
		$PO 					= fopen($path, "w");
		if ($PO) {
			$flag 				= fwrite($PO, $txt);
			fclose($PO);
			if ($flag) {
				return true;
			}else{
				EDIErrorLog::ErrorLog(EDIErrorLog::ERRORLOG_FAIL2MOVE,$path,'Fail to save Purchase order file in Files folder');
				return false;
			}
		}else{
			return false;
		}
	}
	/**
	* 	update this order body DB saved path
	*	@param tran 						(transaction)
	*	@param path 						(string) 
	*/
	public function UpdatePath(&$Tran,$path)
	{
		$query 					= "UPDATE ".self::AMAZON_BODY_TABLE." SET saved_path = '$path' WHERE po_number ='".$this->getPONum()."'";
		$result 				= self::queryInsertWithTran($query,$Tran);
		if ($result) {
			return true;
		}else{
			return false;
		}
	}
	/**
	*	generate items object array by itemsarray
	*	@return result 						(ITEM array)
	*/
	public function getItemObjArray(){
		$array 					= $this->getItemArray();
		$result 				= array();
		for ($i=0; $i <sizeof($array) ; $i++) { 
			$result[] 			= new ITEM($array[$i],parent::getSeparator(),parent::getLine());
		}
		return $result;
	}
	/**
	*	
	*/
	public function getItemArray(){
		$array = array();
		$one;
		$data = parent::getPart();
		$flag = false;
		for ($i=0; $i <sizeof($data) ; $i++) { 
			$one;
			$header = $data[$i][0];
			if (strlen($header)>3) {
				$header = substr($header, 0,3);
			}
			if ($header == ITEM::ITEM_START_SEGMENT) {
				if (!$flag) {
					$one = array();
					$one[] = $data[$i];
					$flag = true;
				}else{
					$array[]=$one;
					$one = array();
					$one[] = $data[$i];
				}
				
			}elseif ($header == ITEM::ITEM_END_SEGMENT) {
				$one[] = $data[$i];
				$array[] = $one;
				$flag = false;
			}elseif ($flag) {
				$one[] = $data[$i];
			}
		}
		return $array;
	}
	public function initWithDBdata($data){
		$this->initDSBody();
		$this->po_id 			= $data['id'];
		$this->user_account 	= $data['user_account'];
		$this->body_path 		= $data['saved_path'];
		$this->header_path 		= Milk_EDI850::Get850FilePathByPOnum($data['po_number']);
	}
	public static function getBodyObjByPOnum($ponum)
	{
		$query = "SELECT * FROM ".self::AMAZON_BODY_TABLE." WHERE po_number = '$ponum'";
		$result = self::querySelect($query);
		if ($result) {
			$path = $result[0]['saved_path'];
			if ($path[0]=='/') {
				$array = EDIObj::file2arr($path,Milk_EDI::AMAZON_DS_COLUMN,Milk_EDI::AMAZON_DS_LINE);
			}else{
				$array = EDIObj::file2arr(Milk_EDIConfig::EDI_PATH.'/'.$path,Milk_EDI::AMAZON_DS_COLUMN,Milk_EDI::AMAZON_DS_LINE);
			}
			if ($array) {
				$body = new Milk_BODY($array,Milk_EDI::AMAZON_DS_COLUMN,Milk_EDI::AMAZON_DS_LINE);
				//TO-DO id or po_id
				$body->initWithDBdata($result[0]);
				
				return $body;
			}else{
				return null;
			}
		}else{
			return false;
		}
	}
	
	public static function getBodyIDByPOnum($ponum)
	{
		$query = "SELECT * FROM ".self::AMAZON_BODY_TABLE." WHERE po_number = '$ponum'";
		$result = self::querySelect($query);
		if ($result) {
			return $result[0]['id'];
		}else{
			return false;
		}
	}
	/*
		retrun saved_path for ponum with ponum at the end of string
		return false if po does not exist
	*/
	public static function getBodySavedPathByPOnum($ponum)
	{
		$query = "SELECT * FROM ".self::AMAZON_BODY_TABLE." WHERE po_number = '$ponum'";
		$result = self::querySelect($query);
		if ($result) {
			return $result[0]['saved_path'];
		}else{
			return false;
		}
	}
	/*
		True if the po number is already insert into DB
		False if the po number is not insert into DB
	*/
	public static function CheckDuplicatePOBody($ponum)
	{
		$query = "SELECT * FROM ".self::AMAZON_BODY_TABLE." WHERE po_number = '$ponum'";
		$result = self::checkExist($query);
		return $result;
	}
}
?>