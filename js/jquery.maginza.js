
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

    $('.cartactbtns #saveCart').on('click', function(){
        $.get(
            maginza.ajaxurl+'?'+$('#formcart').serialize(),
            {
                action:	'order',
                method: 'savecart'
            },
            function(data){
                alert(data);
                alert('Сохранено.');
                try {
                    var data=$.parseJSON(_data);
                }
                catch (err) {
                    return;
                }

            }
        );
    });

    $('.cartactbtns #sendCart').on('click', function(){
        $.get(
            maginza.ajaxurl,
            {
                action:	'order',
                method: 'sendCart',
                cartorderid: $('#cartOrderID').val()
            },
            function(_data){
                try {
                    var data=$.parseJSON(_data);
                }
                catch (err) {
                    return;
                }
                alert('Ваш заказ отправлен на обработку.');
            }
        );
    });

});
