jQuery(document).ready(function($){
    
	
	/*
	* Что бы при выборе категорий/характеристик родительская группа тоже выбиралась
	* а так же, при клике по родительской все дочерние выделялись/девыделялись, бля. Велик и Могучь.
	*
	*/
	function toplevel(el, val) {    
		rec++;
		if (rec>20) return false;       
		if (($(el).hasClass('children'))) {      
		  cb=$(el).parent().children('.selectit').children('input');      
		  if (val===true) { 	
		$(cb).attr('checked', true);	
		  } else {	
		if ($(el).find('input:checked').length==0) {
		   $(cb).attr('checked', false);
		}	
		  }      
		}    
		if ($(el).hasClass('list:types') || $(el).hasClass('list:features')) {      
		  return true;      
		} else {
		  return toplevel($(el).parent().closest('ul'), val);      
		}    
	}
 
  $('.selectit input[type=checkbox]').change(function(){    
    rec=0;
    val=$(this).is(':checked');
    if (!val) {
      $(this).parent().parent().find('input').attr('checked', false);
    } else {
      $(this).parent().parent().find('input').attr('checked', true);
      
    }
    toplevel($(this), val);   
  });
  
  
  
  
   
	$("a#addnewgroup").live('click', function(){
		var groupName=prompt('Enter new group name: ', '');
		if (!groupName) return false;
		$.get(ajaxurl,
			{
			  action: 'addnewgroup',
			  name: groupName
			}, function(data) {				
				location.reload();
			}, "json"	  
		);
		return false;
	});
	
	$("a#addnewmetaoption").live('click', function(){
		var optionName=prompt('Enter new option name: ', '');
		if (!optionName) return false;
		$.get(
			ajaxurl,
			{
			  action: 'addnewmetaoption',
			  name: optionName,
			  grid: $("#groupactiveid").val()
			}, function(data) {				
				location.reload();
			}, "json"	  
		);
		return false;
	});
		
	
	$("a.btnMetaOptionEdit").live('click', function(){
		$(this).parent().parent().parent().parent().find('input, select').prop('disabled', false); //-- разрешаем редактировать мета опцию.
		return false;
	});
	
	/**
	* Блок комбинаций товара
	*
	*
	*/
	
	$("#mbcombinations").on('click', 'a#addcombination', function(){
		$.get(
			ajaxurl+'?'+$("input[name^='tax_input[features]']").serialize(),
			{
				action: 'addCombination',
				lotID: $('input#post_ID').val()
			}, function(_data) {
				refreshCombList();
			}
		);	
		return false;
	});
		
	$("#mbcombinations").on('click', 'a#autogeneratecombination', function(){
		$.get(
			ajaxurl+'?'+$("input[name^='tax_input[features]']").serialize(),
			{
				action: 'autogeneratecombination',
				lotID: $('input#post_ID').val()
			}, function(_data) {
				refreshCombList();
			}
		);	
		return false;
	});
	
	
	
	$("#mbcombinations").on('click', 'a.btnCombinationDelete', function(){
		var id=$(this).attr('id');
		$.get(
			ajaxurl,
			{
				action: 'delCombination',
				combID: id
			}, function(_data) {
				refreshCombList();
			}
		);	
		return false;
	});
	
	jQuery.fn.justtext = function() {
		return $(this).clone().children().remove().end().text();
 	};
	
	$("#mbcombinations").on('click', 'a.btnCombinationEdit', function(){
		$('.combinations_list_table .curCombiEdit').removeClass('curCombiEdit');
		$(this).parents('tr').addClass('curCombiEdit');		
		$('a#addcombination').hide();
		$('a#autogeneratecombination').hide();
		$('a#combinationsave').show();		
		$('.combinations_list_table input').attr("disabled", true);
		$(this).parents('tr').find('input').attr("disabled", false);
		
		$('ul#featureschecklist li[id^="features"] input').attr('checked', false);
		var ids=$(this).parents('tr').find('.combinationIDS').justtext().split(',');
		for (var id in ids) {
			$('ul#featureschecklist li[id="features-'+ids[id]+'"] input').click();
		}

		return false;
	});
	
	
	$("#mbcombinations").on('click', 'a#combinationsave', function(){
		
		$.get(
			ajaxurl+'?'+$("input[name^='tax_input[features]']").serialize(),
			{
				action: 'editCombination',
				lotID: $('input#post_ID').val(),
				combinID: $('.combinations_list_table tr.curCombiEdit td.column-id').text(),
				article: $('.combinations_list_table tr.curCombiEdit td.column-article input').val(),
				title: $('.combinations_list_table tr.curCombiEdit td.column-title input').val()
			}, function(_data) {
				refreshCombList();
			}
		);	
		
		$('a#addcombination').show();
		$('a#autogeneratecombination').show();
		$('a#combinationsave').hide();
		$('.combinations_list_table .curCombiEdit').removeClass('curCombiEdit');
		$('.combinations_list_table input').attr("disabled", true);
		
		$('ul#featureschecklist li[id^="features"] input').attr('checked', false);
		
		return false;		
	});
	
	function refreshCombList() {
		$.get(
			ajaxurl,
			{
				action: 'refreshCombList',
				lotID: $('#post_ID').val()
			}, function(_data) {
				$('#combo-list').html(_data);
			}
		);	
		return;
	}
    
});  
  