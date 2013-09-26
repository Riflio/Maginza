<?php
//TODO: complete this class

class merchant extends Options {
	var $mActive;
	
	function __construct() {
		require_once 'merchant/merchant_manager.php'; 		
		add_shortcode('mz_merchant', array(&$this, 'mz_merchant'));	
	
		add_action('wp_ajax_nopriv_merchant', array(&$this, 'ajax'));
		add_action('wp_ajax_merchant', array(&$this, 'ajax'));
	}
	
	public function init() {				
		$this->mActive=new merchant_manager();
	}
	
	function mz_merchant($args) {
		$this->init();
		echo $this->mActive->action('shortcode', $args);
	} 
	
	public function getClientInfo($userId, $ordderId, $isShow=false) {
		$this->init();
		return $this->mActive->mgetClientInfo($userId, $orderId, $isShow);
	}


	
	
	
	function ajax() {
		$class=$_GET['mc']; //FIXME !SROCHNO! zaschita
		$mc=new  $class();
		$mc->action('ajax', $_GET); 
		die(0);
	}
	
	public function getOrder($status) {
		global $wpdb;
		$opt=Options::getInstance(0);
		$listName='Bag';
		$buyer=Buyer::getInstance(0)->ID();
		$row=$wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.$opt->table_order.' WHERE `listName`="'.$listName.'" AND `userID`="'.$buyer.'" AND `status`='.$status));
		return $row;
	}
	
	
	public function setOrderStatus($id, $status) {
		global $wpdb;
		$wpdb->update(Options::getInstance()->table_order, array('status'=>$status), array('id'=>$id));
		return true;
	}
	
}
