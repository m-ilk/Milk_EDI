<?php 
/*
michael lee production
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../Milk_EDI.php');
if (isset($_POST["isAjax"]))
{
    if ($_POST["isAjax"] == 1)
    {
        if ($_POST['act']=='search') {
            $user_account       = $_POST['user_account'];
            $upc                = $_POST['upc'];
            $asin               = $_POST['asin'];
            $sku                = $_POST['sku'];
            search($user_account,$upc,$asin,$sku);
        }
        if ($_POST['act']=='detail') {
            $upc                = $_POST['upc'];
            detail($upc);
        }
        if ($_POST['act']=='update') {
            $upc                = $_POST['upc'];
            $asin               = $_POST['asin'];
            $sku                = $_POST['sku'];
            $target             = $_POST['target'];
            update($upc,$asin,$sku,$target);
        }
        if ($_POST['act']=='create') {
            $upc                = $_POST['upc'];
            $asin               = $_POST['asin'];
            $sku                = $_POST['sku'];
            $target             = $_POST['target'];
            $user_account       = $_POST['user_account'];
            create($upc,$asin,$sku,$target,$user_account);
        }
    }
}
/**
*   create a new product 
*   @param upc                                  (string)
*   @param asin                                 (string)
*   @param sku                                  (string)
*   @param target                               (string)
*   @param user_account                         (string)
*   @return echo
*/
function create($upc,$asin,$sku,$target,$user_account)
{
    try {
        if (!isset($upc)||!isset($asin)||!isset($sku)||!isset($target)||!isset($user_account)) {
            throw new Exception("Invalid inputs");
        }
        if (Milk_EDI846::checkIfUpcExist($upc)) {
            throw new Exception("Can not find product by upc: $upc");
        }
        $product                = new Milk_EDI846;
        $product                ->initWithInputs($upc,$asin,$sku,$target,$user_account);
        $result                 = $product->createProduct();
        if ($result) {
             echo "[{'ack':'1','msg':'Success'}]";
        }else{
            echo "[{'ack':'0','msg':'Fail to create'}]";
        }
    } catch (Exception $e) {
        echo "[{'ack':'0','msg':'error: ".$e->getMessage()."'}]";
    }
}
/**
*   update product information by upc
*   @param upc                                  (string)
*   @param asin                                 (string)
*   @param sku                                  (string)
*   @param target                               (string)
*/
function update($upc,$asin,$sku,$target)
{
    try {
        if (!isset($upc)) {
            throw new Exception("Invalid upc");
        }
        if (!Milk_EDI846::checkIfUpcExist($upc)) {
            throw new Exception("Can not find product by upc: $upc");
        }
        $array                      = array();
        $array['asin']              = $asin;
        $array['sku']               = $sku;
        $array['target']            = $target;
        $db_helper                  = new OperateDBHelper;
        $Tran                       = $db_helper->GetTranConn();
        $db_helper->BeginTran($Tran);
        $result                     = Milk_EDI846::updateProductByUPC($upc,$array,$Tran);
        if ($result) {
             echo "[{'ack':'1','msg':'Success'}]";
        }else{
             echo "[{'ack':'0','msg':'Fail to update'}]";
        }
    } catch (Exception $e) {
        echo "[{'ack':'0','msg':'error: ".$e->getMessage()."'}]";
    }
}
/**
*   Action edi button
*   @param upc                                      (string)
*/
function detail($upc)
{
    try {
        if (!isset($upc)) {
            throw new Exception("Invalid Input");
        }
        $edi846                    = new Milk_EDI846;
        $result                    = $edi846->initWithUpc($upc);
        if (!$result) {
            throw new Exception("Fail to find product by upc");
        }
        $dataInfo                   = "{".
            "'user_account':'"      .$edi846->getUser_account()."',".
            "'sku':'"               .$edi846->getSku()."',".
            "'asin':'"              .$edi846->getAsin()."',".
            "'upc':'"               .$edi846->getUpc()."',".
            "'current':'"           .$edi846->getCurrent_quantity()."',".
            "'target':'"            .$edi846->getTarget_quantity()."',".                
        "}";
        $dataInfo                   ="[".$dataInfo."]";
        echo "[{'ack':'1','msg':'success','data':" . $dataInfo . "}]";
    } catch (Exception $e) {
        echo "[{'ack':'0','msg':'error: ".$e->getMessage()."'}]";
    }
}
/**
*   search products based on condition
*   @param $user_account                            (string)
*   @param $upc                                     (string)
*   @param $asin                                    (string)
*   @param $sku                                     (string)
*/
function search($user_account,$upc,$asin,$sku)
{
    try {
        $array                      = array();
        if (!empty($user_account)) {
            $array['user_account']  = $user_account;
        }
        if (!empty($upc)) {
            $array['upc']           = $upc;
        }
        if (!empty($asin)) {
            $array['asin']          = $asin;
        }
        if (!empty($sku)) {
            $array['sku']           = $sku;
        }
        $colInfo = getColumns();
        $result = EDIProduct::getProductsByCondition($array);
        //var_dump($result);
        if ($result) {
            $dataInfo = getData($result);
            if (!empty($result)) {
                $dataInfo  = "[".$dataInfo."]";
                //$dataInfo = EnCodeData($dataInfo);
            }else{
                $dataInfo  = "[{}]";
            }
            echo "[{'ack':'1','msg':'success'," . "'columns':" . $colInfo . "," . "'data':" . $dataInfo . "}]"; 
        }else{
            echo "[{'ack':'0','msg':'Fail to find any result','columns':".$colInfo."}]";
        }
    } catch (Exception $e) {
         echo "[{'ack':'0','msg':'error: ".$e->getMessage()."','columns':".$colInfo."}]";
    }
    
}
/**
*   bootstrap table column name
*   @return string
*/
function getColumns()
{
    $sku                            = 'SKU';
    $asin                           = 'ASIN';
    $upc                            = 'UPC';
    $current                        = 'Current Quantity';
    $target                         = 'Target Qunatity';
    $user_account                   = 'Store';
    $action                         = 'Action';
    $colInfo ="[".
                "{'field':'sku','title':'".$sku."'}," .
                "{'field':'asin','title':'".$asin."'}," .
                "{'field':'upc','title':'".$upc."'}," .
                "{'field':'current','title':'".$current."'}," .
                "{'field':'target','title':'".$target."'}," .
                "{'field':'user_account','title':'".$user_account."'}," .
                "{'field':'action','title':'".$action."'}" .
                "]";
    return $colInfo;
}
/**
*   get front-end row value,used for bootstrap table
*   @param result                           (EDI846 array)
*   @return string                          
*/
function getData($result)
{
    $dataInfo                               = '';
    for ($i=0; $i <sizeof($result) ; $i++) {
        $one                                = $result[$i];
        $action                             = 
                '<button type="button" class="btn btn-default edit_btn"'.
                ' flag = "'.$one->getUpc().'">'.
                'Edit'.
                '</button>';
        
        $dataItem  ="{".
            "'user_account':'"              .$one->getUser_account()."',".
            "'sku':'"                       .$one->getSku()."',".
            "'asin':'"                      .$one->getAsin()."',".
            "'upc':'"                       .$one->getUpc()."',".
            "'current':'"                   .$one->getCurrent_quantity()."',".
            "'target':'"                    .$one->getTarget_quantity()."',".
            "'action':'"                    .$action.
        "'}";
        if ($dataInfo =='') {
            $dataInfo                       = $dataItem;
        }else{
            $dataInfo                       = $dataInfo .",".$dataItem;
        }
    }
    return $dataInfo;
}

?>