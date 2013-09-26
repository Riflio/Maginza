<?php
/*
 * 
 *  
 */

class Formatter extends Options{

	function __construct() {}
	

	function format($templ, $param) {
		$args = func_get_args();
		apply_filters('mz_format', $args);
		apply_filters('mz_format_'.$args[0], $args);
		switch ($templ) {
			case 'text':
				return sprintf('%s', $args[2]);							
			break;
			case 'button':
				return sprintf(get_option('mz_format_addbutton'), $args[1], $args[2] );
			break;	
		}
	}
	
	function widget($type, $metaOpt) {
		$args = func_get_args();
		apply_filters('mz_widget', $args);
		apply_filters('mz_widget_'.$type, $args);
		switch ($type) { //-- виджеты по умолчанию
			case 'text': 
				return '<input name="metaoptvals['.$metaOpt->optName.']" type="text" value="'.$args[2].'" /> '.__($metaOpt->optName).'<br/>';
			break;
			case 'spin':
				return '<input name="metaoptvals['.$metaOpt->optName.']" type="text" value="'.$args[2].'" /> '.__($metaOpt->optName).'<br/>';
			break;
			case 'hidden':
				return '<input name="metaoptvals['.$metaOpt->optName.']" type="hidden" class="meta-'.$metaOpt->optName.'" value="'.$args[2].'" /> ';
			break;
		}
	}
	
}

?>