<?php

/*
 *
 */

class Order extends Lot {	

	public function __construct() {						
		add_action('wp_ajax_order', array(&$this, 'ajax_order'));			
		add_action('wp_ajax_nopriv_order', array(&$this, 'ajax_order'));	
		add_action('the_post', array(&$this, 'the_post'),100,100);	
	}
	
	function ajax_order() {		
		$method=$_GET['method'];		
		$lotID=intval($_GET['lotid']);
		if ($method==="buy") {			
			$metaOpts=$_GET['metaoptvals']; //FIXME: защита!
            $features=$_GET['feature']; //FIXME: защита!

			echo $this->addToOrder($lotID, $metaOpts, $features);
		}
		
		die();	
	}

    /**
    * Выдаём айдишник заказа.
    * Если заказа нет (клиент добавил первый товар), то создаём.
    */

    function orderID() {
        global $wpdb;
        $table_order=Options::$table_order;
        $qOrder=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_order} WHERE userID=%s AND orderStatus=0 LIMIT 1", Buyer::ID() ));
        if ($qOrder) {
            return $qOrder->orderID;
        } else {
            $wpdb->insert($table_order, array('userID'=>Buyer::ID(), 'orderStatus'=>0, 'orderDT'=>date("Y-m-d H:i:s")), array('%s', '%s'));
            return $wpdb->insert_id;
        }
    }


    /**
     *
     *
     */
    function addToOrder($lotID, $data, $features) {
		global $wpdb;
		$status=(object) NULL;


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
				'orderID'=>$this->orderID(),
				'orderItemID'=>$lotID,
				'metaOptions'=>$values,
				'combinationID'=>$combination['id']
			), array('%d', '%d', '%s', '%d')
		);

        $status->status='OK';
        $status->msg='Позиция успешно добавлена в заказ.';
         echo json_encode($status);
		
	}
	
	function onOrder($lotID, $values, $combination) {
	//	global $wpdb;
	//	$user_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.Options::$table_order.'  WHERE userID=%s AND lotID=%d AND lotMetaOptionsHash=%s ', Buyer::ID(), $lotID, sha1($values)));
	//	return ($user_count>0)? true : false;
	}

	public function the_post(&$postData) {		
		if ($postData->post_type!='lots') return;
 		Lot::getInstance($postData);
	}
		
	
	public function theButton($action, $text) {
		echo Formatter::format('button', $action, $text);
	}
	
	public function theDeleteButton($isShow=true, $lotID=-1) {
		
	}
	
	
}

?>