$(document).ready(function () {
	$('#search_btn').click(function () {
		addressCode 			=$('#addressCode').val();
		$.post("AddressMapping_Handle.php",
            {
                isAjax			: 1,
                act 			: 'search',
				addressCode 	: addressCode,
            },
	    function (result)
	    {
			//alert(result);
			try {
				$result 		= eval(result);
				data 			= $result[0].data;
		        ack 			= $result[0].ack; 
		        if (ack =='1') {
		        	$('#QueryTable').bootstrapTable('destroy');
			        $('#QueryTable').bootstrapTable({
						columns: $result[0].columns,
			            data: data
			        });
		        }else{
		        	 msg 		= $result[0].msg;
		        	//alert(msg);
		        }
			}catch(err){
				alert('error');
			}
	    });
	});
})

