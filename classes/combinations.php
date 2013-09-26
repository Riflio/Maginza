<?php

require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class Combinations__List_Table extends WP_List_Table {
	var $data=array();
	
	function __construct($class) {
		parent::__construct( array(
			'singular'=> 'wp_list_text_link', //Singular label
			'plural' => $class, 
			'ajax'	=> false //We won't support Ajax for this table
		) );
	}
	
	function display_tablenav( $which ) {
		if ( 'top' == $which ) {
			echo '<div class="tablenav '.esc_attr( $which ).'"><div class="alignleft actions">';
			$this->bulk_actions(); 
			echo '</div>';
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			echo '<br class="clear" /></div>';
		}
	}	
	
	function extra_tablenav( $which ) {
		echo '
			<ul class="subsubsub">
				<li class="all">
					<a href="#" class="current">
						'.__('Total combinations').'
						<span class="count">('.count($this->items).')</span>
					</a>					
				</li>
			</ul>
		';		
	}


	function prepare_items($items, $columns) {
		$hidden = array('id', 'combinationIDS');
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $items;
	}
	
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'id':
				return $item[$column_name];
			case 'article':				
			case 'title':
				return "<input type='text' disabled=true name='combination[{$item[id]}][{$column_name}]' value='{$item[$column_name]}' />";
			case 'combination':
				return $item[$column_name];
			case 'combinationIDS':
				return $item[$column_name];
			default:
				return $item[$column_name];
		}		
	}
	
	function column_article($item) {
	  $actions = array(
				'edit'      => sprintf('<a class="btnCombinationEdit" id="%s" href="#">Edit</a>', $item['id']),
				'delete'    => sprintf('<a class="btnCombinationDelete" id="%s" href="#">Delete</a>', $item['id']),
	  );
	  return sprintf('%1$s %2$s', $this->column_default($item, 'article'), $this->row_actions($actions) );
	}
	
	function no_items() {
		_e( 'No combinations add.' );
	}

}


class Combinations {

	function __construct() {
		add_action('admin_init', array(&$this, 'admin_init'));
	}
	
	function admin_init() {
		add_action('edit_form_advanced', array(&$this, 'edit_form_advanced'), 100, 1);
		add_meta_box('mbcombinations', __('Lot combinations'), array(&$this, 'showCombinsBox'), 'lots', 'advanced',  'core', '');
		add_action('wp_ajax_addCombination', array(&$this, 'ajax_addCombination'));
		add_action('wp_ajax_editCombination', array(&$this, 'ajax_addCombination'));
		add_action('wp_ajax_autogeneratecombination', array(&$this, 'ajax_addCombination'));
		add_action('wp_ajax_delCombination', array(&$this, 'ajax_delCombination'));
		add_action('wp_ajax_refreshCombList', array(&$this, 'ajax_refreshCombList'));
	}
	
	function init() {
	
	}
	
	
	/**
	* Отдаём клиентскую форму выбора характеристик. всех, которые есть в комбинациях.
	*
	*
	*/
	function clientFeaturesForm() {
		global $wpdb;
			
	
	}


	
	/**
	* Сохраняяем все параметры бокса комбинаций товара
	*
	*/
	function edit_form_advanced($post) {
		global $wpdb;		
		
	}
	
	/*
	* Отдаём список комбинаций товара
	*
	*/	
	
	public function getCombinationList($lotID) {
		global $wpdb;
		$items=array();
		
		$table_combinations=Options::$table_combinations;
		$table_combinations_rel=Options::$table_combinations_rel;
		
		//-- получим все комбинации товара вместе с айдишниками зависимостей названий
		$qCombinations=$wpdb->get_results($wpdb->prepare(
			"SELECT comb.*, GROUP_CONCAT(rels.combinRelID SEPARATOR ',' ) as relsID
			FROM  {$table_combinations} as comb
			JOIN {$table_combinations_rel} as rels ON rels.combinRelCombinID=comb.combinID 
			WHERE comb.lotID=%d
			GROUP BY comb.combinID
			", $lotID
		));
		
		$relIDs='-1'; //-- сохраним все айдишники таблицы зависимостей названий характеристик и группы
		foreach ($qCombinations as $comb) {
			$items[]=array('id'=>$comb->combinID, 'article'=>$comb->combinArticle, 'title'=>$comb->combinTitle,  'combination'=>$comb->relsID);
			$relIDs.=(','.$comb->relsID);
		}		
		
		//--получим все названия характеристик и название их группы по айдишникам
		$qNames=$wpdb->get_results($wpdb->prepare(
			"
			SELECT rels.combinRelID, rels.combinRelItemsID, 
			GROUP_CONCAT(DISTINCT termsItems.name  SEPARATOR ',' ) as GroupFeatures, 
			GROUP_CONCAT(DISTINCT rels.combinRelItemsID  SEPARATOR ',' ) as GroupFeaturesIDs, 
			termsGroups.name as GroupName, rels.combinRelGroupId as GroupID
			FROM  {$table_combinations_rel} as rels
			JOIN  {$wpdb->terms} as termsGroups ON  termsGroups.term_id = rels.combinRelGroupId 
			JOIN  {$wpdb->terms} as termsItems ON  FIND_IN_SET(termsItems.term_id, rels.combinRelItemsID )
			WHERE   FIND_IN_SET (rels.combinRelID, %s)  
			GROUP BY rels.combinRelID
			", $relIDs
		), OBJECT_K); //-- что бы первый столбец запроса был айдишником в массиве
		
		//-- раскидаем названия вместо айдишников
		foreach ($items as $key =>  $item) {
			$ids=explode(',', $items[$key]['combination']);
			$items[$key]['combination']='';
			foreach ($ids as $id) {
				$items[$key]['combination'].="<b>{$qNames[$id]->GroupName}: </b> {$qNames[$id]->GroupFeatures}</br>";
				$items[$key]['combinationIDS']=$qNames[$id]->GroupFeaturesIDs;				
			}	
			
		}
		
		return $items;	
	}
	
