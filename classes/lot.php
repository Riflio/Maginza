<?php
/*
 * Всё, что касается товара
 */

class Lot extends Meta  {
	public $LOT;
	
	
	private static $instance;
	
	public static function getInstance($lot=-1) {
		if ( is_null(self::$instance) ) {
			self::$instance = new Lot();
		}
		if ($lot!=-1) {
			if (is_object($lot)) self::$instance->LOT=$lot;
			if (is_numeric($lot)) self::$instance->LOT=get_post($lot);
		}		
		return self::$instance;
	}
    
	public function ID() {
		$inst=Lot::getInstance();
		return $inst->LOT->ID;
	}
			
	public function theMeta($metaName) {
		$inst=Lot::getInstance();
		$inst->theMetaValue($inst->LOT, $metaName);
	}
	
	public function metaForm($exclude='') {
		$inst=Lot::getInstance();
		$inst->showMetaForm($inst->LOT, $exclude);
	}
	
}



?>