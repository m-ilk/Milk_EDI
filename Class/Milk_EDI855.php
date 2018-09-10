<?php
/**
*	michael lee production
*	Class EDI855
*/
require_once(dirname(__FILE__) . "/Milk_EDIfunctionl.php");
require_once(dirname(__FILE__) . "/Milk_EDI850.php");
require_once(dirname(__FILE__) . "/EDIObj.php");
require_once(dirname(__FILE__) . "/EDIsegment/ACK.php");
require_once(dirname(__FILE__) . "/EDIsegment/EDI855Seg.php");
require_once(dirname(__FILE__) . "/Item855.php");
class Milk_EDI855 extends EDIObj
{ 
	const AMAZON_RECEIVER 			= 'AMAZONDS';
	const AMAZON_855GSID_POSTFIX 	= '01';

	CONST DEFAULT_855_COLOUMN 		= '*'; 				//column seperator
	const DEFAULT_855_LINE	 		= '~'; 				//line seperator
	//common 
	private $vendor;
	private $Items;
	private $Enviroment;
	private $ponum;
	//ds
	//private $isa850;
	//private $gs850;
	private $obj850;
	private $body850;
	private $dbID;
	private $bak02;

	//po property
	private $isa; 										//isa obj
	private $gs; 										//gs obj
	private $st; 										//st obj
	private $bak;										//bak obj
	private $ctt;  										//ctt obj
	private $se; 										//se obj
	private $ge; 										//ge obj

