<?php 
/*
EDI static functions
*/
Class Milk_EDIfunctionl{
	/*
	get YYMMDD format date
		$input 		:(int) refer to php doc date() function
		return 	
					:(string)
	*/
	public static function yymmdd($input=0){
		if (empty($input)) {
			return date('ymd');
		}else{
			return date('ymd',$input);
		}
	} 
	public static function ccyymmdd($input=0){
		if (empty($input)) {
			return date('Ymd');
		}else{
			return date('Ymd',$input);
		}
	} 
	public static function hhmm(){
		return date("Hi",time());
	}
	public static function hhmmss(){
		return date("His",time());
	}
	/*	get element from array
		$array 			:(array)
		$index 			:(int)
		return 
		 				:
	*/	
	public static function GetElementFromArray($array,$index =0){
		if (isset($array)&&sizeof($array)>=$index) {
			return $array[$index];
		}else{
			return null;
		}
	}
	/*
		$array 					:(1d array)
		return 					(string)
	*/
	public static function getValueOfIndex($array,$index)
	{
		if (isset($array)&&sizeof($array)>=$index) {
			return $array[$index];
		}else{
			return "";
		}
	}
	public static function PrintArray($array){
		for ($i=0; $i <sizeof($array) ; $i++) { 
			print_r($array[$i]);
			echo "<br>";
		}
	}
	public static function NumString4Digit($num){
		$input = (string)$num;
		while (strlen($input)<4) {
			$input = "0".$input;
		}
		return $input;
	}
	public static function NumString9Digit($num)
	{
		$input =(string)$num;
		while (strlen($input)<9) {
			$input = "0".$input;
		}
		return $input;
	}
	/*
		isa 06 08
	*/
	public static function AddSpaceAtEndOfStringUntilSize($string,$size)
	{
		$input =(string)$string;
		while (strlen($input)<$size) {
			$input = $input.' ';
		}
		return $input;
	}
	/*
		return:					(string)
	*/
	public static function Array2EDILine($array,$seperator,$line){
		$result = "";
		for ($i=0; $i < sizeof($array) ; $i++) { 
			$result.=$array[$i];
			if ($i==(sizeof($array)-1)) {
				$result.=$line;
			}else{
				$result.=$seperator;
			}
		}
		return $result;
	}
	/*
		$array 					:(array)
		$index 					:(int)
		$value 					:(string)
		return: 				(boolean)
	*/
	public static function IsIndexValueEqualTo($array,$index,$value){
		if ($array[$index]==$value) {
			return true;
		}else{
			return false;
		}
	}
	public static function AllElementsIsTypeOf($array,$type)
	{
		$flag = true;
		if (!empty($array)) {
			for ($i=0; $i <sizeof($array) ; $i++) { 
				if (!is_a($array[$i], $type)) {
					$flag =false;
					break;
				}
			}
		}else{
			return false;
		}
		return $flag;
	}
	public static function Array2EDItext($data,$seperator,$line)
	{
		$out = "";
		for ($i=0; $i <sizeof($data) ; $i++) { 
			$out.= self::Array2EDILine($data[$i],$seperator,$line);
		}
		return $out;
	}
	/**
	*	this is only used for create files directory from EDI.php directory
	*	create directory in files/ directory
	*/
	public static function CreateDirectory($path)
	{
		if (!file_exists('../Files/'.$path)) {
			if (mkdir('../Files/'.$path, 0777, true)) {
				return true;
			}else{
				Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_FAIL2CREATE,$path,'CreateDirectory');
				return false;
			}
		}
		return true;
	}
	/**
	*	this is only used for create files directory from EDI.php directory
	*	create directory in 846Files/ directory
	*/
	public static function Create846Directory($path)
	{
		if (!file_exists(Milk_EDIConfig::EDI_846_FOLDER.$path)) {
			if (mkdir(Milk_EDIConfig::EDI_846_FOLDER.$path, 0777, true)) {
				return true;
			}else{
				Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_FAIL2CREATE,Milk_EDIConfig::EDI_846_FOLDER.$path,'CreateDirectory');
				return false;
			}
		}
		return true;
	}
	public static function Create850FilesDirectory($path)
	{
		if (!file_exists(Milk_EDIConfig::EDI_850_FOLDER.$path)) {
			if (mkdir(Milk_EDIConfig::EDI_850_FOLDER.$path, 0777, true)) {
				return true;
			}else{
				Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_FAIL2CREATE,Milk_EDIConfig::EDI_850_FOLDER.$path,'CreateDirectory');
				return false;
			}
		}
		return true;
	}
	/**
	*	copy a file and save it in targetpath
	*	@param return 					(boolean) return true if successfully moved the file to traget location.
	*
	*/
	public static function MoveToTargetFolder($currentpath,$targetpath){
		if (copy($currentpath, $targetpath)) {
			return true;
		}else{
			Milk_EDIErrorLog::ErrorLog(Milk_EDIErrorLog::ERRORLOG_FAIL2MOVE,$currentpath,'MoveToTargetFolder: Fail to copy 850 file');
			return false;
		}
	}
	public static function FilesCount($path,$ext)
	{
		$count = 0;
		$files = scandir ($path);
		for ($i=0; $i <sizeof($files) ; $i++) { 
			$once = $files[$i];
			$thisext = pathinfo($once, PATHINFO_EXTENSION);
			if ($thisext==$ext) {
				$count++;
			}
		}
		return $count;
	}
}

?>