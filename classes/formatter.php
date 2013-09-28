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
		$val=apply_filters('mz_format_'.$args[0], '', $args);
        if ($val!='') return $val;
        //-- форматтеры по умолчанию
		switch ($templ) {
			case 'text':
				return sprintf('%s', $args[2]);							
			break;
			case 'button':
				return sprintf(get_option('mz_format_addbutton'), $args[1], $args[2], $args[2] );
			break;
            case 'price':
                return sprintf(get_option('mz_format_price'), $args[2]);
            break;
		}
	}

    /**
     *
     *
     */
	function widget($type, $metaOpt, $metaVal, $formName) {
		apply_filters('mz_widget', '',  $metaOpt, $metaVal, $formName);
        $val=apply_filters('mz_widget_'.$type, '',  $metaOpt, $metaVal, $formName);
        if ($val!='') return $val;
        //-- виджеты по умолчанию
        $name="metaoptvals[{$formName}][{$metaOpt->optName}]";

		switch ($type) {
			case 'text': 
				return '<input name="'.$name.'" id="'.$name.'" type="text" class="text" value="'.$metaVal.'" /><label for="'.$name.'">'.$metaOpt->optTitle.'</label>';
			break;
			case 'spin':
				return '<input name="'.$name.'" id="'.$name.'" type="text" class="spin" value="'.$metaVal.'" /><label for="'.$name.'">'.$metaOpt->optTitle.'</label>';
			break;
			case 'hidden':
				return '<input name="'.$name.'" id="'.$name.'" type="hidden" class="hidden meta-'.$metaOpt->optName.'" value="'.$metaVal.'" /> ';
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