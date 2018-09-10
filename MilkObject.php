<?php
/**
* michael lee production
*/
require_once(dirname(__FILE__).'/../includes/OperateDBHelper.php');
require_once(dirname(__FILE__).'/../includes/GeneralDateHelper.php');
class MilkObject
{

    static $db_helper;
    function __construct()
    {
        
    }
    public static function initDb_helper()
    {
        self::$db_helper        = new OperateDBHelper; 
        return true;
    }
    public static function getTransaction()
    {
        return self::$db_helper->GetTranConn();                
    }
    public static function beginTransaction($tran)
    {
        self::$db_helper->BeginTran($tran);
    }
    public static function commitTransaciton($tran)
    {
        self::$db_helper->CommitTranAndClose($tran);
    }
    public static function rollbackTransaction($tran)
    {
        self::$db_helper->RollbackTranAndClose($tran);
    }
    public static function checkExist($sql)
    {
    	$query                 = "SELECT EXISTS ($sql LIMIT 1)";
        $db_helper             =  new OperateDBHelper;
        $result                = $db_helper->QuerySQL($query);
        if ($result[0][0]=='1') {
            return true;
        }else{
            return false;
        }
    }
    public static function querySelect($sql)
    {
    	$query = $sql;
        $db_helper =  new OperateDBHelper;
        $result = $db_helper->QuerySQL($query);
        return $result;
    }
    public static function queryInsert($sql)
    {	
		$db_helper = new OperateDBHelper;
		$result = $db_helper->ExeSQL($sql);
    	return $result;
    }
    public static function queryInsertWithTran($sql,&$Tran)
    {
        $db_helper = new OperateDBHelper;
        //$Tran= $db_helper->GetTranConn();
        //$db_helper->BeginTran($Tran);
        $result = $db_helper->ExeSQLwithTran($Tran, $sql);
        return $result;
    }
    public static function queryUpdateWithTran($sql,&$Tran){
        $db_helper = new OperateDBHelper;
        $result = $db_helper->ExeSQLwithTran($Tran,$sql);
        return $result;
    }
    public static function querySelectWithTran($sql,&$Tran)
    {
        $db_helper = new OperateDBHelper;
        $result = $db_helper->QuerySQLwithTran($Tran,$sql);
        return $result;
    }
    public static function queryCount($sql)
    {
        $db_helper =  new OperateDBHelper;
        $result = $db_helper->QuerySQL($sql);
        if ($result) {
            return sizeof($result);
        }else{
            return false;
        }
    }
    public static function HttpPost($url,$data, $optional_headers = null)
    {
        $data = http_build_query($data);
    
        $params = array('http' => array(
            'method' => 'POST',
            'content' => $data
            ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problem reading data from $url, $php_errormsg");
        }
        return $response;
    }
    public static function initWithObjectsArray($classname,$functionname,$data)
    {
        if (!isset($data)) {
            return false;
        }
        $arr = array();
        for ($i=0; $i < sizeof($data) ; $i++) { 
            $row = $data[$i];
            $one =new ReflectionClass($classname);
            $instance = $one->newInstance();
            $instance->$functionname($row);
            $arr[] = $instance;
        }
        return $arr;
    }
    /**
    *   @return ccyy-mm-dd hh:mm:ss
    */
    public static function GetCurDatetime()
    {
        return GeneralDateHelper::GetCurDatetime();
    }
    /**
    *   check if filname's file extension is allowed
    *   @param filename                             (string)
    *   @param allowed_ext                          (array)
    *           e.g array('jpg','png')
    */
    public static function checkFileExtension($filename,$allowed_ext)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(in_array($ext,$allowed_ext) ) {
            return true;
        }else{
            return false;
        }
    }
    public static function sql_string($input){
        if (!isset($input)) {
            return "";
        }
        $db = Connect_to_DB2();
        return mysqli_real_escape_string($db,trim($input)) ;
    }
}
?>