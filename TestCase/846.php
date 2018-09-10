<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("../Milk_EDI.php");
$sender 			= '';
$control_num 		= '1608'; 			//isa iea controller number isa13
$gs_control 		= '1104'; 			//gs ge group control numbergs06
$st_control 		= '0001'; 			//st se transaction
$sku  				= '';		
$quantity 			= '';

$_846 				= new Milk_EDI846();
$_846 				->init();
$logID 				= 1;
$_846 				->initHeader($sender);
$_846 				->Generate846Header($control_num,$gs_control,$st_control,$logID,Milk_EDIConfig::DEFAULT_WAREHOUSE);

$item_array 		= array();
$item 				= new Item846;
$item ->initWithInput($sku,$quantity,Milk_EDIConfig::DEFAULT_WAREHOUSE);
$item_array[] 		= $item;
$item 				= new Item846;
$item ->initWithInput('','',Milk_EDIConfig::DEFAULT_WAREHOUSE);
$item_array[] 		= $item;
$item 				= new Item846;
$item ->initWithInput('','',Milk_EDIConfig::DEFAULT_WAREHOUSE);
$item_array[] 		= $item;

$_846 ->setDetails($item_array);
$_846 ->GenerateExist846ToEDIObj();
$_846 ->GeneratePOFooter();
$_846 ->GenerateExist846ToEDIObj();
$path 				= 'test_846';
var_dump($_846->arr2file($path,''));

$edistore 			= new EDIStore;
$edistore->initWIthUserAccount('');
var_dump($edistore);





Milk_EDI::SendFile($edistore->getSend_from(),$edistore->getSend_to(),'../test_846');
var_dump($_846->getDetails());
echo "<pre>";
//var_dump($_846);
echo "</pre>";
echo('123');
echo "<pre>";
var_dump($result);
echo "</pre>";
/**
*item1
$item 				= new Item855();
$po1_array 			
	= array(
		'PO1',
		PO1::$PO101_count,
		3,
		PO1::PO103_EA,
		10.0,
		PO1::PO105_NT,
		PO1::PO106_UP,
		663489005166
	);
$ack1_array
	= array(
		'ACK',
		ACK::ACK01_IA,
		3,
		ACK::ACK03_EA,
		ACK::ACK04_068,
		EDIfunctionl::ccyymmdd();
	);
$PO1 				= new PO1($po1_array);
$ACK 				= new ACK($ack1_array);  
PO1::$PO101_count++;
$item 				->initWithPOdata($PO1,$ACK);
$_855 				->addItem($item);


//item2
$item 				= new Item855();
$po1_array 			
	= array(
		'PO1',
		PO1::$PO101_count,
		3,
		PO1::PO103_EA,
		10.0,
		PO1::PO105_NT,
		PO1::PO106_UP,
		663489005167
	);
$ack1_array
	= array(
		'ACK',
		ACK::ACK01_IA,
		3,
		ACK::ACK03_EA,
		ACK::ACK04_068,
		EDIfunctionl::ccyymmdd();
	);
$PO1 				= new PO1($po1_array);
$ACK 				= new ACK($ack1_array);  
PO1::$PO101_count++;
$item 				->initWithPOdata($PO1,$ACK);
$_855 				->addItem($item);

//item3
$item 				= new Item855();
$po1_array 			
	= array(
		'PO1',
		PO1::$PO101_count,
		3,
		PO1::PO103_EA,
		30.0,
		PO1::PO105_NT,
		PO1::PO106_UP,
		663489035022
	);
$ack1_array
	= array(
		'ACK',
		ACK::ACK01_R3,
		3,
		ACK::ACK03_EA,
		ACK::ACK04_068,
		EDIfunctionl::ccyymmdd();
	);
$PO1 				= new PO1($po1_array);
$ACK 				= new ACK($ack1_array);  
PO1::$PO101_count++;
$item 				->initWithPOdata($PO1,$ACK);
$_855 				->addItem($item);

//item4
$item 				= new Item855();
$po1_array 			
	= array(
		'PO1',
		PO1::$PO101_count,
		3,
		PO1::PO103_EA,
		20.0,
		PO1::PO105_NT,
		PO1::PO106_UP,
		663489035020
	);
$PO1 				= new PO1($po1_array);
$ACK 				= new ACK($ack1_array);
$ack1_array
	= array(
		'ACK',
		ACK::ACK01_IR,
		3,
		ACK::ACK03_EA,
		ACK::ACK04_068,
		EDIfunctionl::ccyymmdd();
	);
PO1::$PO101_count++;
$item 				->initWithPOdata($PO1,$ACK);
$_855 				->addItem($item);

//item5
$item 				= new Item855();
$po1_array 			
	= array(
		'PO1',
		PO1::$PO101_count,
		3,
		PO1::PO103_EA,
		20.0,
		PO1::PO105_NT,
		PO1::PO106_UP,
		663489035021
	);
$ack1_array
	= array(
		'ACK',
		ACK::ACK01_IR,
		3,
		ACK::ACK03_EA,
		ACK::ACK04_068,
		EDIfunctionl::ccyymmdd();
	);
$PO1 				= new PO1($po1_array);
$ACK 				= new ACK($ack1_array);  
PO1::$PO101_count++;
$item 				->initWithPOdata($PO1,$ACK);
$_855 				->addItem($item);

//item1
$item 				= new Item855();
$po1_array 			
	= array(
		'PO1',
		PO1::$PO101_count,
		3,
		PO1::PO103_EA,
		10.1,
		PO1::PO105_NT,
		PO1::PO106_UP,
		663489035019
	);
$ack1_array
	= array(
		'ACK',
		ACK::ACK01_IR,
		3,
		ACK::ACK03_EA,
		ACK::ACK04_068,
		EDIfunctionl::ccyymmdd();
	);
PO1::$PO101_count++;
$PO1 				= new PO1($po1_array);
$ACK 				= new ACK($ack1_array);  
$item 				->initWithPOdata($PO1,$ACK);
$_855 				->addItem($item);
*/
?>