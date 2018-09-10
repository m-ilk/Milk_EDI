<?php 
/**
*	@author michael lee production
*	Mapping Amazon Address code to acutal address
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../Class/Milk_EDIConfig.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html lang="en">
<head>
	<?php echo Milk_EDIConfig::getControllerRequiredLinks();?>
	<script type="text/javascript" src="../JS/AddressMapping.js"></script>
	<link rel="stylesheet" href="../Css/AddressMapping.css">
</head>
<body>
<h3>
	Address Code Mapping 
</h3>
<div class="container" >
	<div class="row equal">
		<div class="col-xs-4 col-md-3 line_height">
			<div class="align_right">
				Address Code
			</div>
		</div>
		<div class="col-xs-6 col-md-6">
			<input type="text" class="form-control" id="addressCode">
		</div>
	</div>
	<div class="row equal">
		<div class="col-xs-3 col-md-3 line_height">
		</div>
		<div class="col-xs-3 col-md-3 line_height">
			<button type="button" class="btn btn-primary align_right" id ="search_btn">
				Search
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
					Detail
				</h4>
			</div>
			<div class="modal-body">
				<div class="container">
					<div class="row equal">
						<div  class="col-xs-3 align_right line_height">
							ERP sku
						</div>
						<div class="col-xs-6 col-md-6">
							<input type="text" class="form-control" id="asin_input">
						</div>
					</div>
					<div class="row equal">
						<div class="col-xs-3 align_right line_height">
							UPC
						</div>
						<div class="col-xs-3 col-md-2">
								<input type="text" class="form-control" id="upc_input">
						</div>
					</div>
					<div class="row equal">
						<div class="col-xs-3 align_right line_height">
							Asin
						</div>
						<div class="col-xs-6 col-md-6">
							<input type="text" class="form-control" id="asin">
						</div>
					</div>
					<div class="row equal">
						<div class="col-xs-3 col-md-3 align_right line_height">
							Title
						</div>
						<div class="col-xs-7 col-md-7 line_height">
							<input type="text" class="form-control" id="title">
						</div>
					</div>
				</div>
			</div>          
		  
			<div class="modal-footer">
				<button class="btn btn-success" id="btn_create">
					Create
				</button>
				<button class="btn btn-success" id="btn_edit">
					Edit
				</button>
				<button class="btn btn-default" data-dismiss="modal" id="">
					Cancel
				</button>
			</div>
		</div>
	</div>
</div>
</body> 
</html>