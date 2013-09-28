<?php

class Cart extends Order {
	
	function __construct() {
		add_shortcode('maginza_cart', array(&$this, 'showCart'));
        add_action('wp_ajax_order', array(&$this, 'ajax_order'));
        add_action('wp_ajax_nopriv_order', array(&$this, 'ajax_order'));
    }

	function showCart($args) {
		echo 'Корзина';
		$orderItems=$this->getListOrderItems();

        echo '<form method="GET">';
        foreach($orderItems as $item) {
            $lotID=$item->orderItemID;


            $combinations=$this->getCombinationList($lotID);

            echo '<div class="orderitem">';

                echo '<div class="orderitem-previmg"> test1 </div>';

                echo '<div class="orderitem-content">';
                    echo '<span class="article">Артикул:';

                    echo ' </span>';
                    echo '<span class="article">Цена:';

                    echo' </span>';
                    echo '<span class="comb">'.$combinations[$item->combinationID]['combination'].'</span>';

                echo '</div>';

            echo '</div>';

        }
        echo '</form>';
	}

    function ajax_order() {
        $method=$_GET['method'];
        $lotID=intval($_GET['lotid']);
        if ($method==="buy") {
            $metaOpts=Formatter::reqMetaOptpValue($_GET['formname']);
            $features=Formatter::reqCombFeature($_GET['formname']);

            echo $this->addToOrder($this->orderID(true), $lotID, $metaOpts, $features);
        }

        die();
    }


    /**
     * Выдаём айдишник заказа.
     * Если заказа нет (клиент добавил первый товар), то создаём.
     */
    function orderID($create=false) {
        global $wpdb;
        $table_order=Options::$table_order;
        $qOrder=$wpdb->get_var($wpdb->prepare("SELECT orderID FROM {$table_order} WHERE userID=%s AND orderStatus=0 LIMIT 1", Buyer::ID() ));
        if ($qOrder) {
            return $qOrder;
        } else
            if ($create) {
                $wpdb->insert($table_order, array('userID'=>Buyer::ID(), 'orderStatus'=>0, 'orderDT'=>date("Y-m-d H:i:s")), array('%s', '%s'));
                return $wpdb->insert_id;
            } else {
                return false;
            }
    }


    public function theButton($action, $text) {
        echo Formatter::format('button', $action, $text);
    }

    public function theDeleteButton($isShow=true, $lotID=-1) {

    }

	
}




?>