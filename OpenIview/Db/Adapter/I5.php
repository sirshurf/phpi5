<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Db2.php 23199 2010-10-21 14:27:06Z ralph $
 *
 */

/**
 * @see Zend_Db
 */
require_once 'Zend/Db.php';

/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * @see Iscar2_Db_Statement_Db2
 */
require_once 'Openiview/Db/Statement/I5.php';

/**
 * @package    Zend_Db
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Openiview_Db_Adapter_I5 extends Zend_Db_Adapter_Abstract {
	/**
	 * User-provided configuration.
	 *
	 * Basic keys are:
	 *
	 * username   => (string)  Connect to the database as this username.
	 * password   => (string)  Password associated with the username.
	 * host       => (string)  What host to connect to (default 127.0.0.1)
	 * dbname     => (string)  The name of the database to user
	 * protocol   => (string)  Protocol to use, defaults to "TCPIP"
	 * port       => (integer) Port number to use for TCP/IP if protocol is "TCPIP"
	 * persistent => (boolean) Set TRUE to use a persistent connection (db2_pconnect)
	 * os         => (string)  This should be set to 'i5' if the db is on an os400/i5
	 * schema     => (string)  The default schema the connection should use
	 *
	 *I5_OPTIONS_IDLE_TIMEOUT  - sets the time ( in seconds) to raise an event when the connection can be closed by another connection request (i5_connect or i5_pconnect). If this option is not used the connection job will remain open.

I5_OPTIONS_PRIVATE_CONNECTION - Set the ID of the persistent connection to reuse.

I5_OPTIONS_JOBNAME - job name (machine name by default).

I5_OPTIONS_SQLNAMING - Enables using dotted (.) or slashed (/) notation in SQL requests.

I5_OPTIONS_DECIMALPOINT - Enables using dot or comma as decimal separator.

I5_OPTIONS_CODEPAGEFILE - Enables using specific code page (CCSID).

I5_OPTIONS_ALIAS - Enables naming a connection. If the name is used in another i5_connect, then the other i5_connect will use the same connection.

I5_OPTIONS_INITLIBL - Specified libraries are added to the beginning of the initial library list.

I5_OPTIONS_LOCALCP - Sets the local code page used by PHP application. 

I5_OPTIONS_RMTCCSID - Sets the EBCDIC CCSID


	 *
	 * @var array
	 */
	protected $_config = array ('dbname' => null, 'username' => null, 'password' => null, 'host' => 'localhost', 'port' => '50000', 'protocol' => 'TCPIP', 'persistent' => false, 'os' => null, 'schema' => null );
	
	/**
	 * Execution mode
	 *
	 * @var int execution flag (DB2_AUTOCOMMIT_ON or DB2_AUTOCOMMIT_OFF)
	 */
	protected $_execute_mode = DB2_AUTOCOMMIT_ON;
	
	/**
	 * Default class name for a DB statement.
	 *
	 * @var string
	 */
	protected $_defaultStmtClass = 'Openiview_Db_Statement_I5';
	protected $_isI5 = false;
	
	/**
	 * Keys are UPPERCASE SQL datatypes or the constants
	 * Zend_Db::INT_TYPE, Zend_Db::BIGINT_TYPE, or Zend_Db::FLOAT_TYPE.
	 *
	 * Values are:
	 * 0 = 32-bit integer
	 * 1 = 64-bit integer
	 * 2 = float or decimal
	 *
	 * @var array Associative array of datatypes to values 0, 1, or 2.
	 */
	protected $_numericDataTypes = array (Zend_Db::INT_TYPE => Zend_Db::INT_TYPE, Zend_Db::BIGINT_TYPE => Zend_Db::BIGINT_TYPE, Zend_Db::FLOAT_TYPE => Zend_Db::FLOAT_TYPE, 'INTEGER' => Zend_Db::INT_TYPE, 'SMALLINT' => Zend_Db::INT_TYPE, 'BIGINT' => Zend_Db::BIGINT_TYPE, 'DECIMAL' => Zend_Db::FLOAT_TYPE, 'NUMERIC' => Zend_Db::FLOAT_TYPE );
	
	/**
	 * Creates a connection resource.
	 *
	 * @return void
	 */
	protected function _connect() {
		if (is_resource ( $this->_connection )) {
			// connection already exists
			return;
		}
		
		//  :TODO Switch to the correct extension
		if (! extension_loaded ( 'ibm_db2' )) {
			/**
			 * @see Iscar2_Db_Adapter_I5_Exception
			 */
			require_once 'Iscar2/Db/Adapter/I5/Exception.php';
			throw new Iscar2_Db_Adapter_I5_Exception ( 'The IBM DB2 extension is required for this adapter but the extension is not loaded' );
		}
		
		$this->_determineI5 ();
		if ($this->_config ['persistent']) {
			// use persistent connection
			$conn_func_name = 'i5_pconnect';
		} else {
			// use "normal" connection
			$conn_func_name = 'i5_connect';
		}
		
		if (! isset ( $this->_config ['driver_options'] ['autocommit'] )) {
			// set execution mode
			$this->_config ['driver_options'] ['autocommit'] = &$this->_execute_mode;
		}
		
		if (isset ( $this->_config ['options'] [Zend_Db::CASE_FOLDING] )) {
			$caseAttrMap = array (Zend_Db::CASE_NATURAL => DB2_CASE_NATURAL, Zend_Db::CASE_UPPER => DB2_CASE_UPPER, Zend_Db::CASE_LOWER => DB2_CASE_LOWER );
			$this->_config ['driver_options'] ['DB2_ATTR_CASE'] = $caseAttrMap [$this->_config ['options'] [Zend_Db::CASE_FOLDING]];
		}
		
	
		if ( isset ( $this->_config ['driver_options'] ['jobname'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_JOBNAME] = $this->_config ['driver_options'] ['jobname'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['sqlnaming'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_SQLNAMING] = $this->_config ['driver_options'] ['sqlnaming'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['decimalpoint'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_DECIMALPOINT] = $this->_config ['driver_options'] ['decimalpoint'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['codepagefile'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_CODEPAGEFILE] = $this->_config ['driver_options'] ['codepagefile'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['alias'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_ALIAS] = $this->_config ['driver_options'] ['alias'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['initlibll'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_INITLIBL] = $this->_config ['driver_options'] ['initlibll'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['localcp'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_LOCALCP] = $this->_config ['driver_options'] ['localcp'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['rmtccsid'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_RMTCCSID] = $this->_config ['driver_options'] ['rmtccsid'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['idletimeout'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_IDLE_TIMEOUT] = $this->_config ['driver_options'] ['idletimeout'];
		}
		
		if ( isset ( $this->_config ['driver_options'] ['privatecon'] )) {
			// set execution mode
			$this->_config ['driver_options'] [I5_OPTIONS_PRIVATE_CONNECTION] = $this->_config ['driver_options'] ['privatecon'];
		}
	
		$this->_config ['driver_options'] ['i5_naming'] = DB2_I5_NAMING_ON;
		
		$this->_connection = @$conn_func_name ( $this->_config ['dbname'], $this->_config ['username'], $this->_config ['password'], $this->_config ['driver_options'] );
		
		// check the connection
		if (! $this->_connection) {
			/**
			 * @see Iscar2_Db_Adapter_I5_Exception
			 */
			require_once 'Openiview/Db/Adapter/I5/Exception.php';
			throw new Openiview_Db_Adapter_I5_Exception ( i5_errormsg (), i5_errno () );
		}
	}
	
	/**
	 * Test if a connection is active
	 *
	 * @return boolean
	 */
	public function isConnected() {
		return (( bool ) (is_resource ( $this->_connection ) && get_resource_type ( $this->_connection ) == 'easycom link'));
	}

	/**
	 * Force the connection to close.
	 *
	 * @return void
	 */
	public function closeConnection() {
		if ($this->isConnected ()) {
			i5_close ( $this->_connection );
		}
		$this->_connection = null;
	}
	
	/**
	 * Force the private connection to close.
	 *
	 * @return void
	 */
	public function closePrivateConnection() {
		if ($this->isConnected ()) {
			i5_pclose ( $this->_connection );
		}
		$this->_connection = null;
	}
	
	/**
	 * Force the connection to close.
	 *
	 * @return void
	 */
	public function connect() {
		$this->_connect ();
	}
	
	/**
	 * Force the connection to close.
	 *
	 * @return void
	 */
	public function get_connection() {
		if (!$this->isConnected ()) {
			$this->_connect ();
		}
		return $this->_connection;
	}
	
	/**
	 * Returns an SQL statement for preparation.
	 *
	 * @param string $sql The SQL statement with placeholders.
	 * @return Zend_Db_Statement_Db2
	 */
	public function prepare($sql) {
		$this->_connect ();
		$stmtClass = $this->_defaultStmtClass;
		if (! class_exists ( $stmtClass )) {
			require_once 'Zend/Loader.php';
			Zend_Loader::loadClass ( $stmtClass );
		}
		$stmt = new $stmtClass ( $this, $sql );
		$stmt->setFetchMode ( $this->_fetchMode );
		return $stmt;
	}
	
	/**
	 * Gets the execution mode
	 *
	 * @return int the execution mode (DB2_AUTOCOMMIT_ON or DB2_AUTOCOMMIT_OFF)
	 */
	public function _getExecuteMode() {
		return $this->_execute_mode;
	}
	
	/**
	 * @param integer $mode
	 * @return void
	 */
	public function _setExecuteMode($mode) {
		// :TODO check this
		return;
		switch ($mode) {
			case DB2_AUTOCOMMIT_OFF :
			case DB2_AUTOCOMMIT_ON :
				$this->_execute_mode = $mode;
				db2_autocommit ( $this->_connection, $mode );
				break;
			default :
				/**
				 * @see Iscar2_Db_Adapter_I5_Exception
				 */
				require_once 'Iscar2/Db/Adapter/I5/Exception.php';
				throw new Iscar2_Db_Adapter_Db2_Exception ( "execution mode not supported" );
				break;
		}
	}
	
	/**
	 * Quote a raw string.
	 *
	 * @param string $value     Raw string
	 * @return string           Quoted string
	 */
	protected function _quote($value) {
		if (is_int ( $value ) || is_float ( $value )) {
			return $value;
		}
		/**
		 * Use db2_escape_string() if it is present in the IBM DB2 extension.
		 * But some supported versions of PHP do not include this function,
		 * so fall back to default quoting in the parent class.
		 */
		if (function_exists ( 'db2_escape_string' )) {
			return "'" . db2_escape_string ( $value ) . "'";
		}
		return parent::_quote ( $value );
	}
	
	/**
	 * @return string
	 */
	public function getQuoteIdentifierSymbol() {
		$this->_connect ();
		$identQuote = " ";
		
		return $identQuote;
	}
	
	/**
	 * Returns a list of the tables in the database.
	 * @param string $schema OPTIONAL
	 * @return array
	 */
	public function listTables($schema = null) {
		
		/**
		 * @see Iscar2_Db_Adapter_I5_Exception
		 */
		require_once 'Iscar2/Db/Adapter/I5/Exception.php';
		throw new Iscar2_Db_Adapter_Db2_Exception ( "execution mode not supported" );
		
		$this->_connect ();
		
		if ($schema === null && $this->_config ['schema'] != null) {
			$schema = $this->_config ['schema'];
		}
		
		$tables = array ();
		
		if (! $this->_isI5) {
			if ($schema) {
				$stmt = db2_tables ( $this->_connection, null, $schema );
			} else {
				$stmt = db2_tables ( $this->_connection );
			}
			while ( $row = db2_fetch_assoc ( $stmt ) ) {
				$tables [] = $row ['TABLE_NAME'];
			}
		} else {
			$tables = $this->_i5listTables ( $schema );
		}
		
		return $tables;
	}
	
	/**
	 * Returns the column descriptions for a table.
	 *
	 * The return value is an associative array keyed by the column name,
	 * as returned by the RDBMS.
	 *
	 * The value of each array element is an associative array
	 * with the following keys:
	 *
	 * SCHEMA_NAME      => string; name of database or schema
	 * TABLE_NAME       => string;
	 * COLUMN_NAME      => string; column name
	 * COLUMN_POSITION  => number; ordinal position of column in table
	 * DATA_TYPE        => string; SQL datatype name of column
	 * DEFAULT          => string; default expression of column, null if none
	 * NULLABLE         => boolean; true if column can have nulls
	 * LENGTH           => number; length of CHAR/VARCHAR
	 * SCALE            => number; scale of NUMERIC/DECIMAL
	 * PRECISION        => number; precision of NUMERIC/DECIMAL
	 * UNSIGNED         => boolean; unsigned property of an integer type
	 * DB2 not supports UNSIGNED integer.
	 * PRIMARY          => boolean; true if column is part of the primary key
	 * PRIMARY_POSITION => integer; position of column in primary key
	 * IDENTITY         => integer; true if column is auto-generated with unique values
	 *
	 * @param string $tableName
	 * @param string $schemaName OPTIONAL
	 * @return array
	 */
	public function describeTable($tableName, $schemaName = null) {
		// Ensure the connection is made so that _isI5 is set
		$this->_connect ();
		
		if ($schemaName === null && $this->_config ['schema'] != null) {
			$schemaName = $this->_config ['schema'];
		}
		
		// DB2 On I5 specific query
		$sql = "SELECT DISTINCT C.TABLE_SCHEMA, C.TABLE_NAME, C.COLUMN_NAME, C.ORDINAL_POSITION,
                C.DATA_TYPE, C.COLUMN_DEFAULT, C.NULLS ,C.LENGTH, C.SCALE, LEFT(C.IDENTITY,1),
                LEFT(tc.TYPE, 1) AS tabconsttype, k.COLSEQ
                FROM QSYS2.SYSCOLUMNS C
                LEFT JOIN (QSYS2.syskeycst k JOIN QSYS2.SYSCST tc
                    ON (k.TABLE_SCHEMA = tc.TABLE_SCHEMA
                      AND k.TABLE_NAME = tc.TABLE_NAME
                      AND LEFT(tc.type,1) = 'P'))
                    ON (C.TABLE_SCHEMA = k.TABLE_SCHEMA
                       AND C.TABLE_NAME = k.TABLE_NAME
                       AND C.COLUMN_NAME = k.COLUMN_NAME)
                WHERE " . $this->quoteInto ( 'UPPER(C.TABLE_NAME) = UPPER(?)', $tableName );
		
		if ($schemaName) {
			$sql .= $this->quoteInto ( ' AND UPPER(C.TABLE_SCHEMA) = UPPER(?)', $schemaName );
		}
		
		$sql .= " ORDER BY C.ORDINAL_POSITION FOR FETCH ONLY";
		
		$desc = array ();
		$stmt = $this->query ( $sql );
		
		/**
		 * To avoid case issues, fetch using FETCH_NUM
		 */
		$result = $stmt->fetchAll ( Zend_Db::FETCH_NUM );
		
		/**
		 * The ordering of columns is defined by the query so we can map
		 * to variables to improve readability
		 */
		$tabschema = 0;
		$tabname = 1;
		$colname = 2;
		$colno = 3;
		$typename = 4;
		$default = 5;
		$nulls = 6;
		$length = 7;
		$scale = 8;
		$identityCol = 9;
		$tabconstType = 10;
		$colseq = 11;
		
		foreach ( $result as $key => $row ) {
			list ( $primary, $primaryPosition, $identity ) = array (false, null, false );
			if ($row [$tabconstType] == 'P') {
				$primary = true;
				$primaryPosition = $row [$colseq];
			}
			/**
			 * In IBM DB2, an column can be IDENTITY
			 * even if it is not part of the PRIMARY KEY.
			 */
			if ($row [$identityCol] == 'Y') {
				$identity = true;
			}
			
			// only colname needs to be case adjusted
			$desc [$this->foldCase ( $row [$colname] )] = array ('SCHEMA_NAME' => $this->foldCase ( $row [$tabschema] ), 'TABLE_NAME' => $this->foldCase ( $row [$tabname] ), 'COLUMN_NAME' => $this->foldCase ( $row [$colname] ), 'COLUMN_POSITION' => (! $this->_isI5) ? $row [$colno] + 1 : $row [$colno], 'DATA_TYPE' => $row [$typename], 'DEFAULT' => $row [$default], 'NULLABLE' => ( bool ) ($row [$nulls] == 'Y'), 'LENGTH' => $row [$length], 'SCALE' => $row [$scale], 'PRECISION' => ($row [$typename] == 'DECIMAL' ? $row [$length] : 0), 'UNSIGNED' => false, 'PRIMARY' => $primary, 'PRIMARY_POSITION' => $primaryPosition, 'IDENTITY' => $identity );
		}
		
		return $desc;
	}
	
	/**
	 * Return the most recent value from the specified sequence in the database.
	 * This is supported only on RDBMS brands that support sequences
	 * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
	 *
	 * @param string $sequenceName
	 * @return string
	 */
	public function lastSequenceId($sequenceName) {
		$this->_connect ();
		
		$quotedSequenceName = $sequenceName;
		$sql = 'SELECT PREVVAL FOR ' . $this->quoteIdentifier ( $sequenceName, true ) . ' AS VAL FROM QSYS2.QSQPTABL';
		
		$value = $this->fetchOne ( $sql );
		return ( string ) $value;
	}
	
	/**
	 * Generate a new value from the specified sequence in the database, and return it.
	 * This is supported only on RDBMS brands that support sequences
	 * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
	 *
	 * @param string $sequenceName
	 * @return string
	 */
	public function nextSequenceId($sequenceName) {
		$this->_connect ();
		$sql = 'SELECT NEXTVAL FOR ' . $this->quoteIdentifier ( $sequenceName, true ) . ' AS VAL FROM SYSIBM.SYSDUMMY1';
		$value = $this->fetchOne ( $sql );
		return ( string ) $value;
	}
	
	/**
	 * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
	 *
	 * As a convention, on RDBMS brands that support sequences
	 * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
	 * from the arguments and returns the last id generated by that sequence.
	 * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
	 * returns the last value generated for such a column, and the table name
	 * argument is disregarded.
	 *
	 * The IDENTITY_VAL_LOCAL() function gives the last generated identity value
	 * in the current process, even if it was for a GENERATED column.
	 *
	 * @param string $tableName OPTIONAL
	 * @param string $primaryKey OPTIONAL
	 * @param string $idType OPTIONAL used for i5 platform to define sequence/idenity unique value
	 * @return string
	 */
	
	public function lastInsertId($tableName = null, $primaryKey = null, $idType = null) {
		$this->_connect ();
		
		return ( string ) $this->_i5LastInsertId ( $tableName, $idType );
	
	}
	
	/**
	 * Begin a transaction.
	 *
	 * @return void
	 */
	protected function _beginTransaction() {
		$this->_setExecuteMode ( DB2_AUTOCOMMIT_OFF );
	}
	
	/**
	 * Commit a transaction.
	 *
	 * @return void
	 */
	protected function _commit() {
		if (! i5_commit ( $this->_connection )) {
			/**
			 * @see Iscar2_Db_Adapter_I5_Exception
			 */
			require_once 'Iscar2/Db/Adapter/I5/Exception.php';
			throw new Iscar2_Db_Adapter_I5_Exception ( i5_errormsg ( $this->_connection ), i5_errno ( $this->_connection ) );
		}
		
		$this->_setExecuteMode ( DB2_AUTOCOMMIT_ON );
	}
	
	/**
	 * Rollback a transaction.
	 *
	 * @return void
	 */
	protected function _rollBack() {
		if (! i5_rollback ( $this->_connection )) {
			/**
			 * @see Iscar2_Db_Adapter_I5_Exception
			 */
			require_once 'Iscar2/Db/Adapter/I5/Exception.php';
			throw new Iscar2_Db_Adapter_I5_Exception ( i5_errormsg ( $this->_connection ), i5_errno ( $this->_connection ) );
		}
		$this->_setExecuteMode ( DB2_AUTOCOMMIT_ON );
	}
	
	/**
	 * Set the fetch mode.
	 *
	 * @param integer $mode
	 * @return void
	 * @throws Iscar2_Db_Adapter_I5_Exception
	 */
	public function setFetchMode($mode) {
		switch ($mode) {
			case Zend_Db::FETCH_NUM : // seq array
			case Zend_Db::FETCH_ASSOC : // assoc array
			case Zend_Db::FETCH_BOTH : // seq+assoc array
			case Zend_Db::FETCH_OBJ : // object
				$this->_fetchMode = $mode;
				break;
			case Zend_Db::FETCH_BOUND : // bound to PHP variable
				/**
				 * @see Iscar2_Db_Adapter_I5_Exception
				 */
				require_once 'Iscar2/Db/Adapter/I5/Exception.php';
				throw new Iscar2_Db_Adapter_I5_Exception ( 'FETCH_BOUND is not supported yet' );
				break;
			default :
				/**
				 * @see Iscar2_Db_Adapter_I5_Exception
				 */
				require_once 'Iscar2/Db/Adapter/I5/Exception.php';
				throw new Iscar2_Db_Adapter_I5_Exception ( "Invalid fetch mode '$mode' specified" );
				break;
		}
	}
	
	/**
	 * Adds an adapter-specific LIMIT clause to the SELECT statement.
	 *
	 * @param string $sql
	 * @param integer $count
	 * @param integer $offset OPTIONAL
	 * @return string
	 */
	public function limit($sql, $count, $offset = 0) {
		$count = intval ( $count );
		if ($count <= 0) {
			/**
			 * @see Iscar2_Db_Adapter_I5_Exception
			 */
			require_once 'Iscar2/Db/Adapter/I5/Exception.php';
			throw new Iscar2_Db_Adapter_I5_Exception ( "LIMIT argument count=$count is not valid" );
		}
		
		$offset = intval ( $offset );
		if ($offset < 0) {
			/**
			 * @see Iscar2_Db_Adapter_I5_Exception
			 */
			require_once 'Iscar2/Db/Adapter/I5/Exception.php';
			throw new Iscar2_Db_Adapter_I5_Exception ( "LIMIT argument offset=$offset is not valid" );
		}
		
		if ($offset == 0) {
			$limit_sql = $sql . " FETCH FIRST $count ROWS ONLY";
			return $limit_sql;
		}
		
		/**
		 * DB2 does not implement the LIMIT clause as some RDBMS do.
		 * We have to simulate it with subqueries and ROWNUM.
		 * Unfortunately because we use the column wildcard "*",
		 * this puts an extra column into the query result set.
		 */
		$limit_sql = "SELECT z2.*
            FROM (
                SELECT ROW_NUMBER() OVER() AS \"ZEND_DB_ROWNUM\", z1.*
                FROM (
                    " . $sql . "
                ) z1
            ) z2
            WHERE z2.zend_db_rownum BETWEEN " . ($offset + 1) . " AND " . ($offset + $count);
		return $limit_sql;
	}
	
	/**
	 * Check if the adapter supports real SQL parameters.
	 *
	 * @param string $type 'positional' or 'named'
	 * @return bool
	 */
	public function supportsParameters($type) {
		if ($type == 'positional') {
			return true;
		}
		
		// if its 'named' or anything else
		return false;
	}
	
	/**
	 * Retrieve server version in PHP style
	 *
	 * @return string
	 */
	public function getServerVersion() {
		/**
		 * @see Iscar2_Db_Adapter_I5_Exception
		 */
		require_once 'Iscar2/Db/Adapter/I5/Exception.php';
		throw new Iscar2_Db_Adapter_I5_Exception ( "Version request not valid" );
		
		$this->_connect ();
		$server_info = db2_server_info ( $this->_connection );
		if ($server_info !== false) {
			$version = $server_info->DBMS_VER;
			if ($this->_isI5) {
				$version = ( int ) substr ( $version, 0, 2 ) . '.' . ( int ) substr ( $version, 2, 2 ) . '.' . ( int ) substr ( $version, 4 );
			}
			return $version;
		} else {
			return null;
		}
	}
	
	/**
	 * Return whether or not this is running on i5
	 *
	 * @return bool
	 */
	public function isI5() {
		
		return true;
	}
	
	/**
	 * Db2 On I5 specific method
	 *
	 * Returns a list of the tables in the database .
	 * Used only for DB2/400.
	 *
	 * @return array
	 */
	protected function _i5listTables($schema = null) {
		
		/**
		 * @see Iscar2_Db_Adapter_I5_Exception
		 */
		require_once 'Iscar2/Db/Adapter/I5/Exception.php';
		throw new Iscar2_Db_Adapter_I5_Exception ( "Not Valid on I5" );
		
		//list of i5 libraries.
		$tables = array ();
		if ($schema) {
			$tablesStatement = db2_tables ( $this->_connection, null, $schema );
			while ( $rowTables = db2_fetch_assoc ( $tablesStatement ) ) {
				if ($rowTables ['TABLE_NAME'] !== null) {
					$tables [] = $rowTables ['TABLE_NAME'];
				}
			}
		} else {
			$schemaStatement = db2_tables ( $this->_connection );
			while ( $schema = db2_fetch_assoc ( $schemaStatement ) ) {
				if ($schema ['TABLE_SCHEM'] !== null) {
					// list of the tables which belongs to the selected library
					$tablesStatement = db2_tables ( $this->_connection, NULL, $schema ['TABLE_SCHEM'] );
					if (is_resource ( $tablesStatement )) {
						while ( $rowTables = db2_fetch_assoc ( $tablesStatement ) ) {
							if ($rowTables ['TABLE_NAME'] !== null) {
								$tables [] = $rowTables ['TABLE_NAME'];
							}
						}
					}
				}
			}
		}
		
		return $tables;
	}
	
	protected function _i5LastInsertId($objectName = null, $idType = null) {
		
		if ($objectName === null) {
			$sql = 'SELECT IDENTITY_VAL_LOCAL() AS VAL FROM QSYS2.QSQPTABL';
			$value = $this->fetchOne ( $sql );
			return $value;
		}
		
		if (strtoupper ( $idType ) === 'S') {
			//check i5_lib option
			$sequenceName = $objectName;
			return $this->lastSequenceId ( $sequenceName );
		}
		
		//returns last identity value for the specified table
		//if (strtoupper($idType) === 'I') {
		$tableName = $objectName;
		return $this->fetchOne ( 'SELECT IDENTITY_VAL_LOCAL() from ' . $this->quoteIdentifier ( $tableName ) );
	}
	
	/**
	 * Check the connection parameters according to verify
	 * type of used OS
	 *
	 * @return void
	 */
	protected function _determineI5() {
		$this->_isI5 = true;
	
	}
	protected function _checkRequiredOptions(array $config) {
	}

	public function setUsername($strUsername){
		$this->_config['username'] = $strUsername;
	}
	public function setPassword($strPassword){
		$this->_config['password'] = $strPassword;
	}
	
	public function getUsername(){
		return $this->_config['username'];
	}
	public function getPassword(){
		return $this->_config['password'];
	}
	public function setPrivateConnection($strPrivateConnectionId){
		$this->_config['driver_options'] ['privatecon'] = $strPrivateConnectionId;
	}
	
	public function getPrivateConnection(){
		$this->_connect ();		
		return ( string ) $row = i5_get_property(I5_PRIVATE_CONNECTION, $this->_connection );
	
	}
	
	
	
	
		
	/**
     * Quote an identifier and an optional alias.
     *
     * @param string|array|Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An optional alias.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @param string $as The string to add between the identifier/expression and the alias.
     * @return string The quoted identifier and alias.
     */
    protected function _quoteIdentifierAs($ident, $alias = null, $auto = false, $as = ' AS ')
    {
        if ($ident instanceof Zend_Db_Expr) {
            $quoted = $ident->__toString();
        } elseif ($ident instanceof Zend_Db_Select) {
            $quoted = '(' . $ident->assemble() . ')';
        } else {
            if (is_string($ident)) {
                $ident = explode('.', $ident);
            }
            if (is_array($ident)) {
                $segments = array();
                foreach ($ident as $segment) {
                    if ($segment instanceof Zend_Db_Expr) {
                        $segments[] = $segment->__toString();
                    } else {
                        $segments[] = $this->_quoteIdentifier($segment, $auto);
                    }
                }
                //if ($alias !== null && end($ident) == $alias) {
                //    $alias = null;
                //}
                $quoted = implode('.', $segments);
            } else {
                $quoted = $this->_quoteIdentifier($ident, $auto);
            }
        }
		
        if ($alias !== null) {
            $quoted .= $as . $this->_quoteIdentifier($alias, $auto);
        }
		
        return $quoted;
    }

}


