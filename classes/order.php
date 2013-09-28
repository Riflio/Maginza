<?php

/*
 *
 */

class Order extends Combinations {

	public function __construct() {						

		//add_action('the_post', array(&$this, 'the_post'),100,100);
	}
	




    /**
     *
     *
     */
    function addItemOrder($orderID, $lotID, $data, $features) {
		global $wpdb;
		$status=(object) NULL;
        //TODO: Проверять на заполненость полей перед добавлением, а так же добавить фильтр.

		//-- получим список опций товара, что бы на основе их выбирать нужное, из того, что нам подсунули.
		$metaOptions=$this->getLotMetaOptions(get_post($lotID)); 
		$values=array();
		foreach ($metaOptions as $metaOption) {
			if ($metaOption->optClientEditable && $metaOption->optVisible) {
				$values[]=array($metaOption->optName=>$data[$metaOption->optName]); //TODO:  ПРОВЕРЯТЬ!!!
			}		
		}
		$values=serialize($values);

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
				'metaOptions'=>$values,
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
        return $orders;
    }
	
	function onOrder($lotID, $values, $combination) {
	//	global $wpdb;
	//	$user_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.Options::$table_order.'  WHERE userID=%s AND lotID=%d AND lotMetaOptionsHash=%s ', Buyer::ID(), $lotID, sha1($values)));
	//	return ($user_count>0)? true : false;
	}

//	public function the_post(&$postData) {
//		if ($postData->post_type!='lots') return;
// 		Lot::getInstance($postData);
//	}


    function getMetaValue($lot, $metaName) {
        return '12312';

    }


	
}

?>