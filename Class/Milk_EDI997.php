<?php
/**
* 	michael lee production
*	EDI 997
*/
require_once(dirname(__FILE__) . "/EDIObj.php");
require_once(dirname(__FILE__) . "/EDIsegment/EDI997Seg.php");
require_once(dirname(__FILE__) . "/Milk_EDIfunctionl.php");
class Milk_EDI997 extends EDIObj{
	private $ak1;				//segment
	private $ak9;				//segment
	public function __construct($data,$seperator,$line){
		parent::__construct($seperator,$line,$data);
	}
	/**
	*	generate ak1 segment and ak9 segment from original data
	*	@return void
	*/
	public function Generate()
	{
		$this->ak1 =  $this->getAK1();
		$this->ak9 =  $this->getAK9();
	}
	/**
	*	@return 							(AK1|exception)
	*/
	public function getAK1(){
		$AK1array = $this->getFirstArrayByName("AK1");
		if (isset($AK1array)) {
			return new AK1($AK1array);
		}else{
			throw new Exception("can not find AK1 in 997");
		}
	}
	/**
	*	@return 							(AK9|exception)
	*/
	public function getAK9()
	{
		$AK9array = $this->getFirstArrayByName("AK9");
		if (isset($AK9array)) {
			return new AK9($AK9array);
		}else{
			throw new Exception("can not find AK9 in 997");
		}
	}
	/**
	*	indentify the type of edi file this 997 is about
	*	@return 							(ak1 const|exception)
	*/
	public function getAcknowledgeType()
	{
		$type= $this->ak1->getAK101();
		if ($type==AK1::AK101_855) {
			return $type;
		}elseif ($type==AK1::AK101_856) {
			return $type;
		}elseif($type==AK1::AK101_846){
			return $type;
		}else{
			throw new Exception("Invalid Acknowledge Type: $type");
		}
	}
	/**
	*	get ak1 02 content
	*	get original send edi file GS06 ID
	*/
	public function getAcknowledgeID()
	{
		return $this->ak1->getAK102();
	}
	/**
	*	check the EDI file that this 997 trying to acknowledge is valid based on ak9
	*	@return 							(boolean)
	*/
	public function checkFileValid()
	{
		$flag = true;
		$ak901=$this->ak9->getAK901();
		$ak902=$this->ak9->getAK902();
		$ak903=$this->ak9->getAK903();
		$ak904=$this->ak9->getak904();
		if ($ak901!=AK9::AK901_A&&$ak901!=AK9::AK901_E) {
			$flag = false;
		}
		if ($ak902!=$ak903||$ak903!=$ak904) {
			$flag=false;
		}
		return $flag;
	}
}
?>