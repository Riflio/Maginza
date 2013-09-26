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
			$data=$_GET['metaoptvals'];
			echo $this->addToOrder($lotID, $data); 
		}
		
		die();	
	}
	
	function addToOrder($lotID, $data) {
		global $wpdb;
		
		//-- получим список опций товара, что бы на основе их выбирать нужное, из того, что нам подсунули.
		$metaOptions=$this->getLotMetaOptions(get_post($lotID)); 
		$values=array();
		foreach ($metaOptions as $metaOption) {
			if ($metaOption->optClientEditable && $metaOption->optVisible) {
				$values[]=array($metaOption->optName=>$data[$metaOption->optName]); //TODO:  ПРОВЕРЯТЬ!!!
			}		
		}
		$values=serialize($values);
		if ($this->onOrder($lotID, $values)) return 'Такая позиция уже есть в заказе.';
		$wpdb->insert(
			Options::$table_order, 
			array(
				'userID'=>Buyer::ID(), 
				'lotID'=>$lotID, 
				'lotMetaOptions'=>$values,
				'lotMetaOptionsHash'=>sha1($values)
			), array('%s', '%d', '%s', '%s') 
		);
		
		echo 'Позиция успешно добавлена в заказ.';
		
	}
	
	function onOrder($lotID, $values) {
		global $wpdb;
		$user_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.Options::$table_order.'  WHERE userID=%s AND lotID=%d AND lotMetaOptionsHash=%s ', Buyer::ID(), $lotID, sha1($values)));
		return ($user_count>0)? true : false;
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