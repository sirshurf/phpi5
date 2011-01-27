<?php

class Openiview_Db_Table_General extends Zend_Db_Table {
	
	/**
	 * Default Table name, takes it from the Const, (if exists)
	 * @var unknown_type
	 */
	protected $_name = self::TBL_NAME;
	
	protected $_rowClass = 'Openiview_Db_Table_Row';
	
	protected $strInitSql = "";
	
	public function insert($data) {
		$data ['created_on'] = time ();
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		
		if (! empty ( $objUserSessionData->tz )) {
			$data ['created_by'] = $objUserSessionData->tz;
		} else {
			$data ['created_by'] = '';
		}
		return parent::insert ( $data );
	}
	
	public function update($data, $where) {
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		
		$data ['updated_on'] = time ();
		$data ['updated_by'] = $objUserSessionData->tz;
		
		return parent::update ( $data, $where );
	}
	
	public function delete($where) {
		$data ['is_delited'] = 1;
		$this->update ( $data, $where );
	}
	
	/**
	 * Find one row, by index, if nto found return empty row element
	 * 
	 * @param mix $nbmData
	 * @return Zend_Db_Table_Row
	 */
	public function findOne($nbmData) {
		$objRowSet = $this->find ( $nbmData );
		if ($objRowSet->count () > 0) {
			return $this->find ( $nbmData )->getRow ( 0 );
		} else {
			return $this->createRow ();
		}
	
	}
	
	public static function getName() {
		$objClass = new self ();
		return $objClass->_name;
	}
	
	/**
	 * 
	 * @param $objTable
	 * @param $objReflection
	 */
	public static function initTable(&$objTable, &$objReflection) {
		$objReflection = new ReflectionClass(get_called_class());
			$strClassName = $objReflection->getName();
			/**
			 * 
			 * @var  Openiview_Db_Table_General
			 */
			$objTable = new $strClassName ();
			if (!($objTable  instanceof Openiview_Db_Table_General)) {
				unset($objTable);
			}
	}
}