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
            $addressCode                = $_POST['addressCode'];
            search($addressCode);
        }
    }
}
function search($addressCode)
{
    if (!Milk_EDIAddress::checkAddress_codeExist($addressCode)) {
        echo "[{'ack':'0','msg':'No such addres code'}]";
        return;
    }
    try {
        $address                            = new Milk_EDIAddress();
        $address                            ->initWithAddress_code($addressCode);        
        $colInfo                            = getColumns();
        $dataInfo                           = getData($address);
        echo "[{'ack':'1','msg':'success'," . "'columns':" . $colInfo . "," . "'data':" . $dataInfo . "}]"; 
    } catch (Exception $e) {
         echo "[{'ack':'0','msg':'error :'".$e->getMessage()."}]";
    }
}
function getColumns()
{
    $colInfo ="[".
                "{'field':'address_code','title':'Adddress Code'}," .
                "{'field':'amazon_identifier','title':'Amazon Identifier'}," .
                "{'field':'name','title':'Name'}," .
                "{'field':'address1','title':'Address1'}," .
                "{'field':'address2','title':'Address2'}," .
                "{'field':'city','title':'City'}," .
                "{'field':'state','title':'State'}," .
                "{'field':'zip','title':'Zip'}," .
                "{'field':'country','title':'Country'}".
                "]";
    return $colInfo;
}
/**
*   get front-end row value,used for bootstrap table
*   @param address                          (EDIAddress)
*/
function getData(Milk_EDIAddress $address)
{
    $dataInfo                               = '';
    $dataItem                               ="{".
            "'address_code':'"              .$address->getAddress_code()."',".
            "'amazon_identifier':'"         .$address->getAmazon_identifier()."',".
            "'name':'"                      .$address->getName()."',".
            "'address1':'"                  .$address->getAddress1()."',".
            "'address2':'"                  .$address->getAddress2()."',".
            "'city':'"                      .$address->getCity()."',".
            "'state':'"                     .$address->getState()."',".
            "'zip':'"                       .$address->getZip()."',".
            "'country':'"                   .$address->getCountry().
        "'}";
    $dataInfo                               = "[".$dataItem."]";
    return $dataInfo;
}
?>