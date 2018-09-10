$(document).ready(function () {
	$('#search_btn').click(function () {
		ponum=$('#ponum').val();
		$.post("AmazonPurchaseOrder_Handle.php",
            {
                isAjax: 1,
                act: 'search',
				ponum: ponum,
            },
	    function (result)
	    {
	    	try{
	    		$result = eval(result);
				data = $result[0].data;
		        ack = $result[0].ack;
		        if (ack == '0') {
		        	alert('fail to get data')
		        }else if (ack == '1') {
		        	if (data.length > 0){
			        }
					$('#QueryTable').bootstrapTable('destroy');
			        $('#QueryTable').bootstrapTable({
						columns: $result[0].columns,
			            data: data
			        });
		        }
	    	}catch(err){
				alert('Error: '+err.message);
			}
			
	    });
	});

	//click create button
	$('#upload_btn').click(function () {
		$("#edit_modal").modal({backdrop: 'static'});
	})

	$('#btn_update').click(function () {
		var data = new FormData();
		data.append('file',$('#file')[0].files[0]);
		data.append('isAjax',1);
		data.append('act','upload');
		$.ajax({
        	url:"AmazonPurchaseOrder_Handle.php",
        	type:"POST",
            data:data,
            processData: false,
			contentType: false,
			success:function (result)
		    {
				alert(result);
		    },
		    error:function(result) {
		    	alert(result);
		    }
	  	})
	})
})
//click purchase order number
$(".poanchor").livequery(function ()
{
    $(this).click(function ()
    {
        $("#detail_modal").modal({backdrop: 'static'});
        //to-do
        ponum 				= $(this).attr('id');
        $.post("AmazonPurchaseOrder_Handle.php",
        {
            isAjax: 1,
            act: 'detail',
			ponum: ponum,
        },
	    function (result)
	    {
			alert(result);
			try{
				$result 	= eval(result);
				data 		= $result[0].data;
		        ack 		= $result[0].ack;
		        if (ack == '0') {
		        	alert($result[0].msg);
		        }else if (ack == '1') {
		        	$('#ponum_fix').val(data.ponum);
		        	$('#status').val(data.erp_status);
		        	$('#window_start').val(data.shipwindow_start);
		        	$('#window_end').val(data.shipwindow_end);
		        	$('#total_cost').val(data.total_cost);
		        	$('#time').val(data.time);
		        	$('#address').attr('disabled','disabled').val(data.address);
		        		$('#postalcode').attr('disabled','disabled').val(data.postal);
		        		$('#city').attr('disabled','disabled').val(data.city);
		        		$('#province').attr('disabled','disabled').val(data.province);
		        	if (data.erp_status=='Accept'||data.erp_status=='Confirm') {
		        		$('.confirm_row').css('display','block');
		        	}else{
		        		$('.confirm_row').css('display','none');
		        	}
		        	if (data.erp_status=='Confirm') {
		        		$('#shipping_method').attr('disabled','disabled').val(data.shipping_method);
		        		$('#tracking').attr('disabled','disabled').val(data.tracking_lv);
		        		$('#package').attr('disabled','disabled').val(data.package_lv);
		        	}else{
		        		$('#shipping_method').removeAttr('disabled');
		        		$('#tracking').removeAttr('disabled').val('');
		        		$('#package').removeAttr('disabled').val('');
		        	}
		        	$('#DetailTable').bootstrapTable('destroy');
			        $('#DetailTable').bootstrapTable({
						columns: data.column,
			            data: data.table
			        });
		        }
	    	}catch(err){
				alert('Error: '+err.message);
			}
			
	    });
    });
});

//click accept button
//接收按钮
$("#btn_accept").livequery(function ()
{
	$(this).click(function ()
    {
        ponum = $('#ponum_fix').val();
        data = $('#DetailTable').bootstrapTable('getData');
        //alert(data);
		input = [];
		for (var i = 0; i <data.length ; i++) {
			sku = data[i].erpsku;
			//alert(sku);
			quantity = $('[flag=a'+sku+']').val();
			element = {sku:sku,quantity:quantity};
			input.push(element);
		}

        $.post("AmazonPurchaseOrder_Handle.php",
            {
                isAjax: 1,
                act: 'accept',
				ponum: ponum,
				input:input,
            },
	    function (result)
	    {
	    	try{
	    		alert(result);
	    	}catch(err){
				alert('Error: '+err.message);
			}
			
	    });
    });
});

//click purchase order number
//接收按钮
$("#btn_confirm").livequery(function ()
{
	$(this).click(function ()
    {
        ponum 				= $('#ponum_fix').val();
        data 				= $('#DetailTable').bootstrapTable('getData');
        shipping_method 	= $('#shipping_method').val();
        package 			= $('#package').val();
        tracking 			= $('#tracking').val();
         //alert(data);
		input = [];
		for (var i = 0; i <data.length ; i++) {
			sku = data[i].erpsku;
			//alert(sku);
			quantity 		= $('[flag=c'+sku+']').val();
			element 		= {sku:sku,quantity:quantity};
			input.push(element);
		}

        $.post("AmazonPurchaseOrder_Handle.php",
            {
                isAjax: 1,
                act: 'confirm',
				ponum: ponum,
				shipping_method:shipping_method,
				package:package,
				tracking:tracking,
				input:input,
            },
	    function (result)
	    {
	    	try{
	    		alert(result);
	    	}catch(err){
				alert('Error: '+err.message);
			}
	    });
    });
});