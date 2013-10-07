
jQuery(document).ready(function($){

	
	$('#buy.orderaction').live('click', function() {
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
        return false;
    });

    $('.cartactbtns #saveCart').on('click', function(){
        $.get(
            maginza.ajaxurl+'?'+$('#formcart').serialize(),
            {
                action:	'order',
                method: 'savecart'
            },
            function(data){
                alert('Сохранено.');
                try {
                    var data=$.parseJSON(_data);
                }
                catch (err) {
                    return;
                }

            }
        );
        return false;
    });

    $('a#sendCart').on('click', function(){
        var a=$(this);
        $.get(
            $(a).attr('href'),
            {
                test: 'test'
            },
            function(_data){
                alert('Ваш заказ отправлен на обработку.');
                window.location.href = "http://suvenirus.org/cart";
                try {
                    var data=$.parseJSON(_data);
                }
                catch (err) {
                    return;
                }

            }
        );
        return false;
    });



    $('a.btn.changeorder').on('click', function(){
        var itemID=$(this).attr('id');
        $.get(
            maginza.ajaxurl,
            {
                action:	'order',
                method: 'changeorder',
                orderitemid: itemID,
                rand: Math.random()
            },
            function(_data){

                alert('Ваш заказ перенесён в корзину. Сейчас в неё перейдём.');
                window.location.href = "http://suvenirus.org/cart";

            }
        );
        return false;
    });



});
