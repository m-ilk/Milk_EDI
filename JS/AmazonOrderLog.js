$(document).ready(function () {
	$('#date_start').Zebra_DatePicker();
	$('#date_end').Zebra_DatePicker();


	$('#search_btn').click(function () {
		ponum 					=$('#ponum').val();
		user_account 			=$('#user_account').val();
		date_start 				=$('#date_start').val();
		date_end 				=$('#date_end').val();

		$.post("AmazonOrderLog_Handle.php",
            {
                isAjax			: 1,
                act 			: 'search',
				ponum 			: ponum,
				user_account 	: user_account,
				date_start 		: date_start,
				date_end 		: date_end,
            },
	    function (result)
	    {
			//alert(result);
			try{
	    		$result = eval(result);
				data = $result[0].data;
		        ack = $result[0].ack;
		       	$('#QueryTable').bootstrapTable('destroy');
		        $('#QueryTable').bootstrapTable({
					columns: $result[0].columns,
		            data: data
		        });
	    	}catch(err){
				alert('Error: '+err.message);
			}
			
	    });
	});
	$('#receive_btn').click(function () {
		$.post("AmazonOrderLog_Handle.php",
        {
            isAjax			: 1,
            act 			: 'receiver',
        },
	    function (result)
	    {
			alert(result);
	    });
	});
	//click create button
	$('#upload_btn').click(function () {
		$("#edit_modal").modal({backdrop: 'static'});
	})
})
//click purchase order number
$(".insert").livequery(function ()
{
    $(this).click(function ()
    {
        $("#detail_modal").modal({backdrop: 'static'});
        //to-do
        ponum 						= $(this).attr('flag');
        $.post("AmazonOrderLog_Handle.php",
        {
            isAjax: 				1,
            act: 					'insert',
			ponum: 					ponum,
        },
	    function (result)
	    {
	    	alert(result);
	    	try{
				$result 				= eval(result);
				data 					= $result[0].data;
		        ack 					= $result[0].ack;
	    	}catch(err){
				alert('Error: '+err.message);
			}
			
	    });
    });
});
