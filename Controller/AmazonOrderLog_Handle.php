<?php 
/*
michael lee production
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../../../../Sys_Sesscion_Start2.php');
require_once(dirname(__FILE__).'/../Milk_EDI.php');
if (isset($_POST["isAjax"]))
{
    if ($_POST["isAjax"] == 1)
    {
        if ($_POST['act']=='search') {
            $ponum              = $_POST['ponum'];
            $user_account       = $_POST['user_account'];
            $date_start         = $_POST['date_start'];
            $date_end           = $_POST['date_end'];
            search($ponum,$user_account,$date_start,$date_end);
        }
        if ($_POST['act']=='insert') {
            $ponum              = $_POST['ponum'];
            insert($ponum);
        }
        if ($_POST['act']=='receiver') {
            receiver();
        }
    }
}
function receiver()
{
    $stores                     = EDIStore::getEDIStoreByCode();
    for ($i=0; $i < sizeof($stores) ; $i++) { 
        $one                    = $stores[$i];
        $path                   = $one->getPath();
        $result                 = Milk_EDI::Receiver($path);
        //echo "<pre>";
        var_dump($result);
        //echo "</pre>";
    }

}
function insert($ponum)
{
    if (!EDILog::CheckIfPonumExist($ponum)) {
        echo "[{'ack':'0','msg':'po number not exist'" . "}]"; 
        return ;
    }
    if (EDILog::CheckIfPonumInsertAlready($ponum)) {
        echo "[{'ack':'0','msg':'po number insert already'" . "}]"; 
        return ;
    }
   
        $result                 = Milk_EDI::InsertOneOrder($ponum);
        $output                 = "[{'ack':'".$result['ack']."','msg':'".$result['msg']."'";
        if (isset($result['error'])) {
            $output             .= "'error':'".$result['error']."'";
        }
        $output                 .="}]"; 
        echo "$output";

}
function search($ponum,$user_account,$date_start,$date_end)
{
    $array                      = array();
    if (!empty($ponum)) {
        $array['ponum']         = $ponum;
    }
    if (!empty($user_account)) {
        $array['user_account']  = $user_account;
    }
    if (!empty($date_start)&&!empty($date_end)) {
        $array['date_start']    = $date_start;
        $array['date_end']      = $date_end;
    }
    $array['order_by']          = 'create_time';
    $array['order_type']        = EDILog::ORDER_TYPE_DS;
    $result = EDILog::getEDILogByCondition($array);
    //var_dump($result);
    $colInfo = getColumns();
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
        echo "[{'ack':'0','msg':'This platform data is not in ERP system','columns':".$colInfo."}]";
    }
}
function getColumns()
{
    $_855_GS06                     = '855 GS06';
    $_856_GS06                     = '856 GS06';
    if(Sys_Sesscion_Start::$_language =='zh_CN') {
        $user_account             = '店铺';
        $po_number                = 'Po 号';
        $create_time              = '创建时间';
        $state                    = 'ERP 状态';
        $state_855                = '855 状态';
        $state_856                = '856 状态';
        $order_code               = '订单号码';
        $action                   = '操作';
    } else {
        $user_account             = 'Store';
        $po_number                = 'Po number';
        $create_time              = 'Create Time';
        $state                    = 'ERP State';
        $state_855                = '855 State';
        $state_856                = '856 State';
        $order_code               = 'order code';
        $action                   = 'action';
    }
    $colInfo ="[".
                "{'field':'user_account','title':'".$user_account."'}," .
                "{'field':'order_code','title':'".$order_code."'}," .
                "{'field':'po_number','title':'".$po_number."'}," .
                "{'field':'create_time','title':'".$create_time."'}," .
                "{'field':'state','title':'".$state."'}," .
                "{'field':'state_855','title':'".$state_855."'}," .
                "{'field':'state_856','title':'".$state_856."'}," .
                "{'field':'action','title':'".$action."'}" .
                "]";
    return $colInfo;
}
/*
    get front-end row value,used for bootstrap table
*/
function getData($result)
{
    $dataInfo                               = '';
    for ($i=0; $i <sizeof($result) ; $i++) {
        $one                                = $result[$i];
        $action                             = '';
        if ($one->getState()==EDILog::STATE_NOTINSERT) {
            $action                         = 
                    '<button type="button" class="btn btn-default insert"'.
                    ' flag = "'.$one->getPo_number().'">'.
                    'Insert'.
                    '</button>';
        }else{
            
        }
        $dataItem  ="{".
            "'user_account':'"              .$one->getUser_account()."',".
            "'order_code':'"                .$one->getOrder_code()."',".
            "'po_number':'"                 .$one->getPo_number()."',".
            "'create_time':'"               .$one->getCreate_time()."',".
            "'state':'"                     .$one->getStateString()."',".
            "'state_855':'"                 .$one->getState_855_string()."',".
            "'state_856':'"                 .$one->getState_856_string()."',".
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