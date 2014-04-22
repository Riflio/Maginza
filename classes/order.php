<?php

/*
 *
 */

class Order extends OrderItem {

	public function __construct() {						

	}

    /**
     *
     *
     */
    public function updateOrderItem($orderItemID, $lotID, $metaOptions) {
        global $wpdb;

    }

    /**
     *
     *
     */
    public function deleteOrderItem($itemID) {
        global $wpdb;
        $errCode=$wpdb->delete(Options::$table_order_items, array('orderItemsID'=>$itemID), array('%d'));
        return $errCode;
    }

    /**
     *
     *
     */
    public function addItemOrder($orderID, $lotID, $data, $features) {
		global $wpdb;
		$status=(object) NULL; //TODO: перенести в Cart
        //TODO: Проверять на заполненость полей перед добавлением, а так же добавить фильтр.

		//--выбирать нужное, из того, что нам подсунули.
		$metaOpts=$this->checkMetaOptions(get_post($lotID), $data);
        $metaOpts=serialize($metaOpts);

        //-- подберём комбинацию по характеристикам
        $combination=$this->whatCombination($features, $lotID);

        if ($combination===false) {
            $status->status='ERROR';
            $status->msg='Приносим извенения, произошла ошибка при добавлении товара. Пожалуйста, обратитесь к консультанту в левом нижнем углу сайта.';
            echo json_encode($status);
            return;
        }


        $wpdb->insert(
			Options::$table_order_items,
			array(
				'orderID'=>$orderID,
				'orderItemID'=>$lotID,
				'metaOptions'=>$metaOpts,
				'combinationID'=>$combination['id']
			), array('%d', '%d', '%s', '%d')
		);

        $status->status='OK';
        $status->msg='Позиция успешно добавлена в заказ.';
         echo json_encode($status);
		
	}

    /**
     * Отдаём список заказов клиента
     *
     */
    function getListOrders() {

    }


    /**
     * Отдаём список товаров в текущем заказе клиента
     *
     */
    function getListOrderItems($orderID) {
        global $wpdb;
        $table_order_items=Options::$table_order_items;
        $orders=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_order_items} WHERE orderID=%d ", $orderID ));
        //-- Возможно, пока клиент отсутствовал, лот удалили, необходимо проверить и в случае чего удалить из заказа
        //TODO: Будет правильнее удалять из заказов непосредственно при удалении лота см. общий туду
        $allLotIDs=$wpdb->get_var($wpdb->prepare("SELECT  GROUP_CONCAT(ID) FROM {$wpdb->posts} WHERE post_type=%s ", 'lots'));
        $allLotIDs=explode(',' , $allLotIDs);        
        foreach($orders as $key=> $item) {        
        	if (! in_array($item->orderItemID, $allLotIDs)  ) {
        		unset($orders[$key]);
        	}        	
        }	
        return $orders;
    }
	
	function onOrder($lotID, $values, $combination) {
	//	global $wpdb;
	//	$user_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.Options::$table_order.'  WHERE userID=%s AND lotID=%d AND lotMetaOptionsHash=%s ', Buyer::ID(), $lotID, sha1($values)));
	//	return ($user_count>0)? true : false;
	}




	
}

?>