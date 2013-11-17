<?php
/*
	Plugin Name: Maginza
	Plugin URI: http://pavelk.ru/maginza
	Description: Интернет магазин
	Version: 1.0
	Author: PavelK
	Author URI: http://PavelK.ru
	Copyright 2011  PavelK  (email: 2me@pavelk.ru)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once 'classes/options.php';
require_once 'classes/formatter.php';
require_once 'classes/meta.php';  require_once 'classes/metasettings.php';
require_once 'classes/combinations.php';
require_once 'classes/lot.php'; require_once 'classes/lots.php';

require_once 'classes/buyer.php';

require_once 'classes/FormulaInterpreter/Parser.class.php';

require_once 'classes/orderitem.php';
require_once 'classes/order.php';

require_once 'classes/order.php';


require_once 'classes/cart.php';



require_once 'classes/globalsettings.php';


class Maginza extends Options  {
	private $setup;
	private $admin;
	private $options;
	private $order;
	public  $cart;
	public $combinations;
    private $lots;
	
	function __construct(){
		//-- подгрузим язык --//
		define('TEXTDOMAIN', 'default');		
		load_plugin_textdomain(TEXTDOMAIN, PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/langs');
			
		if (is_admin()) {			
			include 'classes/admin.php';			
			include 'classes/setup.php';					
			$this->setup=new Setup(__FILE__);
			$this->admin=new Admin();
		}

		$this->options=new Options();
		$this->order=new Order();
		$this->cart=new Cart();
		$this->combinations=new Combinations();
        $this->lots=new Lots();
		
		add_action('init', array($this, 'init'), 1);			
		//--  Добавим поиск по артикулу а потом мож ещё по чему 
		add_filter('posts_join', array(&$this, 'maginza_search_join' ));
		add_filter('posts_where', array(&$this,'maginza_search_where' ));
		add_filter('posts_groupby', array(&$this,'maginza_search_groupby'));
	}	

	function init() {		
		global $wp_rewrite;
		$labels=array(
		  'add_new'=>__('Add new'),
		  'add_new_item'=> __('Add new lot'),
		  'edit_item' => __('Edit lot'),
		  'new_item' => __('New lot'),
		  'all_items' => __('All lots'),
		  'view_item' => __('View lot'),
		  'not_found' => __('Not found')
		);
		//-- тип постов для товаров
		$args = array( 
		   'label' => __('lots'), 
		   'labels'=>$labels,
		   'singular_label' => __('lots'), 
		   'public' => true, 
		   'show_ui' => true, 
		   'capability_type' => 'post', 
		   'hierarchical' => true, 
		   'rewrite' => true, 
		   'query_var' => true,	
		   'supports' => array(
				'thumbnail',
				'comments',
				'title',
				'editor',
				'excerpt',
				'page-attributes'
			)  
		); 
		register_post_type('lots',$args);	
		//-- типы товаров
		register_taxonomy( 'types', 'lots',
			array(
			  'hierarchical' => true, 
			  'label' => __('types'), 
			  'query_var' => true,			  
			  'rewrite' => array('slug' => 'types' )
			)
		);		
		//-- их характеристики
		register_taxonomy( 'features', 'lots',
			array(
			  'hierarchical' => true, 
			  'label' => __('features'), 
			  'query_var' => 'features',
			  'rewrite' => array('slug' => 'features' )
			)
		);
		wp_enqueue_script('maginza', WP_PLUGIN_URL.'/wp_maginza/js/jquery.maginza.js', array('jquery'));	//-- через хэш указываем на ajaxurl
		wp_localize_script( 'maginza', 'maginza', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		
		
		
		Buyer::getInstance()->ID();	
		
	}


	function maginza_search_join($join) {
		global $wpdb;
		if( is_search() ) {
			
			 $join .= 'LEFT JOIN '.Options::$table_meta.' ON wp_maginzameta.maginza_id=wp_posts.ID';			
		}
		return $join;
	}

	function maginza_search_where( $where )	{
		global $wpdb;
		if( is_search() ) {
			$qfindkeys=$wpdb->get_results('SELECT optName FROM '.Options::$table_meta_options.' WHERE optForSearch=1');
			$findkeys=array();	
			foreach ($qfindkeys as $findkey) { $findkeys[]= $findkey->optName; }
			$findkeys=implode(',', $findkeys);			
			$where = preg_replace('/post_title LIKE \'(.*?)\'\)/', 'post_title LIKE  \'$1\') OR ( (wp_maginzameta.meta_key FIND_IN_SET('.$findkeys.') ) AND  (wp_maginzameta.meta_value LIKE \'$1\') ) ', $where );
		}
		return $where;
	}

	function maginza_search_groupby( $groupby )	{
		if( !is_search() ) {
			return $groupby;
		}
		$groupby="wp_posts.ID";
		return $groupby;
	}
}
$Maginza = new Maginza();
?>