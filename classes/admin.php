<?php

class Admin extends Options {

	function __construct() {
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));	

		$this->globalSettings=new GlobalSettings();
		$this->metaSettings=new MetaSettings();

	}
		
	function admin_init() {
		add_filter('manage_posts_columns', array(&$this, 'managepostscolumns'), 2, 2);
		add_action('manage_pages_custom_column', array(&$this, 'managepostcolumn'), 0, 2);
		
		
	}
	
	function managepostscolumns($postcolumns, $posttype) {
		$postcolumns['thumb']='Превью';
		$postcolumns['article']='Артикул';
		return $postcolumns;
	}
	
	function managepostcolumn($column_name, $postID) {
		switch ($column_name) {
			case 'thumb':
				$url = wp_get_attachment_url( get_post_thumbnail_id($postID) );
				echo '<img width="50px" height="50px" src="'.$url.'" />';
				break;
			case 'article':
				echo '<b>'.get_post_meta($postID, 'article', true).'</b>';
				break;
		}
		return true;
	}
	
	function admin_menu(){
		//-- добавляем пункт в основное меню
		add_menu_page( "Maginza", __('Maginza', TEXTDOMAIN), 5, 'Maginza',  array(&$this, 'admin_maginza'));
		add_submenu_page("Maginza", __('Global Settings', TEXTDOMAIN), __('Global Settings', TEXTDOMAIN), 5, 'mz_globalsettings', array(&$this->globalSettings, 'showForm'));	 
		add_submenu_page("Maginza", __('Meta Settings', TEXTDOMAIN), __('Meta Settings', TEXTDOMAIN), 5, 'mz_metasettings', array(&$this->metaSettings, 'showForm'));
		
		add_action('admin_print_scripts', array($this, 'print_scripts'));
		
		add_filter( 'manage_users_columns', array( &$this, 'manage_users_columns' ), null, 1 );	//-- в списке пользователей добавляем колонку со стоимостью всего купленного
		add_filter('manage_users_custom_column', array(&$this, 'manage_users_custom_column'), null, 3); 
	}

	function admin_enqueue_scripts() {
		wp_enqueue_script( 'maginza-admin', plugins_url( 'js/jquery.admin.js' , dirname(__FILE__) ) );
		wp_register_style('maginza-admin', plugins_url( 'css/admin.maginza.css', dirname(__FILE__ ))); 
		wp_enqueue_style('maginza-admin');
	}
	
	function print_scripts() {
		wp_enqueue_script('post');	
			
	}
	
	
	function admin_maginza(){ 
		global $Maginza, $wpdb; 
	
		$orders=$wpdb->get_results($wpdb->prepare('SELECT * FROM '.Options::$table_order.' '));
		

		echo '<table>';
		
		foreach ($orders as $order) {
			
			$options = unserialize($order->lotMetaOptions);
						
			echo '<tr>';
			
				echo '<td>';
					echo $order->userID;
				echo '</td>';
				
				echo '<td>';
					echo $order->lotID;
				echo '</td>';
				
				do_action('mz_showorder', $order->lotID, $order->userID, $options);
					
			echo '</tr>';
		
		}
		
		echo '</table>';
		
	}
	
	function render_lot_meta_box_content($data) {
		var_dump($data);
	}
	

	
	function manage_users_columns($columns) {
		$columns['allorderprice']='Всего на';
		return $columns;
	}

	function manage_users_custom_column($out, $column_name, $userid) {
		global $Maginza, $wpdb; 
		if ( $column_name==='allorderprice') {
			$total=0;			
			return $total;	
		}
	} 

	
	
	
}
?>