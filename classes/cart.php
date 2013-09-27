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
            Lot::getInstance($lotID);

            $combinations=$this->getCombinationList($lotID);

            echo '<div class="orderitem">';

                echo '<div class="orderitem-previmg"> test1 </div>';

                echo '<div class="orderitem-content">';
                    echo '<span class="article">Артикул:'.Lot::theMeta('Article').' </span>';
                    echo '<span class="article">Цена:'.Lot::theMeta('Price').' </span>';
                    echo '<span class="comb">'.$combinations[$item->combinationID]['combination'].'</span>';
                echo '</div>';

            echo '</div>';

        }
	}


	
}




?>