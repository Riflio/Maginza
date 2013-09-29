<?php

class Cart extends Order {
	
	function __construct() {
		add_shortcode('maginza_cart', array(&$this, 'showCart'));
        add_action('wp_ajax_order', array(&$this, 'ajax_order'));
        add_action('wp_ajax_nopriv_order', array(&$this, 'ajax_order'));
    }

	function showCart($args) {
        global $post;
		$orderItems=$this->getListOrderItems($this->orderID());
        echo '<div class="cart"><form method="GET" id="formcart" >';
            echo '<input type="hidden" name="cartOrderID" id="cartOrderID" value="'.$this->orderID().'" />';
        foreach($orderItems as $item) {
            $lotID=$item->orderItemID;
            $itemID=$item->orderItemsID;

            $lot=get_post($lotID);

            $post=$lot;
            setup_postdata($post);

            $this->setItemID($itemID);

            $comb=$this->getCombination();

            //TODO: изменить на пользовательский шаблон

            echo '<div class="orderitem item-'.$itemID.'">';

                echo '<div class="orderitem-previmg">';
                         $this->theMetaValue($lot, 'Selprevimg', 'cart');
                echo '</div>';



            echo '</div>';

        }
        echo '<div class="cartactbtns">';
            $this->theButton('saveCart', 'Сохранить', "#");
            $this->theButton('sendCart', 'Отправить заказ', "#");
        echo '</div>';
        echo '</form></div>';
	}

    function ajax_order() {
        $method=$_GET['method'];
        $lotID=intval($_GET['lotid']);
        if ($method==="buy") {
            $metaOpts=Formatter::reqMetaOptpValue($_GET['formname']);
            $features=Formatter::reqCombFeature($_GET['formname']);

            echo $this->addItemOrder($this->orderID(true), $lotID, $metaOpts, $features);
        }
        if ($method==="getitemtotalprice") {
            $itemID=intval($_GET['orderitemid']);
            $customOpts=$metaOpts=$this->checkMetaOptions(get_post($lotID), $_GET); //-- выберем метаопци из всего запроса

            $this->setItemID($itemID);
            $totalPrice=$this->getItemTotalPrice($customOpts);

            $price=(object) NULL;
            $price->text=Formatter::format('price', '', $totalPrice);
            $price->value=$totalPrice;
            $price->orderitemid=$itemID;
            $price->rand=$_GET['rand'];
            echo json_encode($price);
        }

        if ($method==="deleteorderitem") {
            $itemID=intval($_GET['orderitemid']);
            $this->deleteOrderItem($itemID);

            $res=(object) NULL;
            $res->orderitemid=$itemID;
            echo json_encode($res);
        }

        if ($method==="savecart") {
            $metaoptvals=$_GET['metaoptvals'];
            $this->saveCart($metaoptvals);
        }

        if ($method==="sendcart") {
            $cartOrderID=intval($_GET['cartorderid']);
            $this->sendCart($cartOrderID);
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


    public function theButton($action, $text, $url) {
        echo Formatter::format('button', $action, $text, $url);
    }

    /**
     *
     *
     */
    public function saveCart($metaoptvals) {
        foreach($metaoptvals as $orderitemid => $saveMetaOpts) {
            $orderitemid=explode('-', $orderitemid);
            $orderitemid=$orderitemid[1];

            $iItem=OrderItem::getInstance();
            $iItem->setItemID($orderitemid);

            $item=$iItem->getItem();
            $lot=get_post($item->orderItemID);

            $saveMetaOpts=$this->checkMetaOptions($lot, $saveMetaOpts);

            $orderItemMetaOptions=$iItem->orderItemMetaOptionsValues($lot, $saveMetaOpts, true);


        }

    }

    /**
     *
     *
     */
    public function sendCart($orderID) {
        global $wpdb;

    }


}




?>