	//control numbers
	private $isa_iea; 									//isa13 iea02
	private $gs_ge; 									//gs06 	ge02
	private $st_se; 									//st02 	se02
	public function __construct()
	{
	}
	/**
	*	set edi line and columen separator
	*/
	public function init($seperator = self::DEFAULT_855_COLOUMN,$line = self::DEFAULT_855_LINE)
	{
		parent::__construct($seperator,$line);
		$this->Enviroment 				= ISA::TEST15;
	}
	/**
	*	
	*/
	public function initPO()
	{
		$this->init();
	}
	/**
	*	control number
	*	@param isa_iea 					(string)isa 13
	*	@param gs_ge 					(string)gs 06
	*	@param st_se 					(string)st 02
	*	@param vendor 					(string)vendor code
	*/
	public function initControlNum($isa_iea,$gs_ge,$st_se,$vendor = null)
	{
		$this->isa_iea 					= $isa_iea;
		$this->gs_ge 					= $gs_ge;
		$this->st_se 					= $st_se;
		if (isset($vendor)) {
			$this->vendor 				= $vendor;
		}
	}
	public function getItems()
	{
		return $this->Items;
	}
	public function setItems($array)
	{
		if (isset($array)&&!empty($array)) {
			if (Milk_EDIfunctionl::AllElementsIsTypeOf($array,"Item855")) {
				$this->Items 			= $array;
			}
		}else{
			throw new Exception("setPO1_ACKarray input is not an array()");
			
		}
	}
	public function addItem(Item855 $item)
	{
		if (!isset($this->items)) {
			$this->items 				= array();
		}
		if (is_a($item, 'Item855')) {
			$this->items[]				= $item;
		}else{
			throw new Exception("additem's item is not a item 855 type");
		}
	}
	/*******************PO 856*******************/
	/**
	*	generate po 855's isa gs st & bak
	*	@param ponum 					(string)
	*	@param bak02 					(string)
	*	@return 						(void)
	*/
	public function GeneratePOHeader($ponum,$bak02 = BAK::BAK02_AC)
	{
		$this->isa 						= ISA::GenerateISA(Milk_EDIfunctionl::AddSpaceAtEndOfStringUntilSize($this->vendor,15),Milk_EDIfunctionl::AddSpaceAtEndOfStringUntilSize(ISA::ISA08_856,15),ISA::ISA12_855_PO,Milk_EDIfunctionl::NumString9Digit($this->isa_iea),ISA::ISA14_0,$this->Enviroment,ISA::ISA16_DEFAULT);
		$this->gs 						= GS::GenerateGS(GS::GSO1PR,$this->vendor,GS::GS03AMAZON,$this->gs_ge,GS::GS08_855);
		$this->st 						= ST::GenerateNewSTBy(ST::ST855,$this->st_se);
		$this->bak 						= BAK::GenerateBAK_PO($bak02,$ponum);
	}
	/**
	*	generate po 856's ctt se ge & iea
	*	@return 						(void)
	*/
	public function GeneratePOFooter()
	{
		$this->GeneratePoCTT();
		$this->GeneratePoSE();
		$this->GeneratePoGE();
		$this->GeneratePoIEA();
	}
	public function GeneratePoCTT()
	{
		if (empty($this->Items)) {
			throw new Exception("empty 855 items");
		}
		$ctt01 							= 0;
		$ctt02  						= 0;
		$ctt01 							= sizeof($this->Items);
		for ($i=0; $i < sizeof($this->Items) ; $i++) { 
			$one 						= $this->Items[$i];
			$ctt02 						+= $one->getPO1()->getPO102();
		}
		$this->ctt 						= CTT::GenerateCTT($ctt01,$ctt02);
	}
	public function GeneratePoSE()
	{
		$totalline 						= $this->NumOfLineFromTo("ST","SE");
		$se02 							= $this->st->getST02();
		$this->se 						= SE::GenerateSE($totalline,$se02);
	}
	public function GeneratePoGE()
	{
		$ge02 							= $this->gs->getGS06();
		$this->ge 						= GE::GenerateGE($ge02);
	}
	public function GeneratePoIEA()
	{
		$iea02 							= $this->isa->getISA13();
		$this->iea 						= IEA::GenerateIEA($iea02);
	}
	/**
	*	generate 856 to edi object's data array , and save as a file
	*	@param name 					(string) 856 file name 
	*	@param path 					(string) saved path
	*	@return path 					() arr2file return value
	*/
	public function ExportPO855ToFile($name,$path)
	{
		parent::addIntoEDIArray($this->SegmentToArray($this->isa));
		parent::addIntoEDIArray($this->SegmentToArray($this->gs));
		parent::addIntoEDIArray($this->SegmentToArray($this->st));
		parent::addIntoEDIArray($this->SegmentToArray($this->bak));
		for ($i=0; $i < sizeof($this->Items) ; $i++) { 
			$one 						= $this->Items[$i];
			$item 						= $one->POItemToArray();
			for ($j=0; $j < sizeof($item) ; $j++) {
				$temp 					= $item[$j];
				if (is_array($temp)) {
					for ($x=0; $x < sizeof($temp) ; $x++) { 
						parent::addIntoEDIArray($this->SegmentToArray($temp[$x]));
					}
				}elseif (is_subclass_of ($temp, 'Segment')) {
					parent::addIntoEDIArray($this->SegmentToArray($temp));
				}
				
			}
		}
		parent::addIntoEDIArray($this->SegmentToArray($this->ctt));
		parent::addIntoEDIArray($this->SegmentToArray($this->se));
		parent::addIntoEDIArray($this->SegmentToArray($this->ge));
		parent::addIntoEDIArray($this->SegmentToArray($this->iea));
		$path 							= $this-> arr2file($name,$path);
		return $path;
	}
	/***********************DS 855***********************/
	/**
	*	init drop ship 856
	*	@param obj850 					(EDI850)
	*	@param body850 					(Body)
	*	@param bak02 					(bak const)
	*	@param enviroment 				(string) ias 15
	*	@param ponum 					(string)
	*	@param vendor 					(string) vendor code
	*	@return 						(void)
	*/
	public function InitWithDSData ($obj850,$body850,$bak02,$Enviroment,$ponum,$vendor=""){
		$this->obj850 					= $obj850;
		$this->body850 					= $body850;
		$this->dbID 					= $body850->getPo_id();
		$this->bak02 					= $bak02;
		$this->Enviroment 				= $Enviroment;
		$this->ponum 					= $ponum;
		//check original 850 valid
		if (empty($vendor)) {
			$this->vendor 				= $this->obj850->getISA()->getISA08();
		}else{
			$this->vendor 				= $vendor;
		}

	}
	/**
	*	simple version of 855
	*	eitehr all accept or all soft reject
	*	@param ponum 					(string)
	*	@param obj850 					(edu850)
	*	@param bak02 					(bak const)
	*	@param environment 				(string) isa 15
	*	@param separator 				(string)
	*	@param line 					(string)
	*	@return 						(void)
	*/
	public static function GenerateDS855FileBy850Obj($ponum,$obj850,$bak02,$ack01,$Enviroment,$separator = null, $line = null)
	{
		if (is_a($obj850, 'Milk_EDI850')) {
			$obj855 			= new Milk_EDI855();
			if (!isset($separator)&&!isset($line)) {
				$obj855 		->init();
			}else{
				$obj855 		->init($separator,$line);
			}
			$body850 			= Milk_BODY::getBodyObjByPOnum($ponum);
			if (!$body850) {
				throw new Exception("Fail to retrieve body850 by ponum : $ponum");
			}

			$obj855 			->InitWithDSData($obj850,$body850,$bak02,$Enviroment,$ponum);
			$items850 			= $body850->getItemObjArray();
			$items855 			= array();
			if (sizeof($items850)>0) {
				for ($i=0; $i <sizeof($items850) ; $i++) { 
					$po1 		= $items850[$i]->getPO1();
					$item 		= Item855::GenerateItem855($po1,$ack01,$po1->getPO102());
					$items855[] = $item;
				}
			}else{
				throw new Exception("Purchase order item empty");
			}
			$obj855 			->setItems($items855);
			$saved_path 		= $body850->getBodyFolder();
			$obj855 			->Generate855DataBy850();
			$path 				= $obj855-> arr2file("855_".$ponum,$saved_path);
			//var_dump($path);
			if ($path) {
				$result = array();
				$result['path'] =$path;
				$result['gsid'] =$obj855->getGS06();
				return $result;
			}else{
				return $path;
			}
			return $result;
		}else{
			throw new Exception("invalid input paramter ".$obj850);
			return false;
		}
	}
	public function Generate855DataBy850()
	{
		$this->Generate855header();
		$this->Generate855Body();
		$this->Generate855Footer();
	}
	public  function Generate855header(){

		$isa = $this->ISA();
		parent::addIntoEDIArray($this->SegmentToArray($isa));
		$gs = $this->GS();
		parent::addIntoEDIArray($this->SegmentToArray($gs));
		$st = $this->ST();
		parent::addIntoEDIArray($this->SegmentToArray($st));
		$bak = $this->BAK();
		parent::addIntoEDIArray($this->SegmentToArray($bak));
		$this->body850->GenerateReceiver();

		$warehouse = $this->body850->getN1SF_code();
		$receiver = $this->body850->getReviver()->getName();
		$n1= $this->N1($warehouse,$warehouse);
		parent::addIntoEDIArray($this->SegmentToArray($n1));
			
		
	}
	public function Generate855Body()
	{
		for ($i=0; $i < sizeof($this->Items); $i++) {
			//echo gettype($this->Items[$i]); 
			$item = $this->Items[$i]->ObjToArray();
			for ($j=0; $j < sizeof($item) ; $j++) { 
				parent::addIntoEDIArray($this->SegmentToArray($item[$j]));
			}
		}
	}
	public function Generate855Footer($se02="")
	{
		$ctt= $this -> CTT();
		parent::addIntoEDIArray($this->SegmentToArray($ctt));
		$se = $this -> SE($se02);
		parent::addIntoEDIArray($this->SegmentToArray($se));
		$ge = $this -> GE();
		parent::addIntoEDIArray($this->SegmentToArray($ge));
		$iea = $this -> IEA();
		parent::addIntoEDIArray($this->SegmentToArray($iea));
	}
	
