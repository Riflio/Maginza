<?php

class Lots extends Lot {
	
	
	function __construct(){
		add_action('the_post', array(&$this, 'the_post'),100,100);
	}
	
	public function the_post(&$postData) {		
		if ($postData->post_type!='lots') return;
 		Lot::getInstance($postData);
	}
	

}



?>