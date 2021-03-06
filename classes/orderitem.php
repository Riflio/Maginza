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

	/**
	*
	*
	*/
    public function setItemID($itemID) {
        $inst=OrderItem::getInstance();
        $inst->itemID=$itemID;
    }

    /**
     * Отдаём комбинацию(а в ней выбранные характеристики) позиции заказа
     */
    public function getCombination() {
        $inst=OrderItem::getInstance();
        $item=$inst->getItem();
        $comb=parent::getCombination($item->orderItemID, $item->combinationID);
        return $comb;
    }

    /**
     * Отдаём позицию заказа
     */
    public function getItem() {
        global $wpdb;
        $inst=OrderItem::getInstance();
        $table_order_items=Options::$table_order_items;
        $item=$wpdb->get_row("SELECT * FROM {$table_order_items} WHERE orderItemsID={$inst->itemID} LIMIT 1");
        return $item;
    }

    /**
     *  Возьмём значения по умолчанию опций лота  объединим с изменёнными значениями опций лота из текущего элемента заказа
     *  + объединим с текущими переданными значениями опций
     */
    public function orderItemMetaOptionsValues($lot, $customMetaOptions=array(), $onlyEditable=false) {
          $lotMetaOptions=$this->getLotMetaOptions($lot);
          $orderItemMetaOptions=array();
        foreach ($lotMetaOptions as $metaOpt) {
            if ($onlyEditable) {
                if ( $metaOpt->optClientEditable) {
                    $orderItemMetaOptions[$metaOpt->optName]=$this->getMetaValue($lot, $metaOpt->optName);
                }
            } else {
            	$orderItemMetaOptions[$metaOpt->optName]=$this->getMetaValue($lot, $metaOpt->optName);
            }
        }
        $metaOpts=array_merge($orderItemMetaOptions, $customMetaOptions);
        return $metaOpts;

    }
    
    /**
     * Отдайм данные для расчёта стоимости и прочего для js
     * 
     */
    public function getItemJSData($js=true) {
    	$inst=OrderItem::getInstance();
    	$item=$inst->getItem();
    	$lot=get_post($item->orderItemID);
    	
    	$data=array(
    			"meta"=> $this->orderItemMetaOptionsValues($lot),
    			"formula"=> $this->getLotFormula($lot)
    	);
    	
    	if ($js) {
    		return ('maginza.lotdata'.$inst->itemID.'='.json_encode($data).'');
    	}
    	
    	return $data;
    }
    /**
     * Отдаём конечную стоимость позиции товара
     *
     */
    function getItemTotalPrice($customMetaOptions=array()) {
        //TODO: Передать это в яваскрипт, для высчитывания формулы без аджакса
        $inst=OrderItem::getInstance();
        $item=$inst->getItem();
        $lot=get_post($item->orderItemID);
        //--
        $formula=$this->getLotFormula($lot);

        $metaOpts=$inst->orderItemMetaOptionsValues($lot, $customMetaOptions);

       
        
        $formula=str_replace(array_keys($metaOpts), array_values($metaOpts), $formula);

        
      
        $parser = new Parser($formula);

        return   $parser->run();
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
        $metaval=($metaOpts[$metaName]!=null)? $metaOpts[$metaName] : parent::getMetaValue($lot, $metaName);
        return  $metaval;
    }
     /**
      * Переопределяем функцию айдишника формы
      *
      */
    public function metaFormID($lot='') {
        $inst=OrderItem::getInstance();
        return "<input type='hidden' name='orderitemid' id='orderItemID' value='{$inst->itemID}'/>";
    }


}