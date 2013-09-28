<?php
/**
 *
 *
 */

class OrderItem extends Combinations{
    private static $instance;
    public $itemID;

    function __construct() {

    }

    public static function getInstance() {
        if ( is_null(self::$instance) ) {
            self::$instance = new Lot();
        }
        return self::$instance;
    }

    public function setItemID($itemID) {
        $inst=OrderItem::getInstance();
        $inst->itemID=$itemID;
    }


    /**
     *  Переопределяем функию полученея значения метаопции, отдаём значение из позиции заказа.
     *
     */
    function getMetaValue($lot, $metaName) {
        $inst=OrderItem::getInstance();
        return  $inst->itemID;
    }



}