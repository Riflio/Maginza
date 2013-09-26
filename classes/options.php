<?php

class Options {
	private static $instance;


	public static $table_order='wp_maginza_order';
	public static $table_meta_group='wp_maginza_metagroup';
	public static $table_meta_options='wp_maginza_metaoptions';
	public static $table_meta='wp_maginzameta';
	public static $table_combinations='wp_maginza_combinations';
	public static $table_combinations_rel='wp_maginza_combinations_relationships';
	
	
	public function __construct() {
		global $wpdb;		
		$wpdb->maginzameta = Options::$table_meta;
	}
	
	
		
}


?>