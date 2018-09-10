<?php
/**
* michael lee production
* EDI based configuration 
*/
class Milk_EDIConfig
{

	const INSERT_ORDER_METHOD 	= "Milk_EDI::InsertOneOrder";
	CONST DEFAULT_WAREHOUSE 	= ''; 			//amazon warehouse code

	const EDI_RECEIVE_PATH 		= ''; 			//edi 850 997 receive path
	CONST EDI_850_FOLDER 		= 'Milk_EDI_0/850Files/';
	const PAYLOAD_EXT 			= 'payload_0';	//edi file extension in receive path
	const MOVE_TO_PATH 			= "Milk_EDI/OriginalFiles/"; //the path original files that are going to be moved to
	CONST SAVED_PATH 			= 'Milk_EDI/Files/'; //body class file save path
	CONST EDI_846_FOLDER 		= 'Milk_EDI/846Files/'; //846 files saved path
	
	CONST BOOTSTRAP_CSS			= 'bootstrap.min.css';
	CONST BOOTSTRAP_JS 			= 'bootstrap.min.js';
	CONST BOOTSTRAP_TABLE_CSS 	= 'bootstrap-table.css';
	CONST BOOTSTRAP_TABLE_JS 	= 'bootstrap-table.js';
	const JQUERY_PATH 	 		= 'jquery.min.js';
	CONST JQUERY_UI_JS 			= 'jquery-ui.min.js';
	CONST LIVEQUERY_PATH 		= 'jquery.livequery.min.js';
	CONST DATE_PICKER_JS		= 'zebra_datepicker.js';
	CONST DATE_PICKER_CSS 		= 'default.css';
	CONST BOOTSTRAP_MODAL_CSS 	= '';
	CONST BOOTSTRAP_MODAL_JS 	= 'bootstrap-modal-ex.js';
	
	const MilkObject_path		= 'MilkObject.php';
	//relative path
	CONST EDI_PATH 				= '/Milk_EDI';

	CONST ERP_ORDER_CLASS 		=  "";

	/**	as2 library*/
	CONST SEND_FELE_CLASS 		= '/as2secure/www/include.inc.php';
	//send from infomration used for 856
	CONST SF_CITY 				= ''; 					//full name
	CONST SF_PROVINCE 			= ''; 					// 2 letters :CA
	CONST SF_POSTAL 			= ''; 					//postal code
	CONST SF_COUNTRY 			= ''; 					// 2 letters :US
	/**
	*	Controller html head tags
	*/
	public static function getControllerRequiredLinks()
	{
		$output = '
			<script src="'.self::JQUERY_PATH.'"></script>
			<script src="'.self::JQUERY_UI_JS.'"></script>
			<script type="text/javascript" src="'.self::LIVEQUERY_PATH.'"></script>
			<link rel="stylesheet" href="'.self::BOOTSTRAP_CSS.'" type="text/css">
			<script type="text/javascript" src="'.self::BOOTSTRAP_JS.'"></script>
			<link rel="stylesheet" href="'.self::BOOTSTRAP_TABLE_CSS.'" type="text/css">
			<script type="text/javascript" src="'.self::BOOTSTRAP_TABLE_JS.'"></script>
			<link rel="stylesheet" href="'.self::DATE_PICKER_CSS.'" type="text/css">
			<script type="text/javascript" src="'.self::DATE_PICKER_JS.'"></script>
			<script type="text/javascript" src="'.self::BOOTSTRAP_MODAL_JS.'"></script>
		';
		echo $output;
	}
}
?>