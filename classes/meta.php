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
		//TODO: ���������� �����.
		return $wpdb->get_results('SELECT * FROM '.Options::$table_meta_group);
	}
	
	function getLotMetagroups($lot) {
		$curGroups = get_metadata('maginza', $lot->ID, 'metaoptiongroups', true);	
		if (!$curGroups) { //-- ���� � ���� ��� �� ����� ������ �����, �� ������ ��� ������ �� ����������.
			$lotTerms= wp_get_post_terms($lot->ID, 'types');
			$termGroups='-1';
			foreach ($lotTerms as $term) {
				$curTermGroups = get_metadata('maginza', $term->term_id, 'metaoptiongroups', true);	
				if (!$curTermGroups ) continue;
				$termGroups=$termGroups.','.$curTermGroups;
			}	
			$curGroups=($termGroups=='-1') ? '1' : $termGroups; //-- �� ���, ��� ���, ������ ��������� ��������.
		}
		return $curGroups;
	}
	
	
	
	function getLotMetaOptions($lot) {
		global $wpdb;	
		//-- ��������, ����������� �� ������ ������, ����������� � ������� ����������� ���������� ����� �  ������ ���������� ��������
		$lotMetaGroups=$this->getLotMetagroups($lot);
		$metaOpts=$wpdb->get_results('SELECT DISTINCT optName, optType, optValue, optFormatter, optVisible, optClientEditable, optGroupID FROM '.Options::$table_meta_options.' WHERE optGroupID IN ('.$lotMetaGroups.') ORDER BY optGroupID ', OBJECT);	
		return $metaOpts; 
		//TODO: ��������� �����
	}
	
	private function getMetaValue($lot, $metaName) {
		$metaVal=get_metadata('maginza', $lot->ID, $metaName, true);	
		$metaVal=apply_filters('getmetavalue',$metaVal, $metaName, $lot->ID);
		return $metaVal;
	}
	
	//-- ��������� � �������, ������������ �� ���� ����� � ���� ������� ���� � ���������� �����
	function OptionInMetaGroups($lot, $metaName) {
		$metaOpts=$this->getLotMetaOptions($lot);
		if (!$metaOpts) return; 	 //-- ��� �������, ����������	
		foreach ($metaOpts as $metaOpt) { //-- ������� ����� ���� ������
			if ($metaOpt->optName==$metaName) return $metaOpt;
		}		
		return false;
	}
	
	//-- ������, ��� ������ � ����������
	private function processMetaOption($metaOpt, $metaVal) {
		if (!$metaOpt) return false; //-- ��� �������, ����������	
		if (!$metaVal) $metaVal=$metaOpt->optValue;	
		//-- ���������, ����� ��
		if (!$metaOpt->optVisible) return; 		
		//-- ���������, ���� ������������� ��� �� �������, �� ������� ������, ��� - ����� ��������� ���������
		if ($metaOpt->optClientEditable || is_admin()  ) {
			echo $this->widget($metaOpt->optType, $metaOpt, $metaVal);
		} else {
			echo $this->format($metaOpt->optFormatter, $metaOpt, $metaVal);
		}
	}
	
	//-- ������� ����������������� �������� ��� ������
	public function theMetaValue($lot, $metaName) {
		global $wpdb;	
		$metaVal=$this->getMetaValue($lot, $metaName);		
		$metaOpt=$this->OptionInMetaGroups($lot, $metaName);		
		$this->processMetaOption($metaOpt, $metaVal);		
		return true;		
	}
	
	/**
	*
	*  $exclude (string) ���� ���� ��������� �� ������ ����� ���� ������ ��� ���������
	*/
	public function showMetaForm($lot, $exclude='') {
		global $wpdb;			
		$options=$this->getLotMetaOptions($lot);
		$exclude=explode(',', $exclude);
		//-- � ����� ������ ������� �������� ����
		echo "<input type='hidden' name='lotid' id='lotid' value='{$lot->ID}'/>";
		if (!$options) return; 
		foreach($options as $option) {
			if (in_array($option->optName, $exclude))	continue;				
			$metaVal=$this->getMetaValue($lot, $option->optName);			
			$this->processMetaOption($option, $metaVal);
		}		
		return true;
	}

}