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
		add_action('wp_login', array(&$this, 'wp_login'));	
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
            $user_info->login=$id;
            return $user_info;
        } else {
            $id=(isset($id))? $id : Buyer::ID();
        }


        $user_info->login=get_the_author_meta('user_login', $id);
        $user_info->url=get_the_author_meta('user_url', $id);

        return $user_info;


	}
	
	public function wp_login($u) {
		global $wpdb;		
	/*	if (is_admin()) return true;		
		$userInfo= get_userdatabylogin($u);				
		if (session_id()!="") { 
			//-- пробуем найти старый айдишник			
			$oldID=session_id();			
			$wpdb->update(Options::getInstance(0)->table_order, array('userID'=>$userInfo->ID), array('userID'=>$oldID, 'status'=>Options::getInstance(0)->status_onbuy), array("%s"),  array("%s", "%s"));		
		} 		*/		
		return true;			
	}
}
Buyer::getInstance();
?>