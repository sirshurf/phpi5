<?php
class OpenIview_Db_Table_Select extends Zend_Db_Table_Select {
	//Overide function _renderColumns
	/**
	 * Render DISTINCT clause
	 *
	 * @param string   $sql SQL query
	 * @return string|null
	 */
	protected function _renderColumns($sql) {
		if (! count ( $this->_parts [self::COLUMNS] )) {
			return null;
		}
		
		$columns = array ();
		foreach ( $this->_parts [self::COLUMNS] as $columnEntry ) {
			list ( $correlationName, $column, $alias ) = $columnEntry;
			if ($column instanceof Zend_Db_Expr) {
				$columns [] = $this->_adapter->quoteColumnAs ( $column, $alias, true );
			} else {
				if ($column == self::SQL_WILDCARD) {
					$column = new Zend_Db_Expr ( self::SQL_WILDCARD );
					$alias = null;
				}
				//if (empty ( $correlationName )) {
					$columns [] = $this->_adapter->quoteColumnAs ( $column, $alias, true );
				//} else {
				//	$columns [] = $this->_adapter->quoteColumnAs ( array ($correlationName, $column ), $alias, true );
				//}
			}
		}
		
		return $sql .= ' ' . implode ( ', ', $columns );
	}

}
?>