	public function ISA(){
		$isa 						=$this->obj850->getISA();
		if (!isset($isa)) {
			throw new Exception("invalid 850 isa" );
		}
		$newISA=$this->obj850->getISA()->GenerateNewISABy850ISA($this->obj850->getISA()->getISA06(),ISA::ISA12_855,$this->obj850->getISA()->getISA13(),ISA::ISA14_1,$this->Enviroment,">",ISA::DEFAULT11);
		return $newISA;
	}

	public function GS(){
		$gs 						=$this->obj850->getGS();
		if (!isset($gs)) {
			throw new Exception("invalid 850 GS");
		}
		$newGS = GS::GenerateGS(GS::GSO1PR,$this->obj850->getGS()->getGS03(),GS::GS03AMAZONDS,Milk_BODY::GetBodyIDBYPOnum($this->ponum).self::AMAZON_855GSID_POSTFIX,$this->dbID,GS::GS08_855);
		return $newGS;
	}
	public function getGS06()
	{
		return $this->GS()->getGS06();
	}
	public function ST(){
		$stObj = ST::GenerateNewSTBy(ST::ST855,$this->dbID);	
		return $stObj;
	}
	/*
		bak02 						:(const)BAK class const
	*/
	public function BAK(){	

		return BAK::GenerateBAK_DR($this->bak02,$this->ponum,$this->dbID);
	}
	/*
		return 						:(N1)
	*/
	public function N1($n102,$n104)
	{
		return N1::GenerateN1(N1::N101_SF,$n102,N1::N103_92,$n104);
	}
	/*
	[O]
		CTT01 						Number of Line Items
										his field will be the number of line the logical count of PO1 segments.	
		CTT02						Hash Total
										This field will be the sum of the value of quantities ordered (PO102) for each PO1 segment.
		return 						:(CTT)
	*/
	public function CTT()
	{
		$ctt01 = sizeof($this->Items);
		$ctt02 = 0;
		for ($i=0; $i < sizeof($this->Items) ; $i++) { 
			$po1_ack = $this->Items[$i];
			$ctt02+= $po1_ack ->getQuantityInPO1();
		}
		return CTT::GenerateCTT($ctt01,$ctt02);
	}
	/*
	[m]
		if CTT is not include, please set paramter to the last segment
	*/
	public function SE($se02 = "SE")
	{
		$totalline = $this->NumOfLineFromTo("ST",$se02);
		$se02 =$this->getValueByNameAndIndex("ST",2);
		return SE::GenerateSE($totalline,$se02);
	}
	/*
	[m]
		GE 01 is always 1 
	*/
	public function GE()
	{
		$ge02 = $this->getValueByNameAndIndex("GS",6);
		return GE::GenerateGE($ge02);
	}
	/*
	[m]
		iea 01 is always 1
	*/
	public function IEA()
	{
		$iea02 = $this->getValueByNameAndIndex("ISA",13);
		return IEA::GenerateIEA($iea02);
	}
}

?>