<?php

/**
 * @author pavelk
 * ПОЛОВИНА БУДЕТ ПЕРЕДЕЛАНО
 *
 */

class Cart extends Order {
	
	function __construct() {
		add_shortcode('maginza_cart', array(&$this, 'showCart'));
		add_shortcode('maginza_ordercomplete', array(&$this, 'maginza_ordercomplete'));
		add_shortcode('maginza_clientorderslist', array(&$this, 'maginza_clientorderslist'));
        add_action('wp_ajax_order', array(&$this, 'ajax_order'));
        add_action('wp_ajax_nopriv_order', array(&$this, 'ajax_order'));

        add_action( 'wp_enqueue_scripts',  array(&$this, 'enqueue_scripts') );
        
        //add_action('init', array($this, 'init'), 1);;
        add_action('wp_login', array(&$this, 'wp_login'), 10, 2);
    }
    
    /**
     * Подключаем скрипты
     */
	function enqueue_scripts() {
		wp_enqueue_script('formulaparser',  WP_PLUGIN_URL.'/wp_maginza/js/maginza.formulaparser.js', array());
		
	}
    
    /**
     * Выводим список заказов клиента
     *
     */
    public function maginza_clientorderslist($args) {
        $orders=$this->getListOrders();
        //TODO: !!!переделать всё!!! это временно!!!

        if (count($orders)==0) {
            return 'У Вас ещё небыло заказов.';
        }

        $ret='<table class="tableclientorders"> <tr><td>Номер</td><td>Статус</td><td>Дата</td></tr>';

        foreach ($orders as $order) {

            switch ( $order->orderStatus) {
                case 0: $status='В процессе'; break;
                case 5: $status='Завершён'; break;
            }

            $date=new DateTime($order->orderDT);

            $ret.='<tr>';
                $ret.=('<td>'.$order->orderID.'</td>');
                $ret.=('<td>'.$status.'</td>');
                $ret.=('<td>'.$date->format('d.m.Y H:m').'</td>');
                $ret.=('<td><a class="btn changeorder" id="'.$order->orderID.'" href="#">Изменить</a></td>');
            $ret.='</tr>';
        }

        $ret.='</table>';

        return $ret;
    }

    /**
     * Изменяем статус заказа, короче отправляем его обратно в корзину
     * TODO: сделать запись в лог
     */
    public function toChangeOrder($orderID) {
        global $wpdb;
        $wpdb->update(Options::$table_order, array('orderStatus'=>'0'), array('orderID'=>$orderID), array('%s'), array('%d'));
        return ;
    }

    /**
     *  Когда юзер логинится, меняем айдишник его сессии на айдишник пользователя в заказах
     *
     */

    public function wp_login($ulogin, $userInfo ) {
        global $wpdb;
        if (is_admin()) return true;
        if (session_id()!="") {
            //-- пробуем найти старый айдишник
            $oldID=session_id();
            $wpdb->update(Options::$table_order, array('userID'=>$userInfo->ID), array('userID'=>$oldID), array("%s"),  array("%s"));
        }
        return true;
    }

    /**
     *
     *
     */
    public function cartStatus() {
        $orderItems=$this->getListOrderItems($this->orderID());
        $c=count($orderItems);
        if ($c==0) {
            return 'нет товаров';
        } else {
            if ($c==1) return '1 товар.';
            if ($c>1 && $c<5) return $c.' товара.';
            return $c.' товаров.';
            //TODO: доделать склонения
        }
    }

    /**
     *
     *
     */
    function maginza_ordercomplete($args) {
        global $current_user;
        //TODO: всё переделать!!!
        get_currentuserinfo();
        $r= '';
         if ( !is_user_logged_in() ) {
            $r.= ' <a class="simplemodal-login alogin" href="/login?redirect_to=http://suvenirus.org/zavershenie-zakaza">Вход с паролем</a> или <a class="aregister" href="/register?redirect_to=http://suvenirus.org/zavershenie-zakaza">Регистрация</a>';
            $r.='<br> Регистрация не займёт много времени, она нужна для оформления заказа.';
         } else {

             $url=admin_url('admin-ajax.php' ).'?action=order&method=sendcart&cartorderid='.$this->orderID();
             $r=$this->theButton('sendCart', 'Завершить оформление', $url, false);


         }

        return $r;
    }

