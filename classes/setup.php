<?php
class Setup extends Options {
	
	function __construct($path) {
		register_activation_hook($path, array(&$this, 'install')); //-- ловушка на включение плагина
		//register_deactivation_hook($path, array(&$this, 'uninstall')); //-- на выключение
		
	}
	public function install() {

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		$sql = "CREATE TABLE " . Options::$table_order . " (
					  orderID mediumint(9) NOT NULL AUTO_INCREMENT,
					  userID varchar(255) NOT NULL,
					  orderStatus int NOT NULL,
					  orderDT datetime  NOT NULL,
					  UNIQUE KEY orderID (orderID)
		);";		
		dbDelta($sql);		
		//TODO: Добавить группу по умолчанию
		$sql = "CREATE TABLE " . Options::$table_meta_group . " (
					  groupID  mediumint(9) NOT NULL AUTO_INCREMENT, 
					  groupName varchar(50) NOT NULL,
					  groupTitle varchar(50) NOT NULL,						  
					  lotPriceFormula varchar(255) NOT NULL,			
					  UNIQUE KEY  groupID  ( groupID )
		);";
		dbDelta($sql);	
		//TODO: Добавить опции по умолчанию
		$sql = "CREATE TABLE " . Options::$table_meta_options . " (
					  id mediumint(9) NOT NULL AUTO_INCREMENT, 
					  optGroupID int NOT NULL,
					  optName varchar(50) NOT NULL,
					  optType varchar(50) NOT NULL,
					  optValue varchar(255),
					  optFormatter varchar(255),
					  optVisible bool NOT NULL,	
					  optClientEditable bool NOT NULL,					  
					  UNIQUE KEY id (id)
		);";		
		dbDelta($sql);		
		//
		$sql="CREATE TABLE ".Options::$table_meta." (
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			maginza_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY	(meta_id),
			KEY maginza_id (maginza_id),
			KEY meta_key (meta_key)
		) $charset_collate;";
		dbDelta($sql);
		
		//-- у каждого товара может быть несколько комбинаций, у каждой комбинации свои группы характеристик, у каждой группы свой список характеристик.
		
		//-- Таблица комбинаций товара
		$sql="CREATE TABLE ".Options::$table_combinations." (
			combinID bigint(20) unsigned NOT NULL auto_increment,
			lotID bigint(20) unsigned NOT NULL default '0',
			combinTitle varchar(255),
			combinArticle bigint(20),			
			PRIMARY KEY	(combinID)
		) $charset_collate;";
		dbDelta($sql);		
		
		//-- Таблица зависимотей айдишников названия группы, названий характеристик группы   К  конкретной комбинации товара
		$sql="CREATE TABLE ".Options::$table_combinations_rel." (
			combinRelID bigint(20) unsigned NOT NULL auto_increment,			
			combinRelCombinID bigint(20), 
			combinRelGroupId bigint(20),
			combinRelItemsID longtext,
			PRIMARY KEY	(combinRelID)
		) $charset_collate;";
		dbDelta($sql);

        //-- Таблица товаров заказа с выбранными комбинациями и метаопциями
        $sql="CREATE TABLE ".Options::$table_order_items." (
			orderItemsID bigint(20) unsigned NOT NULL auto_increment,
			orderID bigint(20),
			combinationID bigint(20),
			metaoptions longtext,
			PRIMARY KEY	orderItemsID (orderItemsID)
		) $charset_collate;";
        dbDelta($sql);
    }


	
	public function uninstall() {
		global $wpdb;		
		$sql='DROP TABLE  IF EXISTS '.Options::$table_order;
		$wpdb->query($sql);	
		$sql='DROP TABLE  IF EXISTS '.Options::$table_meta_group;
		$wpdb->query($sql);	
		$sql='DROP TABLE  IF EXISTS '.Options::$table_meta_options;
		$wpdb->query($sql);			
		$sql='DROP TABLE  IF EXISTS '.Options::$table_meta;		
		$wpdb->query($sql);			
		$sql='DROP TABLE  IF EXISTS '.Options::$table_combinations;
		$wpdb->query($sql);			
		$sql='DROP TABLE  IF EXISTS '.Options::$table_combinations_rel;
		$wpdb->query($sql);
        $sql='DROP TABLE  IF EXISTS '.Options::$table_order_items;
        $wpdb->query($sql);
	}
	
}

?>