<?php 
/**
* EDi object class
	All return does not contain Segment object
*/
require_once(dirname(__FILE__) . "/Milk_EDIConfig.php");
require_once(dirname(__FILE__) . "/Milk_EDIfunctionl.php");
require_once(Milk_EDIConfig::MilkObject_path);
class EDIObj extends MilkObject
{

	private $data;					//(array)	2 dimension [row, column]
	private $seperator;
	private $line;

	public static function getCurrentDir()
	{
		return dirname(__FILE__);
	}

	function __construct($seperator,$line,$array= array())
	{
		$this->seperator = $seperator;
		$this->line = $line;
		$this->data = $array;
	}
	public function getEDIarray(){
		return $this->data;
	}
	public function getSeparator(){
		return $this->seperator;
	}
	public function getLine(){
		return $this->line;
	}
	/**
	*	get File EDI type (850,855,856,997)
	* 	@param	return 				
	*	 	false 				:if the file is not a EDI file
	*/
	public static function CheckEDIFileType($filename,$separator,$line)
	{	
		$array = EDIObj::file2arr($filename,$separator,$line);
		if (empty($array)||!isset($array)) {
			return false;
		}
		if ($array[2][0]=='ST') {
			return $array[2][1];
		}else{
			return false;
		}	
	}
	public function clearData()
	{
		$this->data 			= array();
	}
	public function setSeperatorLine($seperator,$line)
	{
		$this->seperator 		= $seperator;
		$this->line 			= $line;
	}
	/**
	*	add a array to data
	*	@param element 				(array)
	*/
	public function addIntoEDIArray($element){
		if (is_array($element)) {
			$this->data[] 		= $element;
		}else{
			throw new Exception("only can add array to EDI array");
		}
	}
	/**
	*	number of segments
	*/
	public function getDataSize()
	{
		if (isset($this->data)) {
			return sizeof($this->data);
		}else{
			return 0;
		}
	}
	/**
	*	get segmnent by segment[0] value
	*	@param name 					(string)
	*	@return:						(array)an array of elemnt that match
	*/
	public function getSegmentArrayByName($name){
		$array 					= array();
		$NameLength 			= strlen($name);
		while ($elemnt = current($this->data)) {
			$header 			= key($this->data);
			if (strlen($header)>$NameLength) {
				$header 		= substr($header, 0,$NameLength);
			}
			if ($header==$name) {
				$array[] 		= $elemnt;
			}
			next($this->data);
		}
		reset($this->data);
		return $array;
	}
	/**
	*	ultility
	*	@param name 					(string) segment name
	*	@param index 					(int)
	*	@param value 					(string)
	*	@return: 						(array)an array of segment
	*/
	public function getArrayByIndexValueEqualTo($name,$index,$value){
		$array =$this->getSegmentArrayByName($name);
		if (isset($array)) {
			for ($i=0; $i <sizeof($array) ; $i++) { 
				if (Milk_EDIfunctionl::IsIndexValueEqualTo($array[$i],$index,$value)) {
					return $array[$i];
				}
			}
		}else{
			return null;
		}
		return null;
	}
	/**
	*	get first segement array that with 0 index value name
	*	@param name:					(string) segement name
	*	@return							(array)
	*/
	public function getFirstArrayByName($name)
	{
		for ($i=0; $i <sizeof($this->data) ; $i++) { 
			if ($this->data[$i][0]==$name) {
				return $this->data[$i];
			}
		}
		return null;
	}
	/**
	*	get segment with index 0 == $name and then get index value
	*	same as getArrayByIndexValueEqualTo()
	*	@param name:					(string) segment name
	*	@param index:					(int) index
	*	@return							(string)
	*/
	public function getValueByNameAndIndex($name,$index)
	{
		$array = $this->getArrayByIndex0ValueEqualTo($name);
		if (isset($array)&&sizeof($array)<$index) {
			throw new Exception("Can not get value by name or index");
		}
		return $array[$index];
	}
	/**
	*	see getValueByNameAndIndex()
	*/
	public function getArrayByIndex0ValueEqualTo($value)
	{
		for ($i=0; $i <sizeof($this->data) ; $i++) { 
			if ($this->data[$i][0]==$value) {
				return $this->data[$i];
			}
		}
	}
	/**
	*	convert given segment to array
	*	@param Segment 					(Segment)
	*	@return  						() 
	*/
	public function SegmentToString($Segment){
		if (is_subclass_of($Segment,"Segment")) {
			return $Segment->ToString($this->seperator,$this->line);
		}else{
			var_dump(get_parent_class($Segment)) ;
			throw new Exception("Invalid type object is sent to SegmentToString");
		}
	}
	/**
	*	convert segment to array
	*	@param segment 
	*/
	public function SegmentToArray($Segment){
		if (is_subclass_of($Segment,"Segment")) {
			return $Segment->getData();
		}else{
			throw new Exception("Invalid type object is sent to SegmentToArray");
		}
	}
	public function Obj_print(){
		$output=array();
		for ($i=0; $i <sizeof($this->data) ; $i++) { 
			$output[] = Milk_EDIfunctionl::Array2EDILine($this->data[$i],$this->seperator,$this->line);
		}
		Milk_EDIfunctionl::PrintArray($output);
	}
	/**
	*	number of line between $from to $to segment
	*	@param from 					(string)
	*	@param to 						(string)
	*/
	public function NumOfLineFromTo($from,$to)
	{
		$start_count = false;
		$count=0;
		for ($i=0; $i <sizeof($this->data) ; $i++) { 
			if (Milk_EDIfunctionl::IsIndexValueEqualTo($this->data[$i],0,$to)) {
				$start_count = false;
			}
			if ($start_count) {
				$count++;
			}
			if (Milk_EDIfunctionl::IsIndexValueEqualTo($this->data[$i],0,$from)) {
				$start_count = true;
			}
		}
		return $count+2;
	}
	/**
	*	Transfer EDI file to an array
	*	@param local_file 				(string) file path
	*	@param eparator 				(string) for each line, column seperator.
	*	@param line 					(string) line seperater
	*	@return 						(array) 2 dimension (row, column) 
 	*/
	public static function file2arr($local_file,$separator,$line){
		$content = file_get_contents($local_file);
		if ($content===false) {
			throw new Exception("File: $local_file does not exist");
			return null;
		}
		if (strpos($content, $separator)==false) {
			return null;
		}
		if (strpos($content, $line)==false) {
			return null;
		}
		$data = array();
		$line_array = explode($line, $content);

		$line_count = sizeof($line_array);
		for ($i=0; $i < $line_count ; $i++) { 
			$line = $line_array[$i];
			if (empty($line)) {
				continue;
			}
			$line = trim($line);
			$column_array = explode($separator, $line);
			$data[] = $column_array;
		}
		return $data;
	}
	/**
	*Convert EDIobj $data into a file based on $separator and $line 
	*	this function will not replace existing file with the same name
	*	@param	path						(string) default path is the same directory with EDIobj.php
	*											"\" for directory
	*	@param name 						(name) file name	
	*/
	public function arr2file($name,$path=""){
		if (empty($path)) {
			$filename = $path.$name;
		}else{
			$lastChar = substr($path, -1);
			$filename = "";
			if ($lastChar =="\\"||$lastChar=="/") {
				$filename = $path.$name;
			}else{
				$filename = $path."\\".$name;
			}
		}
		if (file_exists(self::getCurrentDir().'/'.$filename)) {
			throw new Exception("the file ".$filename." already exist");
		}
		$output="";
		for ($i=0; $i <sizeof($this->data) ; $i++) { 
			$output.= Milk_EDIfunctionl::Array2EDILine($this->data[$i],$this->seperator,$this->line);
		}
		if ($filename[0]=='/') {
			$fp = fopen($filename, "w");
		}else{
			$fp = fopen(Milk_EDIConfig::EDI_PATH.'/'.$filename, "w");
		}
		
		if ($fp) {
			if (fwrite($fp,$output)) {
				fclose($fp);
				if ($filename[0]=='/') {
					return $filename;
				}else{
					return Milk_EDIConfig::EDI_PATH.'/'.$filename;
				}
				
			}else{
				throw new Exception("Fail to write $name");
				return false;
			}
		}else{
			throw new Exception("Fail to open $path $name");
			
			return false;
		}
	}
	/**
	*	Move all the files with prefix $name to directory $scanpath
	*	@param scanpath 					(string)
	*	@param name 						(string)
	*	@return 							(boolean)
	**/
	public static function MoveOriginalFiles($scanpath,$name)
	{
		$files = scandir($scanpath);
		$flag = true;
		

		for ($i=0; $i <sizeof($files) ; $i++) {
			//print_r($files);
			$one = $files[$i];
			$value = substr($one, 0, strlen($name));
			
			if ($value == $name) {
				$flag = rename( $scanpath.'/'.$one,Milk_EDIConfig::MOVE_TO_PATH.$one);
				if ($flag) {

				}else{
					EDIErrorLog::ErrorLog(EDIErrorLog::ERRORLOG_FAIL2MOVE,$scanpath.'/'.$one,"MoveOriginalFiles");
					$flag = false;
				} 
			}
		}
		return $flag;
	}
}
?>