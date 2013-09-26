
jQuery(document).ready(function($){

	
	$('.orderaction').live('click', function() {	
		var met=$(this).attr('id');
		var th=$(this);
		$.get(
			maginza.ajaxurl+'?'+$('#formorder').serialize(),
			{
				action:	'order',
				method: met,
				rand: Math.random()
			},
			function(data){
				//data=$.parseJSON(data);	
				$('.orderMessage').html(data);
				$('.orderMessage').show(1000).delay(3000).hide(1000);		
			}				
		);		
		return false;
	});
	
	

});
