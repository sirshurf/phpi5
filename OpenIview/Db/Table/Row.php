<?php
class Openiview_Db_Table_Row extends Zend_Db_Table_Row {
	
	/**
	 * Allows pre-insert logic to be applied to row.
	 * Subclasses may override this method.
	 *
	 * @return void
	 */
	protected function _insert() {
		$this->created_on = time ();
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		
		if (! empty ( $objUserSessionData->tz )) {
			$this->created_by = $objUserSessionData->tz;
		} else {
			$this->created_by = '';
		}
	}
	
	/**
	 * Allows pre-update logic to be applied to row.
	 * Subclasses may override this method.
	 *
	 * @return void
	 */
	protected function _update() {
		
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		
		$this->updated_on = time ();
		$this->updated_by = $objUserSessionData->tz;
	
	}
	
	
	public function delete(){
		
		$this->is_deleted = 1;
		return $this->save (  );
	}
	
	public function isModified($columnName) {
		
		$columnName = $this->_transformColumn ( $columnName );
		if (! array_key_exists ( $columnName, $this->_data )) {
			require_once 'Zend/Db/Table/Row/Exception.php';
			throw new Zend_Db_Table_Row_Exception ( "Specified column \"$columnName\" is not in the row" );
		}
		return isset( $this->_modifiedFields[$columnName] );
	
	}
	
	/**
	 * Set row field value
	 *
	 * @param  string $columnName The column key.
	 * @param  mixed  $value      The value for the property.
	 * @return void
	 * @throws Zend_Db_Table_Row_Exception
	 */
	public function __set($columnName, $value) {
		$columnName = $this->_transformColumn ( $columnName );
		if (! array_key_exists ( $columnName, $this->_data )) {
			require_once 'Zend/Db/Table/Row/Exception.php';
			throw new Zend_Db_Table_Row_Exception ( "Specified column \"$columnName\" is not in the row" );
		}
		if ($this->_data [$columnName] !== $value) {
			$this->_data [$columnName] = $value;
			$this->_modifiedFields [$columnName] = true;
		}
	}

}