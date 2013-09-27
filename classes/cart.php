<?php

class Cart extends Order {
	
	function __construct() {
		add_shortcode('maginza_cart', array(&$this, 'showCart'));	
	}

	function showCart($args) {
		echo 'Корзина';
		$orderItems=$this->getListOrderItems();

        foreach($orderItems as $item) {
            $lotID=$item->orderItemID;

            $combinations=$this->getCombinationList($lotID);

            echo '<div class="orderitem">';

                echo '<div class="orderitem-previmg"> test1 </div>';

                echo '<div class="orderitem-content">';
                    echo  $combinations[$item->combinationID]['Combination'];
                echo '</div>';

            echo '</div>';

        }
	}


	
}




?>