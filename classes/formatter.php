<?php
/**
 *  Всё, что выводится и вводится  пользователем магинзы проходит через этот класс.
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
	function widget($type, $metaOpt, $metaVal, $formName) {
		$args = func_get_args();
		apply_filters('mz_widget', $args);
		switch ($type) { //-- виджеты по умолчанию
			case 'text': 
				return '<input name="metaoptvals['.$formName.']['.$metaOpt->optName.']" type="text" value="'.$metaVal.'" /> '.__($metaOpt->optName).'<br/>';
			break;
			case 'spin':
				return '<input name="metaoptvals['.$formName.']['.$metaOpt->optName.']" type="text" value="'.$metaVal.'" /> '.__($metaOpt->optName).'<br/>';
			break;
			case 'hidden':
				return '<input name="metaoptvals['.$formName.']['.$metaOpt->optName.']" type="hidden" class="meta-'.$metaOpt->optName.'" value="'.$metaVal.'" /> ';
			break;
            default:
                return apply_filters('mz_widget_'.$type, $val, $args);
            break;

		}
	}

    /**
    *
    *
    */
    function reqMetaOptpValue($formName) {
        if (isset($_REQUEST['metaoptvals'])) {
            $vals=$_REQUEST['metaoptvals'];
            $vals=$vals[$formName];
            return $vals;
        } else {
            return false;
        }
    }

    /**
     * Выводим списки характеристик комбинаций
     *
     */
    function combFeature($rel, $formName) {
        //TODO: Добавить фильтр или событие для кастомизации списков
        $feature="<div class='featureslist  feature-{$rel->combinRelGroupId}'><b>{$rel->GroupName}:</b><br/>";
        $IDS=explode(',', $rel->GroupFeaturesIDS);
        $Names=explode(',', $rel->GroupFeatures);
        for ($i=0; $i<count($IDS); $i++) {
            $feature.="<a id='fid-{$IDS[$i]}' class='' href='#'>{$Names[$i]}</a>, ";
        }
        $feature.="<input type='hidden' class='feature' name='feature[{$formName}][{$rel->combinRelGroupId}]'>";
        $feature.='</div>';
        return $feature;
    }

    function reqCombFeature($formName) {
        if (isset($_REQUEST['feature'])) {
            $vals=$_REQUEST['feature'];
            $vals=$vals[$formName];
            return $vals;
        } else {
            return false;
        }
    }

}

?>