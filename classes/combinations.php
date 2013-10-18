<?php

require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');


class Combinations extends Meta{

	function __construct() {
		add_action('admin_init', array(&$this, 'admin_init'));
	}
	
	function admin_init() {
		add_action('save_post', array(&$this, 'save_post'), 100, 1);

		add_meta_box('mbcombinations', __('Lot combinations'), array(&$this, 'showCombinsBox'), 'lots', 'advanced',  'core', '');
		add_action('wp_ajax_addCombination', array(&$this, 'ajax_addCombination'));
		add_action('wp_ajax_editCombination', array(&$this, 'ajax_addCombination'));
		add_action('wp_ajax_autogeneratecombination', array(&$this, 'ajax_addCombination'));
		add_action('wp_ajax_delCombination', array(&$this, 'ajax_delCombination'));
		add_action('wp_ajax_refreshCombList', array(&$this, 'ajax_refreshCombList'));

        add_action('wp_ajax_whatcombination', array(&$this, 'ajax_whatcombination'));
        add_action('wp_ajax_nopriv_whatcombination', array(&$this, 'ajax_whatcombination'));
	}
	
	function init() {
	
	}
	
	
	/**
	* Отдаём клиентскую форму выбора характеристик. всех, которые есть в комбинациях.
	*
	*
	*/
	public function clientCombFeaturesForm($lotID, $formName) {
        $rels=$this->getLotAllFeatures($lotID, 'combinRelGroupId');
        foreach ($rels as $rel) {
            echo Formatter::combFeature($rel, $formName);
        }
    }

    /**
    * Отгадываем комбинацию по характеристикам
    *
    */
    public function whatCombination($features, $lotID) {
        $combins=$this->getCombinationList($lotID);
        $features=array_diff($features, array(''));
        $fCount=count($features);
        $features=implode('|', $features);
        $res=array();
        foreach ($combins as $comb) {
            //--находим какой либо из переданных айдишников в айдишниках комбинации и считаем количество совпадений
            $m=preg_match_all('/('.$features.')/i', $comb['combinationIDS'], $res);
            if ($m==$fCount) return $comb; //-- совпало сколько передали - значит точно нужная комбинация
        }
        return false;
    }


    /**
    *  Отгадываем комбинацию по хуякс запросу
    *
    */
    function ajax_whatcombination() {
        $features=Formatter::reqCombFeature($_GET['formname']);
        if (!$features) return;
        $lotID=intval($_GET['lotid']);
        echo json_encode($this->whatCombination($features, $lotID));
        die();
    }


    /**
	* Сохраняяем все параметры бокса комбинаций товара
	*
	*/
	function save_post($post) {
		global $wpdb;
        if (isset($_POST['combination'])) {
            $combinations=$_POST['combination'];
            foreach ($combinations as $combID => $combItems) {
                $title=$combItems['title'];
                $article=$combItems['article'];
                $wpdb->update(Options::$table_combinations, array('combinTitle'=>$title, 'combinArticle'=>$article), array('combinID'=>$combID), array('%s', '%s'), array('%d'));
            }
        }
	}

    /**
     * получим все названия характеристик и название их группы для всех комбинаций лота
     *
     */
    private function getLotAllFeatures($lotID, $orderBy='combinRelID') {
        global $wpdb;
        $table_combinations_rel=Options::$table_combinations_rel;
        $qRels=$wpdb->get_results($wpdb->prepare(
            "
              SELECT  rels.combinRelID, rels.combinRelCombinID, rels.combinRelGroupId
              ,GROUP_CONCAT(DISTINCT termsItems.term_id  SEPARATOR ',' ) as GroupFeaturesIDS
              ,GROUP_CONCAT(DISTINCT termsItems.name  SEPARATOR ',' ) as GroupFeatures
              ,termsGroup.name as GroupName
              FROM {$table_combinations_rel} as rels
              JOIN {$wpdb->terms} as termsItems ON FIND_IN_SET(termsItems.term_id, rels.combinRelItemsID )
              JOIN {$wpdb->terms} as termsGroup ON termsGroup.term_id=rels.combinRelGroupId
              WHERE rels.combinRelCombinID IN (SELECT combinID FROM wp_maginza_combinations WHERE lotID=%d)
              GROUP BY rels.{$orderBy}
			", $lotID
        ), OBJECT_K); //-- что бы первый столбец запроса был айдишником в массиве

        return $qRels;
    }

