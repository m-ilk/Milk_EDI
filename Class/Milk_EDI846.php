<?php
/**
* 	michael lee production
*	EDI 846 | product
*/
require_once(dirname(__FILE__) . "/EDIProduct.php");
require_once(dirname(__FILE__) . "/EDIObj.php");
require_once(dirname(__FILE__) . "/Milk_EDIConfig.php");
require_once(dirname(__FILE__) . "/EDIsegment/BIA.php");
require_once(dirname(__FILE__) . "/EDIsegment/LIN.php");
require_once(dirname(__FILE__) . "/EDIsegment/SDQ.php");
require_once(dirname(__FILE__) . "/EDIsegment/DTM.php");
require_once(Milk_EDIConfig::MilkObject_path);
class Milk_EDI846 extends EDIObj
{
	
	CONST CLASS_NAME 					= 'Milk_EDI846';
	CONST DEFAULT_INIT_FUNC 			= 'initWithDBData';
	
	CONST DEFAULT_COLUMN_SEPARATOR 		= '^';
	CONST DEAFULT_LINE_SEPARATOR 		= '~';
	//846
	private $vendor;
	private $enviroment;
	private $isa;
	private $gs;
	private $st;
	private $bia;
	private $dtm;
	private $n1;
	private $ctt;
	private $se;
	private $ge;
	private $iea;

	private $details; 					//array
	function __construct()
	{
		
	}
	
	public function setDetails($details)
	{
		$this->details  				= $details;
		$this->GenerateCTT();
	}
	public function getDetails()
	{
		return $this->details;
	}
	/**
	*	init this 846 object
	* 	@param seperator 
	*	@param line
	*	@return void
	*/
	public function init($seperator=self::DEFAULT_COLUMN_SEPARATOR,$line=self::DEAFULT_LINE_SEPARATOR)
	{
		parent::__construct($seperator,$line);
		$this->enviroment 				= ISA::TEST15;
	}
	/**
	*	edi 846 file init header
	*	@param venrdor 					(string)
	*	@return void
	*/
	public function initHeader($vendor)
	{
		$this->vendor 					= $vendor;
	}
	/**
	*	generate all segments before products details
	*	@param control_num 							(string)
	*	@param gs_control 							(string)
	*	@param st_control 							(string)
	*	@param logID 								(string)
	*	@param warehouse 							(string)
	*	@param bia01 								(string)
	*	@return void
	*/
	public function Generate846Header($control_num,$gs_control,$st_control,$logID,$warehouse,$bia01 = BIA::BIA01_00)
	{
		$this->isa 						= ISA::GenerateISA(Milk_EDIfunctionl::AddSpaceAtEndOfStringUntilSize($this->vendor,15),Milk_EDIfunctionl::AddSpaceAtEndOfStringUntilSize(ISA::DEFAULT08,15),ISA::ISA12_846,Milk_EDIfunctionl::NumString9Digit($control_num),ISA::ISA14_1,$this->enviroment,ISA::ISA16_846);
		$this->gs 						= GS::GenerateGS(GS::GS01IB,$this->vendor,GS::GS03AMAZONDS,$gs_control,GS::GS08_855);
		$this->st 						= ST::GenerateNewSTBy(ST::ST846,$st_control);
		$this->bia 						= BIA::GenerateBIA($bia01,$logID);
		$this->dtm 						= DTM::Generate846DTM();
		$this->warehouse 				= $warehouse;
		$this->n1 						= N1::Generate846N1($warehouse);
	}
	public function GenerateExist846ToEDIObj()
	{
		parent::clearData();
		if (isset($this->isa)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->isa));
		}else{
			return;
		}
		if (isset($this->gs)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->gs));
		}else{
			return;
		}
		if (isset($this->st)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->st));
		}else{
			return;
		}
		if (isset($this->bia)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->bia));
		}else{
			return;
		}
		if (isset($this->dtm)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->dtm));
		}else{
			return;
		}
		if (isset($this->n1)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->n1));
		}else{
			return;
		}
		if (isset($this->details)) {
			for ($i=0; $i <sizeof($this->details) ; $i++) { 
				$one 						= $this->details[$i];
				$itemSegs 					= $one->Convert846Item2Array();
				for ($j=0; $j < sizeof($itemSegs) ; $j++) {
					parent::addIntoEDIArray($this->SegmentToArray($itemSegs[$j]));
				}
			}
		}else{
			return;
		}
		if (isset($this->ctt)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->ctt));
		}else{
			return;
		}
		if (isset($this->se)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->se));
		}else{
			return;
		}
		if (isset($this->ge)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->ge));
		}else{
			return;
		}
		if (isset($this->iea)) {
			parent::addIntoEDIArray($this->SegmentToArray($this->iea));
		}else{
			return;
		}
	}
	public function GeneratePOFooter()
	{
		
		$this->GenerateSE();
		$this->GenerateGE();
		$this->GenerateIEA();
	}
	public function GenerateCTT()
	{
		$ctt01 							= sizeof($this->details);
		$ctt02 							= 0;
		for ($i=0; $i < sizeof($this->details) ; $i++) { 
			$one 						= $this->details[$i];
			$quantity 					= $one->getQuantity();
			$ctt02						+= $quantity;
		}
		$this->ctt 						= CTT::GenerateCTT($ctt01,$ctt02);
	}
	public function GenerateSE()
	{
		$totalline 						= $this->getDataSize();
		$se02 							= $this->st->getST02();
		$this->se 						= SE::GenerateSE($totalline,$se02);
	}
	public function GenerateGE()
	{
		$ge02 							= $this->gs->getGS06();
		$this->ge 						= GE::GenerateGE($ge02);
	}
	public function GenerateIEA()
	{
		$iea02 							= $this->isa->getISA13();
		$this->iea 						= IEA::GenerateIEA($iea02);
	}
}

?>