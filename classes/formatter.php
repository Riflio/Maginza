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
				$s=sprintf('%s', $args[2]);							
			break;
			case 'button':
				$s=sprintf(get_option('mz_format_addbutton'), $args[3], $args[1], $args[2]);
			break;
            case 'price':
                $s=sprintf(get_option('mz_format_price'), $args[2]);
            break;
		}
		return '<span id="" class="formatter '.$templ.'" >'.$s.'</span>';
	}

    /**
     *
     *
     */
	function widget($type, $metaOpt, $metaVal, $formName, $id) {
		apply_filters('mz_widget', '',  $metaOpt, $metaVal, $formName);
        $val=apply_filters('mz_widget_'.$type, '',  $metaOpt, $metaVal, $formName);
        if ($val!='') return $val;
        //-- виджеты по умолчанию
        $name="metaoptvals[{$formName}-$id][{$metaOpt->optName}]";

        $class=(is_admin())? 'mz_widget_admin': 'mz_widget_user'; //-- Что бы разделить для скриптов и css пользовательские виджеты и из админской панели
		$class.=" {$metaOpt->optName} {$type} ";
		switch ($type) {
			case 'text': 
				return '<input name="'.$name.'" data-widget-optname="'.$metaOpt->optName.'" id="'.$id.'" type="text" class="'.$class.'" value="'.$metaVal.'" /><label for="'.$name.'">'.$metaOpt->optTitle.'</label>';
			break;
			case 'spin':
				return '<input name="'.$name.'" data-widget-optname="'.$metaOpt->optName.'" id="'.$id.'" type="text" class="'.$class.'" value="'.$metaVal.'" /><label for="'.$name.'">'.$metaOpt->optTitle.'</label>';
			break;
			case 'hidden':
				return '<input name="'.$name.'" data-widget-optname="'.$metaOpt->optName.'" id="'.$id.'" type="hidden" class="'.$class.' meta-'.$metaOpt->optName.'" value="'.$metaVal.'" /> ';
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