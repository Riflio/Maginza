<?php

/**
 *
 *
 */

require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');


class Admin extends Options {
    private  $pagehook;

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
        $this->pagehook=add_menu_page( "Maginza", __('Maginza', TEXTDOMAIN), 5, 'Maginza',  array(&$this, 'admin_maginza'));
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
        wp_enqueue_script('common');
	}
	
	
	function admin_maginza(){ 
		global $Maginza, $wpdb;

        if (isset($_GET['order'])) {
            add_meta_box('metabox_curorder', __('Orders'), array(&$this, 'metabox_curorder'), $this->pagehook, 'normal', 'core');
        } else {
            add_meta_box('metabox_stat', __('Statistics'), array(&$this, 'metabox_stat'), $this->pagehook, 'normal', 'core');
            add_meta_box('metabox_orders', __('Orders'), array(&$this, 'metabox_orders'), $this->pagehook, 'normal', 'core');
        }

        require_once('admin.tpl.php');
		
	}
	

	/**
	 * Добавляем новую колонку в листинг товаров админки
     *
	 */
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


    /**
     *  Бокс со статистикой...
     *
     */
	public function metabox_stat() {
        echo 'Тут скоро будет статистика...';
    }

    /**
     * Бокс-таблица со всеми заказами
     *
     */
    public function metabox_orders() {
        global $wpdb;
        $tableorder=Options::$table_order;
        $orders=$wpdb->get_results("SELECT * FROM {$tableorder} ");
        //--
        $items=array();
        foreach ($orders as $order) {
            $user=Buyer::getInfo($order->userID);
            $date=new DateTime($order->orderDT);

            $items[]=array(
                'OrderID'=>$order->orderID,
                'User'=>'<a href="'.admin_url('user-edit.php?user_id='.$user->ID, 'http').'">'.$user->user_login.'</a>',
                'OrderStatus'=>$order->orderStatus,
                'OrderDate'=>$date->format('d.m.Y H:m'),
                'OrderAction'=>'<a href="?page=Maginza&order='.$order->orderID.'">'.__('Show').'</a>'
            );
        }
        $table=new Orders__List_Table('OrdersTable');
        //--
        $columns=array(
            'OrderID'=>__('Order ID'),
            'User'=>__('User'),
            'OrderStatus'=>__('Order Status'),
            'OrderDate'=>__('Order Date'),
            'OrderAction'=>__('Actions')
        );
        //--
        $table->prepare_items($items, $columns);
        $table->display();

    }

    /**
     * Выводим текущий заказ
     *
     */
    public function metabox_curorder() {
        global $post;
        $orderID=intval($_GET['order']);

        $order=new Order();
        $orderItems=$order->getListOrderItems($orderID);

        $tableitems=array();
        foreach($orderItems as $item) {
            $lotID=$item->orderItemID;
            $itemID=$item->orderItemsID;

            $lot=get_post($lotID);

            $post=$lot;
            setup_postdata($post);

            $order->setItemID($itemID);

            $comb=$order->getCombination();

            $tableitems[]=array(
                'Title'=>get_the_title(),
                'Descr'=>get_the_content(),
                'Article'=> $order->theMetaValue($lot, 'Article', 'cart', false),
                'Comb'=>$comb['combination'],
                'Price'=>$order->theMetaValue($lot, 'Price', 'cart', false),
                'Quantity'=>$order->theMetaValue($lot, 'Quantity',  'cart-'.$itemID, false)
            );

        }
        //--
        $tablecolumns=array(
            'Title'=>__('Title'),
            'Descr'=>__('Description'),
            'Article'=>__('Article'),
            'Comb'=>__('Comb'),
            'Price'=>__('Price'),
            'Quantity'=>__('Quantity')
        );


        $table=new CurOrder__List_Table('CurOrder_listtable');
        $table->prepare_items($tableitems, $tablecolumns);
        $table->display();


    }
	
	
}


class CurOrder__List_Table extends WP_List_Table {
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
						'.__('Total order items').'
						<span class="count">('.count($this->items).')</span>
					</a>
				</li>
			</ul>
		';
    }


    function prepare_items($items, $columns) {
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $items;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'Title':
                return $item[$column_name];
            break;
            case 'Descr':
                return $item[$column_name];
            break;
            default:
                return $item[$column_name];
                break;
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
        _e( 'No order items.' );
    }

}

class Orders__List_Table extends WP_List_Table {
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
						'.__('Total orders').'
						<span class="count">('.count($this->items).')</span>
					</a>
				</li>
			</ul>
		';
    }


    function prepare_items($items, $columns) {
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $items;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'OrderID':
                return $item[$column_name];
            break;
            case 'User':
                return $item[$column_name];
            break;
            case 'OrderStatus':
                return $item[$column_name];
            break;
            case 'OrderDate':
                return $item[$column_name];
            break;
            default:
                return $item[$column_name];
            break;
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
        _e( 'No client orders.' );
    }

}




?>