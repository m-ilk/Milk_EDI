<?php
/**
*	michael lee production
*	class EDI856
*/
require_once(dirname(__FILE__) . "/EDIsegment/PO1.php");
require_once(dirname(__FILE__) . "/EDIsegment/ISA.php");
require_once(dirname(__FILE__) . "/EDIsegment/GS.php");
require_once(dirname(__FILE__) . "/EDIsegment/N1.php");
require_once(dirname(__FILE__) . "/EDIsegment/CTT.php");
require_once(dirname(__FILE__) . "/EDIsegment/REF.php");
require_once(dirname(__FILE__) . "/EDIsegment/Segment.php");
require_once(dirname(__FILE__) . "/EDIsegment/EDI856Seg.php");
require_once(dirname(__FILE__) . "/Mlik_EDI856Parts.php");
/**
* man02 		:ship_order tracking number
*/
class Milk_EDI856 extends EDIobj
{
	const AMAZON_856GSID_POSTFIX 	= '02';

	private $body850;
	private $obj850;
	private $seperator;
	private $line;
	private $HLid;
	private $ItemPackage;
	private $Shipment;
	private $HL_S_ID;
	private $HL_O_ID;
	private $HL_P_ID;
	private $LIN_ID;
	private $store_code;
	
	//po edi file
	private $isa;
	private $gs;
	private $st;
	private $bsn;
	private $hl_s_section;
	private $hl_o_section;
	private $hl_p_i_section;
	private $ctt;
	private $se;
	private $ge;
	private $iea;
	public function __construct()
	{
		
	}
	/**
	*	init a drop ship 856 object
	*	@return 					(void)
	*/
	public function InitDS856($body850,$obj850,$Shipment,$seperator,$line)
	{
		$this->body850 				= $body850;
		$this->obj850 				= $obj850;
		$this->Shipment 			= $Shipment;
		$this->HLid 				= 1;
		$this->LIN_ID 				= 1;
		parent::__construct($seperator,$line);
	}
	/**
	*	init a purchase order 856 object
	*	@return 					(void)
	*/
	public function initPO856($Shipment,$store_code,$seperator,$line)
	{
		$this->Shipment 			= $Shipment;
		$this->store_code 			= $store_code;
		$this->HLid 				= 1;
		$this->LIN_ID 				= 1;
		parent::__construct($seperator,$line);
	}
	/**
	*	generate po 856's isa,gs,st bsn segment
	*	@return 					(void)
	*/
	public function Generate856Header_PO($gs06,$st02,$isa15 =ISA::DEFAULT15)
	{
		$this->isa  				= ISA::GenerateISA(Milk_EDIfunctionl::AddSpaceAtEndOfStringUntilSize($this->store_code,15),Milk_EDIfunctionl::AddSpaceAtEndOfStringUntilSize(ISA::ISA08_856,15),ISA::ISA12_856_PO,Milk_EDIfunctionl::NumString9Digit($this->Shipment->shipmentID),ISA::ISA14_1,$isa15,ISA::ISA16_DEFAULT,ISA::ISA11_856);
		$this->gs 					= GS::GenerateGS(GS::GS01SH,$this->store_code,GS::GS03AMAZON,$gs06,GS::GS08_856);
		$this->st 					= ST::GenerateNewSTBy(ST::ST856,$st02);
		$this->bsn 					= BSN::GenerateBSN_PO($this->Shipment->shipmentID);
	}
	public function Generate856Body_PO()
	{
		$this->GenerateHL_S_PO();
		$this->GenerateHL_O_PO();
		$this->GenerateHL_P_I_PO();
	}
	/**
	*	generate HL Shipment section
	*	only allow using tracking number
	*	seal number , amazon reference number & bill of lading are not implement
	*	@return 					(void)
	*/
	public function GenerateHL_S_PO()
	{
		$array 						= array();
		$hl 						= HL::GenerateHL_S($this->HLid);
		$this->HL_S_ID 				= $this->HLid;
		$array[]  					= $hl;
		$this->HLid++;
		$td1 						= TD1::GenerateTD1_PO(TD1::TD101_CTN,$this->Shipment->totalCartoon,$this->Shipment->totalWeightLB);
		$array[] 					= $td1;
		//pallet
		if ($this->Shipment->type==Shipment::TYPE_PALLET) {
			$td1_plt 				= TD1::GenerateTD1_plt($this->Shipment->lading_qty);
			$array[] 				= $td1_plt;
		}
		$td5 						= TD5::GenerateTD5($this->Shipment->method,TD5::TD502_02);
		$array[] 					= $td5;
		$ref 						= REF::GenerateREF(REF::REF01_CN,$this->Shipment->tacking_number);
		$array[] 					= $ref;
		$dtm011 					= DTM::GenerateDTM_Shipped($this->Shipment->shippedDate,$this->Shipment->shippedTime,DTM::DTM04_GM);
		$array[] 					= $dtm011;
		$n1SF 						= N1::GenerateN1(N1::N101_SF,'',N1::N103_92,$this->Shipment->SFVendor);
		$array[] 					= $n1SF;
		$n4SF 						= N4::GenerateN4($this->Shipment->SFCity,$this->Shipment->SFProvince,$this->Shipment->SFPostal,$this->Shipment->SFCountry);
		$array[] 					= $n4SF;
		$n1ST 						= N1::GenerateN1(N1::N101_ST,'',N1::N103_92,$this->Shipment->STCode);
		$array[]  					= $n1ST;
		$this->hl_s_section 		= $array;
	}
	/**
	*	generate po 856 Hl order section
	*	PO order
	*	@return 					(void)
	*/
	public function GenerateHL_O_PO(){
		$array 						= array();
		$hl 						= HL::GenerateHL_O($this->HLid,$this->HL_S_ID);
		$this->HL_O_ID 				= $this->HLid; 
		$this->HLid++;
		$array[]					= $hl;
		$ponum 						= $this->Shipment->POnum;
		if (!$ponum) {
			throw new Exception("Fail to find po numebr by orderCode : $this->orderCode");
		}
		$prf 						= PRF::GeneratePRF_PO856($ponum);
		$array[] 					= $prf;
		$this->hl_o_section 		= $array;
	}
	/**
	*	generate po 856 package and item segment
	*	@return 					(void)
	*/
	public function GenerateHL_P_I_PO(){
		$array 						= array();
		$Packages 					= $this->Shipment->Packages;
		
		for ($i=0; $i <sizeof($Packages) ; $i++) { 
			$pack 					= $Packages[$i];
			$arrayP 				= $pack->GenerateHL_P_PO($this->HL_O_ID,$this->HLid,$this->Shipment->tacking_number);
			$this->HL_P_ID 			= $this->HLid;
			$this->HLid++;
			for ($j=0; $j <sizeof($arrayP) ; $j++) { 
				$array[] 			= $arrayP[$j];
			}
			$items 					= $pack->items;
			for ($j=0; $j <sizeof($items) ; $j++) { 
				$item 				= $items[$j];
				$arrayI 			= $item->GenerateItem_PO($this->HLid,$this->HL_P_ID);
				for ($x=0; $x <sizeof($arrayI) ; $x++) { 
					$array[] 		= $arrayI[$x];
				}
			}
		}
		$this->hl_p_i_section 		= $array;
		
	}
	/**
	*	generate po 856's ctt se ge and iea segment
	*	@return 					(void)
	*/
	public function Generate856Footer_PO()
	{
		$this->ctt 					= CTT::GenerateCTT($this->HLid-1,$this->Shipment->getTotalQuantityShipped());
		$this->GenerateExist846ToEDIObj(); 
		$this->se 					= SE::GenerateSE($this->getDataSize(),$this->st->getST02());
		$this->ge 					= GE::GenerateGE($this->gs->getGS06());
		$this->iea 					= IEA::GenerateIEA($this->isa->getISA13());
	}
	/**
	*	Generate EDI Object data array
	*/
	public function GenerateExist846ToEDIObj()
	{
		parent::clearData();
		if (isset($this->isa)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->isa));
		}else{
			return ;
		}
		if (isset($this->gs)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->gs));
		}else{
			return ;
		}
		if (isset($this->st)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->st));
		}else{
			return ;
		}
		if (isset($this->hl_s_section)) {
			for ($i=0; $i <sizeof($this->hl_s_section) ; $i++) { 
				parent::addIntoEDIArray($this->SegmentToArray($this->hl_s_section[$i]));
			}
		}else{
			return ;
		}
		if (isset($this->hl_o_section)) {
			for ($i=0; $i <sizeof($this->hl_o_section) ; $i++) { 
				parent::addIntoEDIArray($this->SegmentToArray($this->hl_o_section[$i]));
			}
		}else{
			return;
		}
		if (isset($this->hl_p_i_section)) {
			for ($i=0; $i <sizeof($this->hl_p_i_section) ; $i++) { 
				parent::addIntoEDIArray($this->SegmentToArray($this->hl_p_i_section[$i]));
			}
		}else{
			return ;
		}
		if (isset($this->ctt)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->ctt));
		}else{
			return ;
		}
		if (isset($this->se)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->se));
		}else{
			return ;
		}
		if (isset($this->ge)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->ge));
		}else{
			return ;
		}
		if (isset($this->iea)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->iea));
		}else{
			return ;
		}
	}
	/*************856 DS*************/
	/**
	*	Generate DS 856
	*	header is generate based on original 850 file
	*	@param isa15 							(string) 
	*	@param store_code 						(string)
	*/
	public function Generate856_DS($isa15,$store_code)
	{

		$this->Generate856header_DS($isa15,$store_code);
		$this->Generate856Body_DS();
		$this->GenerateFooter_DS();
	}
	/**
	*	generate isa gs st bsn segment 
	*	@param see Generate856_DS()
	*/
	public function Generate856header_DS($isa15,$store_code){
		$isa 						= $this->ISA($isa15,$store_code);
		$gs 						= $this->GS($store_code);
		$st 						= $this->ST();
		$bsn 						= $this->BSN();
		parent::addIntoEDIArray($this->SegmentToArray($isa));
		parent::addIntoEDIArray($this->SegmentToArray($gs));
		parent::addIntoEDIArray($this->SegmentToArray($st));
		parent::addIntoEDIArray($this->SegmentToArray($bsn));
	}
	/**
	*	Generate ds HL sections
	*	@return 					(void)
	*/
	public function Generate856Body_DS()
	{
		$HL_O 						= $this->GenerateHL_O_DS();
		for ($i=0; $i <sizeof($HL_O) ; $i++) {
		 	parent::addIntoEDIArray($this->SegmentToArray($HL_O[$i])); 
		}
		$HL_I 						= $this->GenerateHL_I_DS();
		for ($i=0; $i <sizeof($HL_I) ; $i++) {
		 	parent::addIntoEDIArray($this->SegmentToArray($HL_I[$i])); 
		}
	}
	/**
	*	Generate HL order section
	*	@return array 				(array) array of hl order section segments
	*/
	public function GenerateHL_O_DS(){
		$array 						= array();
		$hl 						= HL::GenerateHL_O($this->HLid); 
		$this->HLid++;
		$array[] 					= $hl;
		$prf06 						= EDIWithERP::getPrf06ByShipment($this->Shipment);
		$prf 						= PRF::GeneratePRF_856($this->body850->getPONum(),$prf06);
		$array[] 					= $prf;
		$n1SF 						= N1::GenerateN1(N1::N101_SF,$this->body850->getN1SF_code(),N1::N103_92,$this->body850->getN1SF_code());
		$array[] 					= $n1SF;
		return $array;
	}	
	/**
	*	used for DS 850
	*	get o level based on 850
	*	only 1 P level from shipment
	*	@return $array 						(array) array of HL item sections  
	*/
	public function GenerateHL_I_DS()
	{	
		$array 							= array();
		$packages 						= $this->Shipment->Packages;
		$man02 							= '1';
		$items 							= $this->body850->getItemObjArray();
		for ($i=0; $i <sizeof($items) ; $i++) { 
			$one 						= $items[$i];
			$HL_I 						= HL:: GenerateHL_I($this->HLid);
			$this->HLid++;
			$array[] 					= $HL_I;
			$LIN 						= LIN::Generate856LIN($one->getPO1()->getPO101(),$one->getSkU());
			$array[] 					= $LIN;
			$SN1 						= SN1::GenerateSN1($one->getPO1()->getPO101(),$one->getQuantity(),$one->getQuantity(),SN1::SN108_IA);
			$array[] 					= $SN1;
			$man 						= MAN::GenerateMAN_HL_I($man02);
			$array[] 					= $man;
		}
		//package
		$pack = $packages[0];
		$hl_P = HL::GenerateHL_P($this->HLid);
		$this->HLid++;
		$array[]=$hl_P;
		$td1 = TD1::GenerateTD1_X12($this->Shipment->totalWeightLB);
		$array[]=$td1;
		$td5 = TD5::GenerateTD5($this->body850->getReviver()->getTD5_method());
		$array[] =$td5;
		//TO DO chekc if man is nesscessary
		$man = MAN::GenerateMAN_HL_P($pack->package_id,MAN::MAN04_CP,$this->Shipment->tacking_number);
		$array[] = $man;
		$date 							= $this->Shipment->shippedDate;
		$time = $this->Shipment->shippedTime;
		$dtm = DTM::GenerateDTM_X12(DTM::DTM01_ZZZ,$date);
		$array[] = $dtm;
		return $array;
	}
	/**
	*	generate ctt se ge iea section
	*	@return  							(void)
	*/
	public function GenerateFooter_DS()
	{
		$ctt 							= $this->CTT();
		$se 							= $this->SE();
		$ge 							= $this->GE();
		$IEA 							= $this->IEA();
		parent::addIntoEDIArray($this->SegmentToArray($ctt));
		parent::addIntoEDIArray($this->SegmentToArray($se));
		parent::addIntoEDIArray($this->SegmentToArray($ge));
		parent::addIntoEDIArray($this->SegmentToArray($IEA));
	}
	/**
	*	generate isa segment for ds 856
	* 	@param isa15 						(string)
	*	@param store_code 					(string) 
	*	@return isa 						(ISA Object)
	*	return ISA obj
	*/ 
	private function ISA($isa15,$store_code){
		$Obj850ISA 						= $this->obj850->getISA();
		$isa 							= ISA::GenerateISA(Milk_EDIfunctionl::AddSpaceAtEndOfStringUntilSize($store_code,15),Milk_EDIfunctionl::AddSpaceAtEndOfStringUntilSize(ISA::DEFAULT08,15),ISA::ISA12_856,Milk_EDIfunctionl::NumString9Digit($this->Shipment->shipmentID),ISA::ISA14_1,$isa15,">",ISA::DEFAULT11);
		return $isa;
	}
	/**
	*	generate gs segment for ds 856
	*	@param store_code 					(string)
	*	@return GS obj
	*/
	private function GS($store_code){
		$Obj850GS  						= $this->obj850->getGS();
		$gs 							= GS::GenerateGS(GS::GS01SH,$store_code,GS::GS03AMAZONDS,Milk_BODY::GetBodyIDBYPOnum($this->body850->getPONum()).self::AMAZON_856GSID_POSTFIX,GS::GS08_855);
		return $gs;
	}
	/**
	*	generate st segement for ds 856
	*	@return 							(ST obj)
	*/
	private function ST(){
		$st 						= ST::GenerateNewSTBy(ST::ST856,Milk_BODY::getBodyIDByPOnum($this->Shipment->POnum));
		return $st;
	}
	/**
	*	generate BSN segement for ds 856
	*	@return 							(bsn obj)
	*/
	private function BSN(){
		$bsn 						= BSN::GenerateBSN($this->Shipment->shipmentID);
		return $bsn;
	}
	/**
	*	generate CTT segement for ds 856
	*	@return  							(CTT obj)
	*/
	private function CTT()
	{
		$totalQuantity 				= $this->Shipment->getTotalQuantityShipped();
		return CTT::GenerateCTT($this->HLid-1,$totalQuantity);
	}
	/**
	* 	generate SE segement for ds 856
	*	@return 							(se obj)
	*/
	private function SE(){
		$totalline 					= $this->NumOfLineFromTo("ST","SE");
		$se02 						= $this->getValueByNameAndIndex("ST",2);
		return SE::GenerateSE($totalline,$se02);
	}
	/**
	*	generate GE segement for ds 856
	*	@return  							(GE obj)
	*/
	public function GE()
	{
		$ge02 						= $this->getValueByNameAndIndex("GS",6);
		return GE::GenerateGE($ge02);
	}
	/**
	*	generate IEA segement for ds 856
	*	@return 							(IEA obj)
	*/
	public function IEA()
	{
		$iea02 = $this->getValueByNameAndIndex("ISA",13);
		return IEA::GenerateIEA($iea02);
	}
	/**
	*	Used for update edi log
	*	@return 							(string)
	*/
	public function getGS06()
	{
		return $this->GS()->getGS06();
	}
}
?>