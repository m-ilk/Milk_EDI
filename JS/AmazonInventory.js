$(document).ready(function () {
	$('#search_btn').click(function () {
		user_account 						= $('#user_account').val();
		upc 								= $('#upc').val();
		asin 								= $('#asin').val();
		sku 								= $('#sku').val();
		$.post("AmazonInventory_Handle.php",
            {
                isAjax				: 1,
                act 				: 'search',
				user_account 		: user_account,
				upc					: upc,
				asin 				: asin,
				sku					: sku,
            },
	    function (result)
	    {
	    	//alert(result);
			try{
				$result = eval(result);
				data = $result[0].data;
		        ack = $result[0].ack;
		       
		        if (ack == '0') {
		        	alert($result[0].msg);
		        }else if (ack == '1') {
		        	if (data.length > 0){
			            //data = JSON.hunpack(data);
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
	$('#insert_btn').click(function () {
		$('#sku_input').val('');
    	$('#upc_input').val('').removeAttr("disabled");
    	$('#asin_input').val('');
    	$('#target').val('');
    	$('#btn_update').css('display','none');
    	$('#btn_create').css('display','inline-block');
    	$('.create_row').css('display','block');
    	$("#edit_modal").modal({backdrop: 'static'});

	})
	$('#btn_update').click(function () {
		sku 								= $('#sku_input').val();
		upc 								= $('#upc_input').val();
		asin 								= $('#asin_input').val();
		target 								= $('#target').val();
		
		$.post("AmazonInventory_Handle.php",
            {
                isAjax				: 1,
                act 				: 'update',
				target 				: target,
				upc					: upc,
				asin 				: asin,
				sku					: sku,

            },
	    function (result)
	    {
			try{
				$result = eval(result);
				data = $result[0].data;
		        ack = $result[0].ack;
		       
		        if (ack == '0') {
		        	alert($result[0].msg);
		        }else if (ack == '1') {
		        	alert($result[0].msg);
		        }
			}catch(err){
				alert('Error: '+err.message);
			}
			
	    });
	});
	$('#btn_create').click(function () {
		$('#sku_input').val('');
    	$('#upc_input').val('').removeAttr("disabled");
    	$('#asin_input').val('');
    	$('#target').val('');
    	$('#btn_update').css('display','none');
    	$('#btn_create').css('display','inline-block');
    	$('.create_row').css('display','none');
    	$("#edit_modal").modal({backdrop: 'static'});
	})
	$('#btn_create_request').click(function () {
		sku 								= $('#sku_input').val();
		upc 								= $('#upc_input').val();
		asin 								= $('#asin_input').val();
		target 								= $('#target').val();
		user_account 						= $('#user_account_input').val();					
		$.post("AmazonInventory_Handle.php",
            {
                isAjax				: 1,
                act 				: 'create',
				target 				: target,
				upc					: upc,
				asin 				: asin,
				sku					: sku,
				user_account 		: user_account,
            },
	    function (result)
	    {
			try{
				$result = eval(result);
				data = $result[0].data;
		        ack = $result[0].ack;
		       
		        if (ack == '0') {
		        	alert($result[0].msg);
		        }else if (ack == '1') {
		        	alert($result[0].msg);
		        }
			}catch(err){
				alert('Error: '+err.message);
			}
			
	    });
	});
})
$(".edit_btn").livequery(function ()
{
    $(this).click(function ()
    {	
        //to-do
        upc = $(this).attr('flag');
        $.post("AmazonInventory_Handle.php",
        {
            isAjax		: 1,
            act 		: 'detail',
			upc 		: upc,
        },
	    function (result)
	    {
	    	try{
				$result = eval(result);
				data = $result[0].data[0];
		        ack = $result[0].ack;
		        if (ack == '0') {
		        	alert($result[0].msg);
		        }else if (ack == '1') {
		        	$('#sku_input').val(data.sku);
		        	$('#upc_input').val(data.upc).attr("disabled", "disabled");
		        	$('#asin_input').val(data.asin);
		        	$('#target').val(data.target);
		        	$('#btn_update').css('display','inline-block');
    				$('#btn_create').css('display','none');
		        }
		        $("#edit_modal").modal({backdrop: 'static'});
	    	}catch(err){
	    		alert('Error: '+err.message);
	    	}
			
	    });
    });
});
