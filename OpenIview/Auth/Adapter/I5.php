<?php
class Openiview_Auth_Adapter_I5 implements Zend_Auth_Adapter_Interface {
	/**
	 * Username
	 *
	 * @var string
	 */
	protected $username = null;
	
	/**
	 * Password
	 *
	 * @var string
	 */
	protected $password = null;
	
	/**
	 * Class constructor
	 *
	 * The constructor sets the username and password
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}
	
	/**
	 * Authenticate
	 *
	 * Authenticate the username and password
	 *
	 * @return Zend_Auth_Result
	 */
	public function authenticate() {
		
		$objDb = Zend_Db_Table_Abstract::getDefaultAdapter ();
		
		if ($objDb->isConnected ()) {
			// Disconnect
			$objDb->closeConnection ();
		}
		
		$objDb->setUsername ( $this->username );
		$objDb->setPassword ( $this->password );
		$objDb->setPrivateConnection ( 0 );
		
		try {
			$objDb->getConnection ();
			
			// Initialize return values
			$code = Zend_Auth_Result::FAILURE;
			$identity = null;
			$messages = array ();
			
			if ($objDb->isConnected ()) {
				// Disconnect
				$code = Zend_Auth_Result::SUCCESS;
				$identity = $this->username;
			} else {
				$messages [] = 'Authentication error';
			}
		} catch ( Exception $objE ) {
			$messages[] = $objE->getMessage () . "..";
		}
		
		return new Zend_Auth_Result ( $code, $identity, $messages );
	}
	
}