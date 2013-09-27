<?php
/**
 *  Всё, что выводится пользователю магинзой проходит через этот класс.
 *  
 */

class Formatter extends Options{

	function __construct() {}

    /**
     *
     *
     */
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

    /**
     *
     *
     */
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

    /**
     * Выводим списки характеристик комбинаций
     *
     */
    function combFeature($rel, $lotID) {
        //TODO: Добавить фильтр или событие для кастомизации списков
        $feature="<div class='featureslist  feature-{$rel->combinRelGroupId}'><b>{$rel->GroupName}:</b><br/>";
        $IDS=explode(',', $rel->GroupFeaturesIDS);
        $Names=explode(',', $rel->GroupFeatures);
        for ($i=0; $i<count($IDS); $i++) {
            $feature.="<a id='fid-{$IDS[$i]}' class='' href='#'>{$Names[$i]}</a>, ";
        }
        $feature.='</div>';
        $feature.="<input type='hidden' name='feature[{$rel->combinRelGroupId}]'>";
        return $feature;
    }
	
}

?>