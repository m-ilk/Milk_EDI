<?php
/**
*	michael lee production
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("Milk_EDI.php");
$sender 			= '';
$control_num 		= '1608'; 			//isa iea controller number isa13
$gs_control 		= '1104'; 			//gs ge group control numbergs06
$st_control 		= '0001'; 			//st se transaction
$ponum 				= '';
$_855 				= new Milk_EDI855();
$_855 				->initPO();
$_855 				->initControlNum($control_num,$gs_control,$st_control,$sender);
$_855 				->GeneratePOHeader($ponum);

$item_array 		= array();
$item 				= generatePOItem(3,ACK::ACK01_IA,10.0,'');
$item_array[] 		= $item;
$item 				= generatePOItem(3,ACK::ACK01_IA,10.0,'');
$item_array[] 		= $item;
$item 				= generatePOItem(3,ACK::ACK01_R3,30.0,'');
$item_array[] 		= $item;
$item 				= generatePOItem(3,ACK::ACK01_IR,20.0,'');
$item_array[] 		= $item;
$item 				= generatePOItem(3,ACK::ACK01_IR,20.0,'');
$item_array[] 		= $item;
$item 				= generatePOItem(3,ACK::ACK01_IR,10.1,'');
$item_array[] 		= $item;
$_855 				->setItems($item_array);

$_855 				->GeneratePOFooter();
$path 				= 'test_855';
$_855 				->ExportPO855ToFile($path,'');

echo "<pre>";
var_dump($_855);
echo "</pre>";
echo('123');

function generatePOItem($quantity,$ack01,$price,$itemID,$id_type = PO1::PO106_UP)
{
	$item 				= new Item855();
	$po1_array 			
		= array(
			'PO1',
			PO1::$PO101_count,
			$quantity,
			PO1::PO103_EA,
			$price,
			PO1::PO105_NT,
			$id_type,
			$itemID
		);
	$ack1_array
		= array(
			'ACK',
			$ack01,
			$quantity,
			ACK::ACK03_EA,
			ACK::ACK04_068,
			EDIfunctionl::ccyymmdd()
		);
	$PO1 				= new PO1($po1_array);
	$ACK 				= new ACK($ack1_array);  
	PO1::$PO101_count++;
	$item 				->initWithPOdata($PO1,$ACK);
	return $item;
}
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