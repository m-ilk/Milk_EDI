<?php 
/*
	Michael lee production
*/
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once(dirname(__FILE__).'/../../../../Sys_Sesscion_Start2.php');
	require_once(dirname(__FILE__).'/../Milk_EDI.php');
	$platformusers 					= EDIStore::getEDIStoreByCode();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html lang="en">
<head>
	<?php echo Milk_EDIConfig::getControllerRequiredLinks();?>
	<script type="text/javascript" src="../JS/AmazonOrderLog.js"></script>
	<link rel="stylesheet" href="../Css/AmazonOrderLog.css">
</head>
<body>
<h3>
	Amazon Vendor Log
</h3>
<div class="container" >
	<div class="row equal">
		<div class="col-xs-4 col-md-3 line_height">
			<div class="align_right">
				<?php if(Sys_Sesscion_Start::$_language =='zh_CN') { echo '店铺:'; } else { echo 'Store';} ?>
			</div>
		</div>
		<div class="col-xs-6 col-md-6">
			<select class="form-control" id='user_account'>
				<?php
					for ($i=0; $i < sizeof($platformusers) ; $i++) {
						$one 	= $platformusers[$i]; 
						echo "<option value = '".$one->getUser_account()."'> ".$one->getUser_account()."</option>";
					}
				?>
			</select>
		</div>
	</div>
	<div class="row equal">
		<div class="col-xs-2 col-md-3 line_height">
			<div class="align_right">
				<?php if(Sys_Sesscion_Start::$_language =='zh_CN') { echo '日期:'; } else { echo 'Date';} ?>
			</div>
		</div>
		<div class="col-xs-3 col-md-3">
				<input type="text" class="form-control" id="date_start">
		</div>
		<div class="col-xs-3 col-md-3">
				<input type="text" class="form-control" id="date_end">
		</div>
	</div>
	<div class="row equal">
		<div class="col-xs-4 col-md-3 line_height">
			<div class="align_right">
				<?php if(Sys_Sesscion_Start::$_language =='zh_CN') { echo 'PO number:'; } else { echo 'PO number';} ?>
			</div>
		</div>
		<div class="col-xs-6 col-md-6">
			<input type="text" class="form-control" id="ponum">
		</div>
	</div>
	<div class="row equal">
		<div class="col-xs-3 col-md-3 line_height">
		</div>
		<div class="col-xs-3 col-md-3 line_height">
			<button type="button" class="btn btn-primary align_right" id ="search_btn">
				<?php if(Sys_Sesscion_Start::$_language =='zh_CN') { echo '搜索'; } else { echo 'Search';} ?>
			</button>
			<button type="button" class="btn btn-success align_right" id="insert_btn">
				<?php if(Sys_Sesscion_Start::$_language =='zh_CN') { echo '插入'; } else { echo 'Insert';} ?>
			</button>
			<button type="button" class="btn btn-default align_right" id="receive_btn">
				<?php if(Sys_Sesscion_Start::$_language =='zh_CN') { echo '接收'; } else { echo 'Receive';} ?>
			</button>
		</div>
	</div>
</div>

<div class="container max">
	<table id="QueryTable" 
			data-toggle="table"
			data-height="550"
			data-click-to-select='false'
			class="table table-bordered"
		   > 
		<thead>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>

<div class="modal fade" id="edit_modal" role="dialog">
	<div class="modal-dialog" style="width:100%;">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">
					
				</h4>
			</div>
			<div class="modal-body">
				<div class="container">
					
				</div>
			</div>          
		  
			<div class="modal-footer">
				<button class="btn btn-success" id="btn_create">
					<?php if(Sys_Sesscion_Start::$_language =='zh_CN') { echo '创建'; } else { echo 'Create';} ?>
				</button>
				<button class="btn btn-default" data-dismiss="modal" id="">
					<?php if(Sys_Sesscion_Start::$_language =='zh_CN') { echo '取消'; } else { echo 'Cancel';} ?>
				</button>
			</div>
		</div>
	</div>
</div>
</body> 
</html>