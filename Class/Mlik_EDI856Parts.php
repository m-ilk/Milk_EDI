<?php 
/**
* get shipment infomration from ship_order table
      default setting:   only 1 purchase order for 1 shippment

*/
require_once(dirname(__FILE__) . "/Milk_EDIConfig.php");
require_once(Milk_EDIConfig::MilkObject_path);
class Shipment extends MilkObject
{
	/*orders shippment table*/
	const ERP_SHIP_TABLE = '';
	/*END*/
	/*Vendor Name*/
	CONST TYPE_PALLET 						= 'pallets';
	
	public $orderCode;		  				//Order table refrence_no_platform
	public $tacking_number;               	//REF 02 hl s
  	public $method;                       	//TD5 03
 	public $shipmentID;                   	//BSN 02
	public $totalWeightLB;                	//TD1 07 [total weight]
	public $shippedDate;                  	//DTM 02    CCYYMMDD
	public $shippedTime;                  	//DTM 03    HHMM
	public $Packages;                    	//package 	array
	public $type; 						 	//const
	//condition
	public $lading_qty;
	//NEED TO GENERATE VALUES
	//flat
	public $POnum;                        //HL O REF01
	public $totalCartoon;                 //TD1 02 [total quantity]
	public $SFVendor; 					  //vendor 
	public $SFname; 					  //HL S N1 SF 02(not in used)
	public $SFAddress; 					
	public $SFCity;
	public $SFProvince;
	public $SFPostal;
	public $SFCountry;

	public $STCode;
	//both
	
	/**
	* 	@param array 					(array)
	*										['orderCode'] 		string erp order id
	*										['tackingNumber']	string
	*										[shipMethod] 		amazon ship method
	*										['shipmentID'] 		used for isa and bsn segment
	*										['totalWeightLB']  	user for DS 856
	*										['shippedDate'] 	ccyymmdd
	*										['shippedTime'] 	hhss
	*										['type'] 			(const) not fully implement
	*										['lading_qty'] 		used for shipping type lading
	*										['vendor'] 			used for PO order
	*															DS get vendor code from orginal 850
	*										['SF*'] 			prefix with SF is 'send from' information
	*															used for po 	
	*															DS get SF infomration from original 850
	*										['ST*'] 			prefix with ST is 'send to' information
	*															used for po
	*															DS get ST information from original 850
	*										['ponum'] 			orginal 850 po number used for both po and ds  
	*										['packages'] 		(array)
	*																(array)
	*																	[sku]
	*																	[quantity]
	*	@example 
	*		'packages' => 
	*		array (size=4)
	*		  0 => 
	*		    array (size=2)
	*		      0 => 
	*		        array (size=2)
	*		          'sku' => string 'CH0155-2' (length=8)
	*		          'quantity' => int 1
	*		      1 => 
	*		        array (size=2)
	*		          'sku' => string 'CH0059-1' (length=8)
	*		          'quantity' => int 1 
	*	@param order_flag 				(string) DS|PO
	*	@return 						(void)
	*/
	public function __construct($array,$order_flag = 'DS')
	{
		$this->orderCode 					= $array['orderCode'];
		$this->tacking_number 				= $array['tackingNumber'];
		$this->method 						= $array['shipMethod'];
		$this->shipmentID 					= $array['shipmentID'];
		$this->totalWeightLB 				= $array['totalWeightLB'];
		$this->shippedDate 					= $array['shippedDate'];
		$this->shippedTime 					= $array['shippedTime'];
		if (isset($array['totalCartoon'])) {
			$this->totalCartoon 			= $array['totalCartoon'];
		}
		if (isset($array['type'])) {
			$this->type 					= $array['type'];
		}else{
			$this->type 					= '';
		}
		if (isset($array['lading_qty'])) {
			$this->lading_qty 				= $array['lading_qty'];
		}
		if (isset($array['vendor'])) {
			$this->SFVendor 				= $array['vendor'];
			$this->SFCity 					= $array['SFcity'];
			$this->SFProvince 				= $array['SFprovince'];
			$this->SFPostal 				= $array['SFpostal'];
			$this->SFCountry 				= $array['SFcountry'];
 		}
 		if (isset($array['STCode'])) {
 			$this->STCode 					= $array['STCode'];
 		}
 		$ponum 								= EDILog::getPonumByOrderCode($this->orderCode);
		if (!$ponum) {
			throw new Exception("Invalid order code number, can not po number in 93");
		}
		$this->POnum  						= $ponum;
		if ($order_flag=='DS') {
			$this->Packages 				= Package::GenerateDSPackages($array['packages'],$this->POnum,$this->totalWeightLB);
		}elseif ($order_flag=='PO') {
			$this->Packages 				= Package::GeneratePOPackages($array['packages'],$this->POnum,$this->totalWeightLB);
		}
	}
	public function getTotalQuantityShipped()
	{
		$ItemQuantity = 0;
		for ($i=0; $i < sizeof($this->Packages) ; $i++) { 
			$pack = $this->Packages[$i];
			for ($j=0; $j < sizeof($pack->items) ; $j++) { 
				$ItemQuantity += $pack->items[$j]->quantity;
			}
		}
		return $ItemQuantity;
	}
}
/**
* 
*/
class Package
{
	public $quantity; 				//{DS}hl p td1 02 [used] not used yet;
	public $weight;					//{DS}require
	public $items;					//{DS & PO}(Item856) array
	public $package_id;				//{DS}

