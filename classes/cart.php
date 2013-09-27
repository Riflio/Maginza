<?php

class Cart extends Order {
	
	function __construct() {
		add_shortcode('maginza_cart', array(&$this, 'showCart'));	
	}

	function showCart($args) {
		echo 'Корзина';
		$orders=$this->getListOrderItems();

        foreach($orders as $order) {

            echo '<div class="orderitem">';

                echo '<div class="orderitem-previmg"> test1 </div>';

                echo '<div class="orderitem-content"> test2 </div>';

            echo '</div>';

        }
	}


	
}




?>