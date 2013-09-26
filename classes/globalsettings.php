<?php

class GlobalSettings {
	
	function __construct() {
		add_action('admin_init', array(&$this, 'admin_init'));
	}
	
	function showForm() {
		$optSection=isset($_GET['tab'])? $_GET['tab'] : 'mz_general_options';	
		$tabs=array(
			array("mz_general_options", 'General options'),
			array("mz_merchant_options", 'Merchant options'),
			array("mz_formatter_options", 'Formatter options'),
			array("mz_other_options", 'Other options')
		);
		$tabs=apply_filters("mz_settings_tabs", $tabs);
		echo '
			<div class="wrap">  
				<div id="icon-themes" class="icon32"></div>  
				<h2>'.__('Maginza options').'</h2>  
				<h2 class="nav-tab-wrapper">  
		';
		settings_errors();
		foreach ($tabs as $tab) {
			$isTabActive=($tab[0]==$optSection)? 'nav-tab-active' : '';
			echo "<a href='?page=mz_globalsettings&tab={$tab[0]}' class='nav-tab {$isTabActive}'> {$tab[1]} </a> ";
		}
		echo '	
				</h2>
				<form method="POST" action="options.php">
		';				
				if ($optSection=="mz_formatter_options") { //-- отдельная форма для форматтеров
					
				} else {
					settings_fields($optSection);	
					do_settings_sections($optSection);
					submit_button();
				}	
		echo'	
				</form>
			</div>
		';	
	}
	
	function admin_init() {
		//-- регистрируем настройки	
		
		
		$settings=array(
			array(
				'sectionName'=>'eg_setting_section',
				'descr'=>'Example settings section in reading',
				'page'=>'mz_general_options',
				'fields'=>array(
					array('name'=>'mz_format_price', 'title'=>'Format price', 'descr'=>'Descr text', 'type'=>'text')
					,array('name'=>'mz_format_addbutton', 'title'=>'Format order button', 'descr'=>' descr descr descr', 'type'=>'text')
				)	
			)	
		);
		
		foreach ($settings as $section) {
			add_settings_section($section['sectionName'], __($section['descr']), array(&$this, 'mz_setting_section_callback'), $section['page']);
			foreach($section['fields'] as $field) {
				add_settings_field($field['name'], __($field['title']), array(&$this, 'mz_setting_field_callback'), $section['page'], $section['sectionName'], $field);
				register_setting($section['page'], $field['name']);
			}			
		}
				
	}
	
	function mz_setting_section_callback($section) {
		echo '<p></p>';
    }
	 
	function mz_setting_field_callback($field) {		
		echo "<input name='{$field['name']}' id='edit_{$field['name']}' size='100' type='{$field['type']}' value='".htmlspecialchars(get_option($field['name']))."' class='' /> {$field['descr']}";
	}

}


?>