    /**
     *
     *
     */
    function showCart($args) {
        global $post;
		$orderItems=$this->getListOrderItems($this->orderID());
		
		
		//-- отдаём для js расчёта данные --//
		
        if (count($orderItems)==0) {
            echo 'Ваша корзина пуста.';
            return;
        }

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

            	echo '<script> '.$this->getItemJSData().'</script>';
            
                echo '<div class="orderitem-previmg">';
                         $this->theMetaValue($lot, 'Selprevimg', 'cart');
                echo '</div>';

                echo '<div class="orderitem-content lotform" id="'.$itemID.'" >';
                    echo $this->metaFormID();
                    echo '<div class="title">';
                        the_title();
                    echo ' </div>';
                    echo '<div class="descr">';
                        the_content();
                    echo ' </div>';
                    echo '<div class="article"><b>Артикул: </b>';
                         $this->theMetaValue($lot, 'Article', 'cart');
                    echo ' </div>';
                    echo '<div class="comb">';
                        echo $comb['combination'];
                    echo '</div>';
                    echo '<div class="countandprice">';
                       echo '<span class="dprice">'; $this->theMetaValue($lot, 'Price', 'cart'); echo '</span>';
                       echo '<span class="x">&times;</span>';
                       echo '<span class="count">'; $this->theMetaValue($lot, 'Quantity', 'cart', true, $itemID); echo '</span>';
                       echo '<span class="eq">=</span>';
                       echo '<span class="cost">'; echo Formatter::format('price', '', $this->getItemTotalPrice()); echo '</span>';
                    echo '</div>';

                    echo '<div class="actionbtns">';
                        $this->theButton('delete', 'Удалить', "#");
                    echo '</div>';

                echo '</div>';

            echo '</div>';

        }
        echo '<div class="cartactbtns">';
        
            $this->theButton('saveCart', 'Сохранить', "#");
            $this->theButton('nextCart', 'Продолжить оформление', "http://suvenirus.org/zavershenie-zakaza");
        echo '</div>';
        echo '</form></div>';
	}

    function ajax_order() {
        $method=$_REQUEST['method'];
        $lotID=intval($_REQUEST['lotid']);
        if ($method==="buy") {
            $metaOpts=Formatter::reqMetaOptpValue($_REQUEST['formname']);
            $features=Formatter::reqCombFeature($_REQUEST['formname']);

            echo $this->addItemOrder($this->orderID(true), $lotID, $metaOpts, $features);
        }
       

        if ($method==="deleteorderitem") {
            $itemID=intval($_REQUEST['orderitemid']);
            $this->deleteOrderItem($itemID);

            $res=(object) NULL;
            $res->orderitemid=$itemID;
            echo json_encode($res);
        }

        if ($method==="savecart") {
            $metaoptvals=$_REQUEST['metaoptvals'];
            $this->saveCart($metaoptvals);
        }

        if ($method==="sendcart") {
            $cartOrderID=intval($_REQUEST['cartorderid']);
            $this->sendCart($cartOrderID);
        }

        if ($method==="changeorder") {
            $orderID=intval($_REQUEST['orderitemid']);
            $this->toChangeOrder($orderID);
        }

        die();
    }

    /**
     * Отдаём список заказов клиента
     *
     */
    public function getListOrders() {
        global $wpdb;

        $torder=Options::$table_order;
        $orders=$wpdb->get_results("SELECT * FROM {$torder} WHERE userID=".Buyer::ID());

        return $orders;


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


    public function theButton($action, $text, $url, $echo=true) {
        $r= Formatter::format('button', $action, $text, $url);
        if ($echo) echo $r;
        return $r;
    }

    /**
     *
     *
     */
    public function saveCart($metaoptvals) {
        global $wpdb;
        foreach($metaoptvals as $orderitemid => $saveMetaOpts) {
            $orderitemid=explode('-', $orderitemid);
            $orderitemid=$orderitemid[1];

            $iItem=OrderItem::getInstance();
            $iItem->setItemID($orderitemid);

            $item=$iItem->getItem();
            $lot=get_post($item->orderItemID);

            $saveMetaOpts=$this->checkMetaOptions($lot, $saveMetaOpts);

            $orderItemMetaOptions=$iItem->orderItemMetaOptionsValues($lot, $saveMetaOpts, true);
            $orderItemMetaOptions=serialize($orderItemMetaOptions);

            $wpdb->update(Options::$table_order_items, array('metaOptions'=>$orderItemMetaOptions), array('orderItemsID'=>$orderitemid), array('%s'), array('%d'));

        }

    }

    /**
     *
     *
     */
    public function sendCart($orderID) {
        global $wpdb;
        //TODO:
        $wpdb->update(Options::$table_order, array('orderStatus'=>'5'), array('orderID'=>$orderID, 'userID'=>Buyer::ID()), array('%s'), array('%d', '%s'));

    }


}




?>