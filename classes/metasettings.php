<?php 
/*
 * 
 *  
 */
 
class MetaSettings extends Meta {
	private $columns;
	private $metaTypes;
	private $metaFormatters;
	
	function __construct() {
		add_action('admin_init', array(&$this, 'admin_init'));
		
		add_action('edit_tag_form_fields', array($this, 'category_form_fields'), 10, 2);
		add_action('edited_term', array($this, 'edited_term_taxonomies'), 10, 2);
		
		add_action('save_post', array($this, 'save_box_metagroupsandvalues'));
		
		add_action('wp_ajax_addnewgroup', array(&$this, 'ajax_addnewgroup'));
		add_action('wp_ajax_addnewmetaoption', array(&$this, 'ajax_addnewmetaoption'));

		$this->columns=array(
			'name'=> __('Name'),
			'type'=> __('Type'),
			'value'=> __('Value'),
			'formatter'=>__('Formatter'),
			'visible'=> __('Visible'),
			'clienteditable'=> __('Client editable')			
		);		
		$this->metaTypes=array(
			array('text', 'Текст'),
			array('spin', 'Числовой'),
			array('hidden', 'Скрытый')
		);
		$this->metaFormatters=array(
			array('text','Text'),
			array('number', 'Number'),
			array('hidden', 'Hidden'),
            array('price', 'Price')
		);
		
		
		$this->metaTypes=apply_filters('mz_metaoptions_metatypes', $this->metaTypes);
		$this->metaFormatters=apply_filters('mz_metaoptions_metaformatters', $this->metaFormatters);
		
		//TODO: доделать удаление настройки
		//TODO: доделать удаление группы 
	}
	
	function admin_init() {				
		add_meta_box("lotmetavalues", __( 'Lot meta values'), array($this, 'render_box_lotmetavalues'), 'lots', 'side', 'high');
		add_meta_box("lotmetagroups", __( 'Lot meta groups'), array($this, 'render_box_lotmetagroups'), 'lots', 'side', 'high');
		
	}
	
	function showForm() {
		global $wpdb;		
		if ($_POST['action']=="updatemetaoptions") { //-- если пришла команда на сохранение изменённых настроек
			$this->updateMetaOptions();
			wp_redirect($_POST['_wp_http_referer']);
		}
		//-- покажем табы - группы опций
		$optSection=isset($_GET['tab'])? $_GET['tab'] : 'Default';		
		$groupActive;
		$tabs=array();
		$groups=$this->getGroupsList();
		foreach ($groups as $group) {		
			if ($group->groupName==$optSection) { $groupActive=$group; }
			$tabs[]=array($group->groupName, $group->groupTitle);
		}				
		echo '
			<div class="wrap">  
				<div id="icon-themes" class="icon32"></div>  
				<h2>'.__('Lot meta options').'</h2>  
				<h2 class="nav-tab-wrapper"> 				
		';		
		foreach ($tabs as $tab) {
			$isTabActive=($tab[0]==$optSection)? 'nav-tab-active' : '';
			echo "<a href='?page=mz_metasettings&tab={$tab[0]}' class='nav-tab {$isTabActive}'> {$tab[1]} </a> ";
		}		
		echo'	
				<a class="add-new-h2" id="addnewgroup" href="">'.__('Add new meta group').'</a>
				</h2>			
		';
		//-- покажем в табах настройки		
		$settingsListTable = new MetaSettings__List_Table('settings_list_table');		
		$settingsf=$wpdb->get_results('SELECT * FROM '.Options::$table_meta_options.' WHERE optGroupID='.$groupActive->groupID);		
			
		$settingsListTable->prepare_items($settingsf, $this->columns, $this->metaTypes, $this->metaFormatters); 
		
		echo '	
				<div class="metaoptionslist">
					<form method="POST" action="">
						<input name="action" type="hidden" value="updatemetaoptions" />
						<input name="groupactiveid" id="groupactiveid" type="hidden" value="'.$groupActive->groupID.'" />
		';		
						wp_referer_field();
						$settingsListTable->display();
						
						echo '
							<table class="form-table">
								<tr>
									<th>
										<a class="add-new-h2" id="addnewmetaoption" href="">'.__('Add new meta option').'</a>
									</th>
									<td></td>
								</tr>
								<tr> 
									<th>'.__('Lot price formula').'</th>
									<td><input type="text" name="lotPriceFormula" size="100" value="'.htmlspecialchars($groupActive->lotPriceFormula).'" /></td>
								</tr>									
							</table>
						';  
						
						submit_button();
						
		echo '		
					</form>
				</div>
			</div>
		';
	}	
	

	
	function ajax_addnewgroup() {
		global $wpdb;
		$groupName=$_GET['name'];
		$wpdb->insert(Options::$table_meta_group, array('groupName'=>$groupName, 'groupTitle'=>$groupName,  'lotPriceFormula'=>'{price}'), array('%s', '%s'));
		die();
	}
	
