<?php
/**
*   michael lee production
*   view PO order
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../Milk_EDI.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Amazon Purchase Order</title>
    <?php echo Milk_EDIConfig::getControllerRequiredLinks();?>
    <script type="text/javascript" src="../JS/AmazonPurchaseOrder.js"></script>
    <link rel="stylesheet" href="../Css/AmazonPurchaseOrder.css">
</head>
<body>
<div class="container">
    <div class="row equal">
        <h3>
            Amazon Purchase Order
        </h3>
    </div>
    <div class="row equal" id="category_row">
        <div class="col-xs-3 align_right line_height">
            Purchase Order Number'
        </div>
        <div class="col-xs-9 col-md-9">
            <input type="text" class="form-control" id="ponum">
        </div>
    </div>
    <div class="row equal">
        <div class="col-xs-3 align_right line_height">
        </div>
        <div class="col-xs-6 col-md-6">
            <button type="button" class="btn btn-primary" id="search_btn">
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
<div class="modal fade" id="detail_modal" role="dialog">
    <div class="modal-dialog" style="width:1000px;">
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
                    <div class="row equal" >
                        <div class="col-xs-3 align_right line_height">
                           Purchase Order Number
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <input type="text" class="form-control" id="ponum_fix" disabled>
                        </div>
                    </div>
                    <div class="row equal" >
                        <div class="col-xs-3 align_right line_height">
                            Status
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <input type="text" class="form-control" id="status" disabled>
                        </div>
                    </div>
                    <div class="row equal" >
                        <div class="col-xs-3 align_right line_height">
                           Purchase Order Number
                        </div>
                        <div class="col-xs-3 col-md-3">
                            <input type="text" class="form-control" id="window_start" disabled>
                        </div>
                        <span>-</span>
                        <div class="col-xs-3 col-md-3">
                            <input type="text" class="form-control" id="window_end" disabled>
                        </div>
                    </div>
                    <div class="row equal">
                        <div class="col-xs-3 align_right line_height">
                            Create Time
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <input type="text" class="form-control" id="time" disabled>
                        </div>
                    </div>
                    <div class="row equal">
                        <div class="col-xs-3 align_right line_height">
                           Address
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <input type="text" class="form-control" id="address" disabled>
                        </div>
                    </div>
                    <div class="row equal">
                        <div class="col-xs-3 align_right line_height">
                            City
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <input type="text" class="form-control" id="city" disabled>
                        </div>
                    </div>
                    <div class="row equal">
                        <div class="col-xs-3 align_right line_height">
                            State
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <input type="text" class="form-control" id="province" disabled>
                        </div>
                    </div>
                    <div class="row equal">
                        <div class="col-xs-3 align_right line_height">
                            postal code
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <input type="text" class="form-control" id="postalcode" disabled>
                        </div>
                    </div>
                    <div class="row equal">
                        <div class="col-xs-3 align_right line_height">
                            Shipping Method
                        </div>
                        <div class="col-xs-6 col-md-6">
                            <div class="form-group">
                              <select class="form-control" id="shipping_method">
                                <?php
                                    foreach (AmazonShippingMethod::getMethods() as $key => $value) {
                                        echo "<option value='$key'>$value</option>";
                                    }
                                ?>
                              </select>
                            </div>
                        </div>
                    </div>
                    <div class="row equal confirm_row">
                        <div class="col-xs-3 align_right line_height">
                            Tracking Number
                        </div>
                        <div class="col-xs-6 col-md-6">
                             <input type="text" class="form-control" id="tracking">
                        </div>
                    </div>
                    <div class="row equal confirm_row">
                        <div class="col-xs-3 align_right line_height">
                           Package Quantity
                        </div>
                        <div class="col-xs-6 col-md-6">
                             <input type="text" class="form-control" id="package">
                        </div>
                    </div>
                </div>
            </div>          
            <div class="container" style="width: 1000px;">
                <table id="DetailTable" 
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
            <div class="modal-footer">
                <button class="btn btn-warning" id="btn_accept">
                   Acceept
                </button>
                <button class="btn btn-success" id="btn_confirm">
                    Confirm
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