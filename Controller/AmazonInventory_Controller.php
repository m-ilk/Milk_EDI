<?php 
/**
*	@author Michael lee production
*	Amazon Dynamic Inventordy
*	EDI 846 Controller
*/
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once(dirname(__FILE__).'/../Milk_EDI.php');
	$platformusers 					= EDIStore::getEDIStoreByCode();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html lang="en">
<head>
	<?php echo Milk_EDIConfig::getControllerRequiredLinks();?>
	<script type="text/javascript" src="../JS/AmazonInventory.js"></script>
	<link rel="stylesheet" href="../Css/AmazonInventory.css">
</head>
<body>
<h3>
	Amazon Inventory 
</h3>
<div class="container" >
	<div class="row equal">
		<div class="col-xs-4 col-md-3 line_height">
			<div class="align_right">
				Store
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
		<div class="col-xs-4 col-md-3 line_height">
			<div class="align_right">
				SKU
			</div>
		</div>
		<div class="col-xs-6 col-md-6">
			<input type="text" class="form-control" id="sku">
		</div>
	</div>
	<div class="row equal">
		<div class="col-xs-2 col-md-3 line_height">
			<div class="align_right">
				ASIN
			</div>
		</div>
		<div class="col-xs-6 col-md-6">
				<input type="text" class="form-control" id="asin">
		</div>
	</div>
	<div class="row equal">
		<div class="col-xs-4 col-md-3 line_height">
			<div class="align_right">
				UPC
			</div>
		</div>
		<div class="col-xs-6 col-md-6">
			<input type="text" class="form-control" id="upc">
		</div>
	</div>
	<div class="row equal">
		<div class="col-xs-3 col-md-3 line_height">
		</div>
		<div class="col-xs-3 col-md-3 line_height">
			<button type="button" class="btn btn-primary align_right" id ="search_btn">
				Search
			</button>
			<button type="button" class="btn btn-success align_right" id="insert_btn">
				Add new
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
						<div class="col-xs-3 align_right line_height">
							UPC
						</div>
						<div class="col-xs-6 col-md-6">
								<input type="text" class="form-control" id="upc_input" disabled>
						</div>
					</div>
					<div class="row equal">
						<div  class="col-xs-3 align_right line_height">
							ERP sku
						</div>
						<div class="col-xs-6 col-md-6">
							<input type="text" class="form-control" id="sku_input">
						</div>
					</div>
					<div class="row equal">
						<div class="col-xs-3 align_right line_height">
							Asin
						</div>
						<div class="col-xs-6 col-md-6">
							<input type="text" class="form-control" id="asin_input">
						</div>
					</div>
					<div class="row equal">
						<div class="col-xs-3 col-md-3 align_right line_height">
							Target Quantity
						</div>
						<div class="col-xs-6 col-md-6">
							<input type="text" class="form-control" id="target">
						</div>
					</div>
					<div class="row equal create_row">
						<div class="col-xs-3 col-md-3 align_right line_height">
							Store
						</div>
						<div class="col-xs-6 col-md-6">
							<select class="form-control" id='user_account_input'>
								<?php
									for ($i=0; $i < sizeof($platformusers) ; $i++) {
										$one 	= $platformusers[$i]; 
										echo "<option value = '".$one->getUser_account()."'> ".$one->getUser_account()."</option>";
									}
								?>
							</select>
						</div>
					</div>
				</div>
			</div>          
		  
			<div class="modal-footer">
				<button class="btn btn-success" id="btn_update">
					Edit
				</button>
				<button class="btn btn-primary" id="btn_create_request">
					Create
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