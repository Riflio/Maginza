<?php
/*
 * 
 *  
 */

class Meta extends Formatter {

	function __construct() {
		
	}

	function getGroupsList() {
		global $wpdb;
		//TODO: запоминать вывод.
		return $wpdb->get_results('SELECT * FROM '.Options::$table_meta_group.' ORDER BY groupID', OBJECT_K);
	}
	
	function getLotMetagroups($lot) {
		$curGroups = get_metadata('maginza', $lot->ID, 'metaoptiongroups', true);	
		if (!$curGroups) { //-- если у лота нет не одной группы опций, то ставим ему группы от таксономий.
			$lotTerms= wp_get_post_terms($lot->ID, 'types');
			$termGroups='-1';
			foreach ($lotTerms as $term) {
				$curTermGroups = get_metadata('maginza', $term->term_id, 'metaoptiongroups', true);	
				if (!$curTermGroups ) continue;
				$termGroups=$termGroups.','.$curTermGroups;
			}	
			$curGroups=($termGroups=='-1') ? '1' : $termGroups; //-- ну нет, дак нет, ставим дефолтное значение.
		}
		return $curGroups;
	}
	
	
	
	function getLotMetaOptions($lot) {
		global $wpdb;	
		//-- проверим, принадлежит ли нужной группе, отсортируем в порядке возрастания айдишников групп и  только уникальные названия
		$lotMetaGroups=$this->getLotMetagroups($lot);
		$metaOpts=$wpdb->get_results('SELECT DISTINCT optName, optType, optValue, optFormatter, optVisible, optClientEditable, optGroupID, optTitle FROM '.Options::$table_meta_options.' WHERE optGroupID IN ('.$lotMetaGroups.') ORDER BY optGroupID ', OBJECT);
		return $metaOpts; 
		//TODO: запомнить вывод
	}
	
	function getMetaValue($lot, $metaName) {
		$metaVal=get_metadata('maginza', $lot->ID, $metaName, true);
		$metaVal=apply_filters('getmetavalue',$metaVal, $metaName, $lot->ID);
		return $metaVal;
	}

    /**
     * Отдаём формулу расчёта стоимости позиции
     */
    public function getLotFormula($lot) {
        $groupIDS=explode(',', $this->getLotMetagroups($lot));
        $groups=$this->getGroupsList();
        foreach ($groupIDS as $groupID) { //-- возьмём первую группу, у которой не пустая формула стоимости
            $group=$groups[$groupID];
            if ($group->lotPriceFormula!='null') {
                return $group->lotPriceFormula;
            }
        }
    }


    /**
     *  проверяем и находим, присутствует ли мета опция в мета группах лота и возвращаем опцию
     *
     */
    function OptionInMetaGroups($lot, $metaName) {
		$metaOpts=$this->getLotMetaOptions($lot);
		if (!$metaOpts) return; 	 //-- нас наебали, расходимся	
		foreach ($metaOpts as $metaOpt) { //-- находим среди всех нужную
			if ($metaOpt->optName==$metaName) return $metaOpt;
		}		
		return false;
	}

    /**
     *  Проверяем переданные метаопции на наличие в метаопциях лота, и отдаём только нужные
     *
     */
    public function  checkMetaOptions($lot, $sMetaOptions) {
        $metaOptions=$this->getLotMetaOptions($lot);
        $values=array();
        foreach ($metaOptions as $metaOption) {
            if ($metaOption->optClientEditable && $metaOption->optVisible && isset($sMetaOptions[$metaOption->optName]) ) {
                $values[$metaOption->optName]=$sMetaOptions[$metaOption->optName]; //TODO:  ПРОВЕРЯТЬ!!!
            }
        }
        return $values;
    }

    /**
    * решаем, что делать с метаопцией
	*
    */
     private function processMetaOption($metaOpt, $metaVal, $formName, $echo=true) {
		if (!$metaOpt) return false; //-- нас наебали, расходимся	
		if (!$metaVal) $metaVal=$metaOpt->optValue;	
		//-- проверить, виден ли
		if (!$metaOpt->optVisible) return; 		
		//-- проверить, если редактируемый или из админки, то выводим виджет, нет - через форматтер прогоняем
		if ($metaOpt->optClientEditable || is_admin()  ) {
			$p=$this->widget($metaOpt->optType, $metaOpt, $metaVal, $formName);
		} else {
            $p=$this->format($metaOpt->optFormatter, $metaOpt, $metaVal);
		}
        if ($echo) echo $p;
        return $p;
	}
	
	//-- выводим отформатированное значение или виджет
	public function theMetaValue($lot, $metaName, $formName='', $echo=true) {
		global $wpdb;	
		$metaVal=$this->getMetaValue($lot, $metaName);		
		$metaOpt=$this->OptionInMetaGroups($lot, $metaName);
        return 	$this->processMetaOption($metaOpt, $metaVal, $formName, $echo);
	}

    /**
     * Отдаём айдишник формы, что бы знать какую правим
     *
     */
    public function metaFormID($lot) {
        return "<input type='hidden' name='lotid' id='lotid' value='{$lot->ID}'/>";
    }

    /**
	*
	*  $exclude (string) если надо исключить из вывода какой либо виджет или форматтер
	*/
	public function showMetaForm($lot, $exclude='', $formName) {
		$options=$this->getLotMetaOptions($lot);
		$exclude=explode(',', $exclude);
        echo $this->metaFormID($lot);
		if (!$options) return; 
		foreach($options as $option) {
			if (in_array($option->optName, $exclude))	continue;				
			$metaVal=$this->getMetaValue($lot, $option->optName);			
			$this->processMetaOption($option, $metaVal,  $formName);
		}		
		return true;
	}

}