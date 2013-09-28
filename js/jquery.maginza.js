
jQuery(document).ready(function($){

	
	$('.orderaction').live('click', function() {	
		var met=$(this).attr('id');
		var th=$(this);
		$.get(
			maginza.ajaxurl+'?'+$('#formorder').serialize(),
			{
				action:	'order',
                formname: 'singlelot',
				method: met,
				rand: Math.random()
			},
			function(_data){
                try {
                    var data=$.parseJSON(_data);
                }
                catch (err) {
                    return;
                }

				$('.orderMessage').html(data.msg);
				$('.orderMessage').show(1000).delay(3000).hide(1000);		
			}				
		);		
		return false;
	});


    $('.actionbtns #delete').on('click', function(){
        var itemID=$(this).parents('.orderitem').find('#orderItemID').val();
        $.get(
            maginza.ajaxurl,
            {
                action:	'order',
                method: 'deleteorderitem',
                orderitemid: itemID,
                rand: Math.random()
            },
            function(_data){
                try {
                    var data=$.parseJSON(_data);
                }
                catch (err) {
                    return;
                }
                $('.orderitem.item-'+data.orderitemid).remove();
            }
        );
    });
	

});
