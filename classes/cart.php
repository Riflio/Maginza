<?php

class Cart extends Order {
	
	function __construct() {
		add_shortcode('maginza_cart', array(&$this, 'showCart'));	
	}

	function showCart($args) {
		echo 'Корзина';
		$orders=$this->getListOrders();
	}
	
	function getListOrders() {
		global $wpdb;
		 $wpdb->get_results($wpdb->prepare('SELECT * FROM '.Options::$table_order.' WHERE userID=%s ', Buyer::ID()));
	
	
	}
	
	
}




?>