    /**
     *  Отдаём конкретную комбинацию
     */
    public function getCombination($lotID, $combID) {
        $combs=$this->getCombinationList($lotID);
        return $combs[$combID];
    }

    /**
    * Отдаём список комбинаций товара
    *
    */
    public function getCombinationList($lotID) {
		global $wpdb;
		$items=array();
    	$table_combinations=Options::$table_combinations;
		//-- получим все комбинации товара
		$qCombinations=$wpdb->get_results($wpdb->prepare(
			"SELECT comb.*
			FROM  {$table_combinations} as comb
			WHERE comb.lotID=%d
			", $lotID
		));
		//-- На выход добавим все комбинации
		foreach ($qCombinations as $comb) {
			$items[$comb->combinID]=array('id'=>$comb->combinID, 'article'=>$comb->combinArticle, 'title'=>$comb->combinTitle, 'combination'=>'', 'combinationIDS'=>'-1' );
		}
        //-- На выход раскидаем названия характеристик и названия групп по комбинациям
        $qRels=$this->getLotAllFeatures($lotID, 'combinRelID');
        foreach ($qRels as $key => $rel) {
            $items[$rel->combinRelCombinID]['combination'].="<b>{$rel->GroupName}: </b> {$rel->GroupFeatures}  </br>";
            $items[$rel->combinRelCombinID]['combinationIDS'].=",{$rel->GroupFeaturesIDS}";
        }
		return $items;
	}
	
	/**
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
				<a href="#" id="combineditinputs"  class="button">'.__('Edit Titles and Articles').'</a>
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
			'combinationIDS'=>__('combinationIDS'),
            'isdefault'=>__('Default')
		);
		$columns=apply_filters('mzcombinations_getcolumns', $columns);
		return $columns;
	}
	
	
	/**
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
	
	
	/**
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
	
	
	/**
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
	function addCombination($lotID, $combinFeatures, $title='New title') {
		global $wpdb;
		//--
		$wpdb->insert(Options::$table_combinations, array('lotID'=>$lotID, 'combinTitle'=>$title, 'combinArticle'=>'0'), array('%d','%s','%s'));
		$combinID=$wpdb->insert_id;		
		//-- 
		foreach($combinFeatures as $key => $rel) {
			$wpdb->insert(Options::$table_combinations_rel, array('combinRelGroupId'=>$key, 'combinRelCombinID'=> $combinID, 'combinRelItemsID'=>implode($rel, ',')), array('%d', '%d','%s'));
		}

        $wpdb->update(Options::$table_combinations, array('combinArticle'=>$combinID), array('combinID'=>$combinID), array('%d'), array('%d'));
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
	
	/**
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
            case 'article':
                return $item[$column_name];
            case 'title':
                return "<input type='text' disabled=true name='combination[{$item[id]}][{$column_name}]' value='{$item[$column_name]}' />";
            case 'combination':
                return $item[$column_name];
            case 'combinationIDS':
                return $item[$column_name];
            case 'isdefault':
                $check=($item[$column_name]==true)? 'checked' : '';
                return "<input type='checkbox' disabled=true {$check} name='combination[{$item[id]}][{$column_name}]' "" />";
            default:
                return $item[$column_name];
        }
    }

    function column_article($item) {
        $actions = array(
            'edit'      => sprintf('<a class="btnCombinationEdit" id="%s" href="#">Edit</a>', $item['id']),
            'delete'    => sprintf('<a class="btnCombinationDelete" id="%s" href="#">Delete</a>', $item['id']),
            'save'      => sprintf('<a class="btnCombinationSave" id="%s" href="#">Save</a>', $item['id']),
        );
        return sprintf('%1$s %2$s', $this->column_default($item, 'article'), $this->row_actions($actions) );
    }

    function no_items() {
        _e( 'No combinations add.' );
    }

}
