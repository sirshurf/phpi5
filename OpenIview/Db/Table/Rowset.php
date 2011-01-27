<?php
class OpenIview_Db_Table_Rowset extends Zend_Db_Table_Rowset {
	
	/**
	 * Grand Total
	 *
	 * @var int
	 */
	protected $_total;
	
	
	/**
	 * Constructor, added TOTAL handling.
	 *
	 * @param array $config
	 */
	public function __construct(array $config){
		
		parent::__construct($config);
		
		if (isset($config['total'])){
			$this->_total = (int)$config['total'];
		} else {
			$this->_total = $this->_count;
		}
	}
	
	public function total()
	{
		return $this->_total;
	}
	
	
}
?>