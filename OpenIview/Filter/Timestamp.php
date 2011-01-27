<?php

require_once ('Zend/Filter/Interface.php');

class Openiview_Filter_Timestamp implements Zend_Filter_Interface {
	/**
	 * Filter element converts date in d/m/y to time stampe
	 * @param unknown_type $value
	 */
	public function filter($value) {
		//:TODO Hnadle different date formats by paraneters...		
		if (strstr ( $value, '/' ) !== false) {
			$arrTime = explode ( "/", $value );
			$valueFiltered = mktime ( 0, 0, 0, $arrTime [1], $arrTime [0], $arrTime [2] );
		} else {
			$valueFiltered = $value;
		}
		
		return $valueFiltered;
	
	}

}