	function ajax_addnewmetaoption() {
		global $wpdb;	
		$optionName=$_GET['name'];
		$grID=intval($_GET['grid']);
		$wpdb->insert(Options::$table_meta_options, 
			array(
				'optGroupID'=>$grID, 
				'optName'=>$optionName, 
				'optType'=>'text', 
				'optValue'=>'',
				'optFormatter'=>'',
				'optVisible'=>1, 
				'optClientEditable'=>1  
			), 
			array('%d', '%s', '%s', '%s', '%s', '%d', '%d')
		); 
		die();
	}
	
	function updateMetaOptions() {
		global $wpdb;
		$options=$_POST['metaoption']; //TODO: checkit!
		//-- обновляем опции
		$errCode=0;
		foreach ($options as $key => $option) {
			$errCode=$wpdb->update(Options::$table_meta_options, 
				array(
					'optType'=>$option['type'], 
					'optValue'=>$option['value'], 
					'optFormatter'=>$option['formatter'],
					'optVisible'=>intval($option['visible']), 
					'optClientEditable'=>intval($option['editable']) 
				), 
				array('id'=>$key), 
				array('%s', '%s', '%s', '%d', '%d'), 
				array('%d') 
			);
		}
		//-- обновляем формулу цены
		$mid=intval($_POST['groupactiveid']);
		$formula=$_POST['lotPriceFormula'];
		$errCode=$wpdb->update(Options::$table_meta_group,
			array('lotPriceFormula'=>$formula),
			array('groupID'=>$mid),
			array('%s'),
			array('%d')
		);	
	}
	
	function listGroupsSettings($curGroups, $echo=true) {	
		$curGroups=(!$curGroups)? array('1') : split(',', $curGroups);	
		$groups=$this->getGroupsList();
		$listGroups='';
		foreach ($groups as $group) {
			$checked=(in_array($group->groupID, $curGroups)) ? 'checked' : '';
			$listGroups.='<input type="checkbox" '.$checked.' style="width:10px; margin:0 5px 0 15px;" name="metaoptiongroups[]" value="'.$group->groupID.'" /> '.$group->groupTitle.' ';
		}		
		
		if ($echo) {
			echo '
				<tr class="form-field">
					<th scope="row" valign="top"><label for="">'.__("Meta option groups").'</label></th>
					<td>
					'.$listGroups.'	
					</td>
				</tr>
			';
		} 
		
		return $listGroups;
		
	}
	

    /**
     *  Добавляем на страницу редактирования категории товаров выбор групп метаопций
     *
     */
    function category_form_fields($tag) {
		$curGroups = get_metadata('maginza', $tag->term_id, 'metaoptiongroups', true);			
		$this->listGroupsSettings($curGroups, true);
	}
	
	
	/**
    *
    *
    */
	function edited_term_taxonomies($term_id) {
		if (!$term_id) return;
		$metaGroups=(isset($_POST['metaoptiongroups']))? $_POST['metaoptiongroups'] : false;
		if (!$metaGroups) return;
		update_metadata('maginza', $term_id, 'metaoptiongroups', implode(',', $metaGroups));		
	}
	
