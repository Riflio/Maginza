<?php

class Cart extends Order {
	
	function __construct() {
		add_shortcode('maginza_cart', array(&$this, 'showCart'));	
	}

	function showCart($args) {
		echo 'Корзина';
		$orderItems=$this->getListOrderItems();

        echo '<form method="GET">';
        foreach($orderItems as $item) {
            $lotID=$item->orderItemID;
            Lot::getInstance($lotID);

            $combinations=$this->getCombinationList($lotID);

            echo '<div class="orderitem">';

                echo '<div class="orderitem-previmg"> test1 </div>';

                echo '<div class="orderitem-content">';
                    echo '<span class="article">Артикул:';
                        Lot::theMeta('Article');
                    echo ' </span>';
                    echo '<span class="article">Цена:';
                        Lot::theMeta('Price');
                    echo' </span>';
                    echo '<span class="comb">'.$combinations[$item->combinationID]['combination'].'</span>';
                         Lot::metaForm('Article,Price,Selprevimg', $item->combinationID);
                echo '</div>';

            echo '</div>';

        }
        echo '</form>';
	}


	
}




?>