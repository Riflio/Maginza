<?php
/* *  */
class Buyer extends Options {	
	private static $instance;	

    public static function getInstance() {
		if ( is_null(self::$instance) ) {			
			self::$instance = new Buyer();		
		}		
		return self::$instance;	
	}

	function __construct() {

	}

    /**
     *
     */
    public function ID() {
		global $current_user, $wpdb;

		get_currentuserinfo();
		if (is_user_logged_in()) {
			$ID=$current_user->ID;		
		}  else {
			if (!session_id())	session_start();
			$ID=session_id();
		}		
		return $ID;	
	}

    /**
     * Выдаём инфу о клиенту
     *
     */
    public function getInfo($id=NULL) {
        $user_info=(object) NULL;

        if (isset($id)&&!is_numeric($id)) { //-- Если задан айдишник и он айдишник сессии
            $user_info->user_login=$id;
            return $user_info;
        } else {
            $id=(isset($id))? $id : Buyer::ID();
        }


        $user_info=get_userdata($id);

        return $user_info;


	}

}
Buyer::getInstance();
?>