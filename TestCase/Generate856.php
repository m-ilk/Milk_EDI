<?php
/**
*	michael lee production
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("Milk_EDI.php");
$reference_no 						= "";
$order_id 							= ""; 			
$Shipment 							= new Shipment();
$Shipment->order_id 				= $order_id;
$Shipment->reference_no_platform 	= $reference_no;
$Shipment->shippment_id 			= "";
$Shipment->totalCartoon 			= "";
$Shipment->totalWeightLB 			= "";
$Shipment->method 					= '';
$Shipment->tacking_number 			= "";
$Shipment->shippedDate 				="";
$Shipment->shippedTime 				="";
$array 								= array();
$pack1 								= new Package();
$itemarry 							= array();
$item1 								= new Item856();
$item1->Amazon_sku 					= "";
$item1->quantity 					= 0;
$itemarry[] 						= $item1;
$item2 								= new Item856();
$item2->Amazon_sku 					= "";
$item2->quantity 					= 0;
$itemarry[] 						= $item2;
$pack1->items 						= $itemarry;
$array[] 							= $pack1;
$Shipment->Packages 				= $array;
$Shipment->GenerateShipmentInfo();
Milk_EDI::Generate856AndSend($Shipment);
?>