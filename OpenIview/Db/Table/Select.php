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

 /**
     * Adds a FROM table and optional columns to the query.
     *
     * The table name can be expressed
     *
     * @param  array|string|Zend_Db_Expr|Zend_Db_Table_Abstract $name The table name or an
                                                                      associative array relating
                                                                      table name to correlation
                                                                      name.
     * @param  array|string|Zend_Db_Expr $cols The columns to select from this table.
     * @param  string $schema The schema name to specify, if any.
     * @return Zend_Db_Table_Select This Zend_Db_Table_Select object.
     */
    public function from($name, $cols = self::SQL_WILDCARD, $schema = null)
    {
        if ($name instanceof Zend_Db_Table_Abstract) {
            $info = $name->info();
            $name = $info[Zend_Db_Table_Abstract::NAME];
            if (isset($info[Zend_Db_Table_Abstract::SCHEMA])) {
                $schema = $info[Zend_Db_Table_Abstract::SCHEMA];
            }
        }
		
        return $this->joinInner(array($name), null, $cols, $schema);
    }
    


}