	/*
	* Показываем бокс с комбинациями товара
	*
	*/	
	function showCombinsBox( $post ) {
		echo '<div class="combo-descr">'.__('Combo, blyat').'</div>';
		
		echo '
			<div class="combo-actions">
				<a href="#" id="addcombination" class="button">'.__('Add combination').'</a>
				<a href="#" id="autogeneratecombination" class="button">'.__('Auto generate combinations').'</a>
				<a href="#" id="combinationsave" class="button" style="display: none;" >'.__('Save combination!').'</a>
			</div>
		';
		
		$list=new Combinations__List_Table('combinations_list_table');
		$list->prepare_items($this->getCombinationList($post->ID), $this->get_columns()); 
		
		echo  '<div id="combo-list">';
			$list->display();
		echo '</div>';
		
		
	}	
	
	/**
	*  Отдаём список колонок
	*
	*/	
	
	function get_columns() {
		$columns = array(
			'id'		=> __('id'),
			'article' 	=> __('Article'),			
			'title'		=> __('Title'),
			'combination'=>__('Combination'),
			'combinationIDS'=>__('combinationIDS')
		);
		$columns=apply_filters('mzcombinations_getcolumns', $columns);
		return $columns;
	}
	
	
	/*
	* Раскидываем характеристики по группам
	*
	*
	*/	
	function featuresbygroups($features) {
		global $wpdb;
		
		$qterms=$wpdb->get_results("SELECT * FROM {$wpdb->term_taxonomy} WHERE taxonomy='features' AND term_id IN ({$features})");
		$combirel=array();
		foreach($qterms as $term) {
			if ($term->parent!=0) {
				$combirel[$term->parent][]=$term->term_id;
			}	
		}
		return $combirel;	
	}
	
	
	/*
	* Обновляем список комбинаций по хуякс запросу
	*
	*
	*/
	function ajax_refreshCombList() {
		$lotID=intval($_GET['lotID']);
		$list=new Combinations__List_Table('combinations_list_table');
		$list->prepare_items($this->getCombinationList($lotID), $this->get_columns()); 
		
		$list->display();
		
		die();
	}
	
	
	/*
	* Создаём все возможные комбинации из перечня характеристик
	*
	*
	*/	
	function recGen($lotID, $arrkeys, $features, $groupid, &$genInterration) {		
		foreach ($features[$arrkeys[$groupid]] as $feature) {
			$genInterration[$arrkeys[$groupid]]=array($feature);
			if ( $groupid+1>=count($features)) {	
				$this->addCombination($lotID, $genInterration);	
			} else {
				$this->recGen($lotID, $arrkeys, $features, $groupid+1, $genInterration);			
			}		
		}		
	}	
	
	/**
	* Добавим новую комбинацию
	*
	*
	*/
	function addCombination($lotID, $combinFeatures) {
		global $wpdb;
		//--
		$wpdb->insert(Options::$table_combinations, array('lotID'=>$lotID, 'combinTitle'=>'New title', 'combinArticle'=>'0'), array('%d','%s','%s'));
		$combinID=$wpdb->insert_id;		
		//-- 
		foreach($combinFeatures as $key => $rel) {
			$wpdb->insert(Options::$table_combinations_rel, array('combinRelGroupId'=>$key, 'combinRelCombinID'=> $combinID, 'combinRelItemsID'=>implode($rel, ',')), array('%d', '%d','%s'));
		}
	}
	
	/*
	*
	*
	*
	*/
	function editCombination($lotID, $combinID, $article, $title, $features) {
		global $wpdb;
		$wpdb->update(Options::$table_combinations, array('combinTitle'=> $title, 'combinArticle'=>$article), array('combinID'=>$combinID), array('%s', '%s'), array('%d'));
		$wpdb->delete(Options::$table_combinations_rel, array('combinRelCombinID'=>$combinID), array('%d'));
		foreach($features as $key => $rel) {
	        $wpdb->insert(Options::$table_combinations_rel, array('combinRelGroupId'=>$key, 'combinRelCombinID'=> $combinID, 'combinRelItemsID'=>implode($rel, ',')), array('%d', '%d','%s'));
		}
	}
	
	/*
	* Удаляем комбинацию и заодно и отношения к названиям
	*
	*
	*/
	function ajax_delCombination() {
		global $wpdb;
		$combID=intval($_GET['combID']);
		$wpdb->delete(Options::$table_combinations, array('combinID'=>$combID), array('%d'));
		$wpdb->delete(Options::$table_combinations_rel, array('combinRelCombinID'=>$combID), array('%d'));	
		die();
	}

	/**
	* Добавляем/редактируем комбинацию в базу по хуякс запросу
	*
	*
	*/ 
	function ajax_addCombination() {
		$action=$_GET['action'];
		$features=$_GET['tax_input'];
		$lotID=intval($_GET['lotID']);
		$features=$features['features'];
		$sfeatures=implode($features, ',');
		
		$combinFeatures=$this->featuresbygroups($sfeatures);

		if ($action=="addCombination")
			$this->addCombination($lotID, $combinFeatures);
		if ($action=="autogeneratecombination") 
			$this->recGen($lotID, array_keys($combinFeatures), $combinFeatures, 0, $a=array());
		if ($action=="editCombination") 	
			$this->editCombination($lotID, intval($_GET['combinID']), $_GET['article'], $_GET['title'], $combinFeatures);
		die();
	}
	
} 