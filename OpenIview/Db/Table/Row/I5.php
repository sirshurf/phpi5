<?php
class Openiview_Db_Table_Row_I5 extends Zend_Db_Table_Row {
	 
	 /**
     * Constructs where statement for retrieving row(s).
	 * Changed for I5
     *
     * @param bool $useDirty
     * @return array
     */
    protected function _getWhereQuery($useDirty = true)
    {
        $where = array();
        $db = $this->_getTable()->getAdapter();
        $primaryKey = $this->_getPrimaryKey($useDirty);
        $info = $this->_getTable()->info();
        $metadata = $info[Zend_Db_Table_Abstract::METADATA];

        // retrieve recently updated row using primary keys
        $where = array();
        foreach ($primaryKey as $column => $value) {
            $tableName = $db->quoteIdentifier($info[Zend_Db_Table_Abstract::NAME], true);
            $type = $metadata[$column]['DATA_TYPE'];
            $columnName = $db->quoteIdentifier($column, true);
            $where[] = $db->quoteInto("{$columnName} = ?", $value, $type);
        }
        return $where;
    }

}