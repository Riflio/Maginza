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
		return $wpdb->get_results('SELECT * FROM '.Options::$table_meta_group);
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
     *
     *
     */
    function setMetaOrderValues() {

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
    * решаем, что делать с метаопцией
	*
    */
     private function processMetaOption($metaOpt, $metaVal, $formName) {
		if (!$metaOpt) return false; //-- нас наебали, расходимся	
		if (!$metaVal) $metaVal=$metaOpt->optValue;	
		//-- проверить, виден ли
		if (!$metaOpt->optVisible) return; 		
		//-- проверить, если редактируемый или из админки, то выводим виджет, нет - через форматтер прогоняем
		if ($metaOpt->optClientEditable || is_admin()  ) {
			echo $this->widget($metaOpt->optType, $metaOpt, $metaVal, $formName);
		} else {
			echo $this->format($metaOpt->optFormatter, $metaOpt, $metaVal);
		}
	}
	
	//-- выводим отформатированное значение или виджет
	public function theMetaValue($lot, $metaName, $formName='') {
		global $wpdb;	
		$metaVal=$this->getMetaValue($lot, $metaName);		
		$metaOpt=$this->OptionInMetaGroups($lot, $metaName);		
		$this->processMetaOption($metaOpt, $metaVal, $formName);
		return true;		
	}
	
	/**
	*
	*  $exclude (string) если надо исключить из вывода какой либо виджет или форматтер
	*/
	public function showMetaForm($lot, $exclude='', $formName) {
		global $wpdb;			
		$options=$this->getLotMetaOptions($lot);
		$exclude=explode(',', $exclude);
		//-- в любом случае добавим айдишник лота
		echo "<input type='hidden' name='lotid' id='lotid' value='{$lot->ID}'/>";
		if (!$options) return; 
		foreach($options as $option) {
			if (in_array($option->optName, $exclude))	continue;				
			$metaVal=$this->getMetaValue($lot, $option->optName);			
			$this->processMetaOption($option, $metaVal,  $formName);
		}		
		return true;
	}

}