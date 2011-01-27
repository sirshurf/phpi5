<?php
abstract class OpenIview_Db_Table_Abstract extends Zend_Db_Table_Abstract {
	
	public function __construct($config = array()){
		
		parent::__construct($config);
		
//		$this->get
		
	}
	
	
	//protected $_rowsetClass = "OpenIview_Db_Table_Rowset";	
	
	// Overide Select
	public function select(){
		return new OpenIview_Db_Table_Select($this);
	}
	
    /**
     * Fetches all rows.
     *
     * Honors the Zend_Db_Adapter fetch mode.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAll1($where = null, $order = null, $count = null, $offset = null)
    {
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            if ($count !== null || $offset !== null) {
                $select->limit($count, $offset);
            }

        } else {
            $select = $where;
        }

        $rows = $this->_fetch($select);

        $data  = array(
            'table'    => $this,
            'data'     => $rows,
            'readOnly' => $select->isReadOnly(),
            'rowClass' => $this->getRowClass(),
            'stored'   => true
        );
        
        if ($select->getPart(Zend_Db_Select::LIMIT_COUNT) || $select->getPart(Zend_Db_Select::LIMIT_OFFSET)) {
        	// Limit Exists, reset params and get count...
        	$select->reset(Zend_Db_Select::LIMIT_COUNT)
        			->reset(Zend_Db_Select::LIMIT_OFFSET)
        			->reset(Zend_Db_Select::ORDER)
        			->reset(Zend_Db_Select::COLUMNS)
        			->columns('COUNT(1)');
        	$data['total'] = (int) $this->_db->fetchOne($select);
        }

        $rowsetClass = $this->getRowsetClass();
        if (!class_exists($rowsetClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowsetClass);
        }
        return new $rowsetClass($data);
    }
	
}
?>