	public function __construct()
	{
		
	}
	/**
	*	@param array 				(array)
	*									[]	package array
	*										[] item array
	*											[sku]
	*											[quantity]
	*	@param ponum 				(string)
	*	@param totalweight 			(string)
	*	@return 					(Package array)
	*	@example  					
	*	array (size=4)
	*		0 => 
	*		   array (size=2)
	*		      0 => 
	*		        array (size=2)
	*		          'sku' => string 'CH0155-2' (length=8)
	*		          'quantity' => int 1
	*		      1 => 
	*		        array (size=2)
	*		          'sku' => string 'CH0059-1' (length=8)
	*		          'quantity' => int 1
	*/
	public static function GenerateDSPackages($array,$ponum,$totalweight)
	{
		if (empty($array)||!isset($array)) {
			throw new Exception("Empty packages");
		}
		$output 				= array();
		$totalquantity 			= sizeof($array);
		$eachweight 			= number_format((float)$totalweight/$totalquantity, 2, '.', '');
		for ($i=0; $i < sizeof($array) ; $i++) { 
			$onePackage 		= $array[$i];
			$output[] 			= self::GenearteOneDSPackage($onePackage,$ponum,$i+1,$eachweight);
		}
		return $output;
	}
	/**
	*	@param P_array 				(array)
	*									[] array
	*										['sku']
	*										['quantity']
	*	@param ponum 				(string)
	*	@param package_id 			(string)
	*	@param weight 				(string)in lb
	*	@return package 			(Package)
	*	@example  0 => 
			        array (size=2)
			          'sku' => string 'CH0155-2' (length=8)
			          'quantity' => int 1
			      1 => 
			        array (size=2)
			          'sku' => string 'CH0059-1' (length=8)
			          'quantity' => int 1
	*/
	public static function GenearteOneDSPackage($P_array,$ponum,$package_id,$weight)
	{

		$package 				= new Package();
		$package->package_id 	= $package_id;
		$package->weight 		= $weight;
		$itemsarra 				= array();
		for ($i=0; $i < sizeof($P_array) ; $i++) { 
			$item 				= new Item856();
			$item->initDSItem($P_array[$i],$ponum);
			$item->package_id  	= $package_id;
			$itemsarra[] 		= $item;
		}
		$package ->items 		= $itemsarra;
		return $package;
	}
	/**
	*	@param item_array 			(array)
	*									[array]
	*										see initPOItem()
	*	@return packages 			(package array)
	*/
	public static function GeneratePOPackages($package_array)
	{
		$packages 				= array();
		for ($j=0; $j < sizeof($package_array) ; $j++) { 
			$items_array 			= $package_array[$j];
			$package 				= new Package();
			$output 				= array();
			for ($i=0; $i < sizeof($items_array) ; $i++) { 
				$item 				= new Item856();
				$item->initPOItem($items_array[$i]);
				$output[] 			= $item;
			}
			$package->items 		= $output;
			$packages[] 			= $package;
		}
		return $packages; 
	}
	/**
	*	limitation package weight is not implement
	*	@param hl_o_id				(string)hl parent order segement id
	*	@param hl_id 				(string)this hl p segment id
	*	@param trackingnum 			(string)
	*	@return array 				(array) hl package level segements array				
	**/
	public function GenerateHL_P_PO($hl_o_id,$hlid,$trackingnum)
	{
		$array 					= array();
		$hl_P 					= HL::GenerateHL_P($hlid,$hl_o_id);
		$array[] 				= $hl_P;
		$ref 					= REF::GenerateREF(REF::REF01_CN,$trackingnum);
		$array[] 				= $ref;
		return $array;
	}
}
/**
* 
*/
class Item856 
{

