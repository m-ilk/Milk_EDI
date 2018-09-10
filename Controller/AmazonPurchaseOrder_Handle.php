<?php
/*
michael lee production
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../Milk_EDI.php');
if (isset($_POST["isAjax"])) {
    if ($_POST["isAjax"] == 1) {
        if ($_POST['act']=='search') {
        	$ponum = $_POST['ponum'];
        	search($ponum);
        }
        if ($_POST['act']=='detail') {
        	$ponum = $_POST['ponum'];
        	detail($ponum);
        }
        if ($_POST['act']=='accept') {
        	$ponum = $_POST['ponum'];
        	if (isset($_POST['input'])) {
        		$arr = $_POST['input'];
        		accept($ponum,$arr);
        	}else{
        		echo "[{'ack':'0','msg':'nothing is selected'}]";
        	}
        }
        if ($_POST['act']=='confirm') {
            $ponum = $_POST['ponum'];
            if (isset($_POST['input'])) {
                $arr                    = $_POST['input'];
                $shipping_method        = $_POST['shipping_method'];
                $package                = $_POST['package'];
                $tracking               = $_POST['tracking'];
                confirm($ponum,$arr,$shipping_method,$package,$tracking);
            }else{
                echo "[{'ack':'0','msg':'nothing is selected'}]";
            }
        }
    }
}

/**
*   Confirm a po 
*   @param ponum                        (string) po number
*   @param arr                          (array)  
*                                               [quantity]
*                                               [sku]
*   @param shipping_method              (string)
*   @return echo             
*/
function confirm($ponum,$arr,$shipping_method,$package,$tracking){
    $result         = Milk_EDI::Confirm850PO($ponum,$arr,$shipping_method,$package,$tracking);
    if (isset($result['ack'])) {
        echo "[{'ack':'".$result['ack']."','msg':'".$result['msg']."','error':'".$result['error']."'}]";
    }else{
        echo "[{'ack': '-1','msg':'Invalid result'}]";
    }
}
/**
*   Accept a po 
*   @param ponum                        (string) po number
*   @param arr                          (array)  
*                                               [quantity]
*                                               [sku]
*   @param shipping_method              (string)
*   @return echo             
*/
function accept($ponum,$arr)
{	
    $result    = Milk_EDI::Accept850PO($ponum,$arr);
    if (is_array($result)&&isset($result['ack'])) {
        echo "[{'ack':'".$result['ack']."','msg':'".$result['msg']."','error':'".$result['error']."'}]";
    }else{
        echo "[{'ack': '-1','msg':'Invalid result'}]";
    }
}
/**
*   PO with all items
*   @param ponum                        (string)
*   @return echo                        check detail_data() for all output data     
*/
function detail($ponum)
{
    try {
        if (empty($ponum)) {
            echo "[{'ack':'0','msg':'invalid input po number'"."}]";
            return ;
        }
        if (!AmazonPurchaseOrder::checkPonumAlreadyExist($ponum)) {
            echo "[{'ack':'0','msg':'po number not exist'"."}]";
        }
        $order =  new AmazonPurchaseOrder();
        $order->initWithPOnum($ponum);
        if ($order->getErpStatus()>1) {
        }
        $data = detail_data($order);
        echo "[{'ack':'1','msg':'Success','data':".$data."}]";
    } catch (Exception $e) {
        echo "[{'ack':'0','msg':'error: ".$e->getMessage()."'}]";
    }
	
}
/**
*   detail() out put data
*   @param $order                       (AmazonPurchaseOrder)
*   @return string                      json format string
*/
function detail_data(AmazonPurchaseOrder $order)
{
    $status                     = $order->getErpStatus();
    $tracking_lv                = '';
    $tracking_ch                = '';
    $package_lv                 = '';
    $package_ch                 = '';
    $shipping_method            = '';
    $address                    = '';
    $city                       = '';
    $province                   = '';
    $postal                     = '';
    $address_code               = $order->getAddress_code();
    $EDIAddress                 = new Milk_EDIAddress();
    $result                     = $EDIAddress->initWithAddress_code($address_code);
    $tracking                   = $order->getTracking();
    $package                    = $order->getPackage();
    if (!$result) {
        throw new Exception("Can not find edi address");
    }
    $shipping_method            = $order->getShipping_method();
    $address                    = $EDIAddress->getAddress1();
    $city                       = $EDIAddress->getCity() ;
    $province                   = $EDIAddress->getState();
    $postal                     = $EDIAddress->getZip();
	$table                      = "";
	$data                       = "{".
		"'ponum':'".$order->getPonum()."',".
		"'status':'".$order->getStatus()."',".
		"'shipwindow_start':'".$order->getShipwindow_start()."',".
		"'shipwindow_end':'".$order->getShipwindow_end()."',".
		"'address':'".$address."',".
        "'city':'".$city."',".
        "'province':'".$province."',".
		"'erp_status':'".$order->getErpStatusString()."',".
        "'postal':'".$postal."',".
        "'shipping_method':'".$shipping_method."',".
        "'tracking':'".$tracking."',".
        "'package':'".$package."',".
		"'time':'".$order->getCreattime()."',".
		"'column':".detail_column().",".
		"'table':".detail_table_data($order).""
		."}";
	return $data;
}
/**
*   detail() out put data
*   used for bootstarp table
*   @return string                      json format string
*/
function detail_column()
{
	$erpsku                        = 'ERP SKU';
	$quantity                      = 'Quantity';
	$LA                            = 'LA Warehouse';
	$CH                            = 'CH Warehouse';
	$cost                          = 'cost';
	$status                        = 'ERP Status';
	$erp_accepted_quantity         = 'ERP Accept Quantity';
	$erp_confirm_quantity          = 'ERP Confirm Quantity';
	$colInfo ="[".
        "{'field': 'erpsku','title':'".$erpsku."'}," .
        "{'field': 'itemid','title':'Item ID'}," .
        "{'field': 'quantity','title':'".$quantity."'},".
        "{'field': 'cost','title':'".$cost."'},".
        "{'field': 'status','title':'".$status."'},".
        "{'field': 'erp_accepted_quantity','title':'".$erp_accepted_quantity."'},".
        "{'field': 'erp_confirm_quantity','title':'".$erp_confirm_quantity."'}".
        "]";
    return $colInfo;
}
/**
*   detail() out put data
*   used for bootstarp table
*   @param $order                       (AmazonPurchaseOrder)
*   @return string                      json format string              
*/
function detail_table_data(AmazonPurchaseOrder $order)
{
	$dataInfo                      = '';
	$details                       = $order->getDetails();
    $status                        = $order->getErpStatus();
	for ($i=0; $i < sizeof($details) ; $i++){ 
		$one = $details[$i];
        $accept                    = '';
        $confirm                   = '';
        if ($status==AmazonPurchaseOrder::STATUS_CREATE) {
            $accept                = "<input type=".'"text"'."flag=".'"a'.$one->getErpsku().'"'."value=".'"'.$one->getErpAcceptQuantity().'"'.">";
            $confirm               = "N/A";
        }elseif ($status==AmazonPurchaseOrder::STATUS_ACCEPT) {
            $accept                = $one->getErpAcceptQuantity();
            $confirm               = "<input type=".'"text"'."flag=".'"c'.$one->getErpsku().'"'."value=".'"'.$one->getErpConfirmQuantity().'"'.">";
        }else{
            $accept                = $one->getErpAcceptQuantity();
            $confirm               = $one->getErpConfirmQuantity();
        }
		$dataItem                 ="{".
			"'erpsku':'".$one->getErpsku()."',".
            "'itemid':'".$one->getItemID()."',".
            "'quantity':'".$one->getQuantity()."',".
            "'cost':'".$one->getCost()."',".
            "'status':'".AmazonPurchaseOrderDetail::ConvertStatus($one->getStatus())."',".
            "'erp_accepted_quantity':'".$accept."',".
            "'erp_confirm_quantity':'".$confirm.
            "'}";
        if ($i==0) {
        	$dataInfo              = $dataItem;
        }else{
        	$dataInfo              = $dataInfo.",".$dataItem;
        }
	}
	$dataInfo                      = "[".$dataInfo."]";
	return $dataInfo;
}
/**
*   search po by ponume
*   @param $ponum                       (string) po number
*   @return string                      json format string 
*/
function search($ponum)
{   
    try {
        $PurchaseOrders             = AmazonPurchaseOrder::searchByPonum($ponum);
        $colInfo = search_column();
        if (!empty($PurchaseOrders)) {
            $dataInfo               = search_data($PurchaseOrders);
            if ($dataInfo) {
                $dataInfo           = "[".$dataInfo."]";
            }else{
                $dataInfo           = "[]";
            }
            echo "[{'ack':'1','msg':'Success'," . "'columns':" . $colInfo . "," . "'data':" . $dataInfo . "}]";
        }else{
            echo "[{'ack':'1','msg':'Success'," . "'columns':" . $colInfo . "," . "'data':[]}]";
        }
    } catch (Exception $e) {
        echo "[{'ack':'-1','msg':'".$e->getMessage()."'}]";
    }
	
}
/**
*   search() out put data
*   @param data                         (AmazonPurchaseOrder array)
*   @return dataInfo                    json format string    
*/
function search_data($data){
	if (!isset($data)||empty($data)) {
		return false;
	}
	$dataInfo                      = '';
	for ($i=0; $i < sizeof($data) ; $i++) { 
		$dataItem                  ="{".
					"'ponum':'".'<a class="poanchor" id="'.$data[$i]->getPonum().'">'.$data[$i]->getPonum()."</a>',".
                    "'status':'".$data[$i]->getErpStatusString().
                    "','creattime':'".$data[$i]->getCreattime().
                    "'}";
        if ($i==0) {
            $dataInfo               = $dataItem;
        }else{
            $dataInfo               = $dataInfo.",".$dataItem;                
        }
	}
	return $dataInfo;
}
/**
*   search() out put data
*   used for bootstrap table
*   @return dataInfo                    json format string    
*/
function search_column(){
	$ponum                         = 'Purchase Order Number';
	$status                        = 'status';
	$creattime                     = 'create time';
	$colInfo                       = "[".
        "{'field': 'ponum','title':'".$ponum."'}," .
        "{'field': 'status','title':'".$status."'},".
        "{'field': 'creattime','title':'".$creattime."'},".
        "{'field': 'id','title':'id','visible':false}".
        "]";
    return $colInfo;
}
?>