	function render_box_lotmetavalues($lot) {
		echo '<div class="lotmetavalues">';
		$this->showMetaForm($lot, '',  'adminpostform');
		echo '</div>';
	}
	
	function render_box_lotmetagroups($lot) {		
		$curGroups=$this->getLotMetagroups($lot);		
		$list=$this->listGroupsSettings($curGroups, false);
		echo '
			<div class="lotmetagroups">
				'.$list.'	
			</div>		
		';
	}
	
	function save_box_metagroupsandvalues($lotId) {
		if (!$lotId) return;

		//--обновляем список активных групп опций у лота		
		$metaGroups=(isset($_POST['metaoptiongroups']))? $_POST['metaoptiongroups'] : false;
		if(!$metaGroups) return;
		update_metadata('maginza', $lotId, 'metaoptiongroups', implode(',', $metaGroups));		


		//--обновляем значения опций
		$metaOptions=Formatter::reqMetaOptpValue('adminpostform');
		if(!$metaOptions) return;
		foreach($metaOptions as $metaName => $metaVal) {
			$metaVal=apply_filters('mz_setmetaoptionvalue', $metaVal, $lotId, $metaName);
			update_metadata('maginza', $lotId, $metaName, $metaVal);			
		}		
	}
	
}


require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class MetaSettings__List_Table extends WP_List_Table {
	private $metaTypes;
	private $metaFormatters;
	
	function __construct($class) {
		parent::__construct( array(
			'singular'=> 'wp_list_text_link', 
			'plural' => $class, 
			'ajax'	=> false 
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
						'.__('Total Settings').'
						<span class="count">('.count($this->items).')</span>
					</a>					
				</li>
			</ul>
		';		
	}


	function prepare_items($items, $columns, $mt, $mf) {
		$this->metaTypes=$mt;
		$this->metaFormatters=$mf;
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $items;
	}
	
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'name':
				return $item->optName;
			case 'formatter':
				$select="<select name='metaoption[{$item->id}][formatter]' disabled>";				
				foreach ($this->metaFormatters as $metaFormatter) {
					$isCurrent=($item->optFormatter==$metaFormatter[0])? 'selected ' : '';
					$select.="<option value='{$metaFormatter[0]}' {$isCurrent} >{$metaFormatter[1]}</option>";
				}				
				$select.="</select>";	
				return $select;			
			case 'type':
				$select="<select name='metaoption[{$item->id}][type]' disabled>";				
				foreach ($this->metaTypes as $metaType) {
					$isCurrent=($item->optType==$metaType[0])? 'selected ' : '';
					$select.="<option value='{$metaType[0]}' {$isCurrent} >{$metaType[1]}</option>";
				}				
				$select.="</select>";	
				return $select;
			case 'value':
				return "<input name='metaoption[{$item->id}][value]' type='text' value='{$item->optValue}' disabled />";
			case 'visible':
				$visible=($item->optVisible==true)? 'checked':'';
				return "<input name='metaoption[{$item->id}][visible]' type='checkbox' value='1' disabled {$visible} />";
			case 'clienteditable': 
				$editable=($item->optClientEditable==true)? 'checked':'';
				return "<input name='metaoption[{$item->id}][editable]' type='checkbox' value='1' disabled {$editable} />";
			default:
				return 'error'; //TODO: сделать ногрмальным сообщение об ошибке
		}
	}
	
	function column_name($item) {
	  $actions = array(
				'edit'      => sprintf('<a class="btnMetaOptionEdit" id="%s" href="#">Edit</a>', $item->id),
				'delete'    => sprintf('<a class="btnMetaOptionDelete" id="%s" href="#">Delete</a>', $item->id),
	  );
	  return sprintf('%1$s %2$s',  $item->optName, $this->row_actions($actions) );
	}
	
	function no_items() {
		_e( 'No settings added.' );
	}

}

