	public $Amazon_sku;							//{DS|PO}
	public $ERP_sku;							//{}
	public $quantity;							//{}shipped quantity
	public $po1_id; 							//product id
	public $quantity_o;							//quantity order, po102 
	public $package_id;
	public $sku_type;

	public function __construct()
	{
		
	}
	/**
	*	@param array 							(array)
	*												[sku_type] (const)
	*												[quantity]
	*												[erp_sku]
	*												[amazon_sku] it does not required to be sku 
	*															the value of this field depend on sku type
	*/
	public function initPOItem($array)
	{
		$this->sku_type 		= $array['sku_type'];
		$this->quantity 		= $array['quantity'];
		$this->erp_sku 			= $array['erp_sku'];
		if (isset($array['Amazon_sku'])) {
			$this->Amazon_sku 	= $array['Amazon_sku'];
		}else{
			$this->Amazon_sku 	= EDIProduct::convertSku2Upc($this->erp_sku);
			if (!$this->Amazon_sku||empty($this->Amazon_sku)) {
				throw new Exception("Can Not find sku:".$this->ERP_sku." in mapping, ponum: $ponum");
			}
		}
		
	}
	/**
	*	 array (size=2)
    *      'sku' => string 'CH0155-2' (length=8)
    *      'quantity' => int 1
	*/
	public function initDSItem($array,$ponum)
	{
		$this->ERP_sku 			= $array['sku'];
		$this->quantity 		= $array['quantity'];
		$this->Amazon_sku 		= ITEM::getEDISKUbyERPSKUAndPOnum($this->ERP_sku,$ponum);
		if (!$this->Amazon_sku||empty($this->Amazon_sku)) {
			throw new Exception("Can Not find sku:".$this->ERP_sku." in mapping, ponum: $ponum");
		}
		$row 					= ITEM::getPO1RowbyPOnumAndSKU($ponum,$this->ERP_sku);
		$this->po1_id 			= $row['po1_id'];
		$this->quantity_o 		= $row['po1_qty'];
	}
	/**
	*	limitation mea dtm not implement
	*	@param hlid 							(string)
	*	@param hl_p_id 							(string)					
	*/
	public function GenerateItem_PO($HLid,$hl_P_id=""){
		$array 					= array();
		$HL_I 					= HL:: GenerateHL_I($HLid,$hl_P_id);
		$array[] 				= $HL_I;
		$LIN 					= LIN::Generate856LIN_PO($this->Amazon_sku,$this->sku_type);
		$array[] 				= $LIN;
		//TO DO reject item implementation
		$SN1 					= SN1::GenerateSN1_PO('',$this->quantity);
		$array[] 				= $SN1;
		return $array;
	}
}

?>