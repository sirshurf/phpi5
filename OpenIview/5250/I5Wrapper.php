<?php
require_once('Zend/5250/I5Wrapper.php');

class Openiview_5250_I5Wrapper extends Zend_5250_I5Wrapper{

	public function __construct($resConnection){
		
		//parent::__construct();
		
		$this->i5_resource = $resConnection;
	}

}
