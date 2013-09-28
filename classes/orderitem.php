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
            self::$instance = new OrderItem();
        }
        return self::$instance;
    }

    public function setItemID($itemID) {
        $inst=OrderItem::getInstance();
        $inst->itemID=$itemID;
    }

    /**
     *
     */
    public function getItem() {
        global $wpdb;
        $inst=OrderItem::getInstance();
        $table_order_items=Options::$table_order_items;
        $item=$wpdb->get_row("SELECT * FROM {$table_order_items} WHERE orderItemsID={$inst->itemID} LIMIT 1");
        return $item;
    }

    /**
     *  Переопределяем функию полученея значения метаопции,
     *  отдаём значение из позиции заказа
     *  если значения нет, отдаём значение от лота
     *
     */
    function getMetaValue($lot, $metaName) {
        $inst=OrderItem::getInstance();
        $item=$inst->getItem();
        $metaOpts=unserialize($item->metaOptions);
        $metaval=($metaOpts[$metaName])? $metaOpts[$metaName] : parent::getMetaValue( $item->orderItemID, $metaName);
        return  $metaval;
    }



}