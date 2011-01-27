<?php
class OpenIview_Auth_Adapter implements Zend_Auth_Adapter_Interface {
	
	/**
	 * $_authenticateResultInfo
	 *
	 * @var array
	 */
	protected $_authenticateResultInfo = null;
	
	/**
	 * Constructor
	 *
	 * @param  array  $options  An array of arrays of Zend_Ldap options
	 * @param  string $username The username of the account being authenticated
	 * @param  string $password The password of the account being authenticated
	 * @return void
	 */
	public function __construct(array $options = array(), $username = null, $password = null) {
		$this->setOptions ( $options );
		if ($username !== null) {
			$this->setUsername ( $username );
		}
		if ($password !== null) {
			$this->setPassword ( $password );
		}
	}
	
	/**
	 * Returns the array of arrays of Zend_Ldap options of this adapter.
	 *
	 * @return array|null
	 */
	public function getOptions() {
		return $this->_options;
	}
	
	/**
	 * Sets the array of arrays of Zend_Ldap options to be used by
	 * this adapter.
	 *
	 * @param  array $options The array of arrays of Zend_Ldap options
	 * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
	 */
	public function setOptions($options) {
		$this->_options = is_array ( $options ) ? $options : array ();
		return $this;
	}
	
	/**
	 * Returns the username of the account being authenticated, or
	 * NULL if none is set.
	 *
	 * @return string|null
	 */
	public function getUsername() {
		return $this->_username;
	}
	
	/**
	 * Sets the username for binding
	 *
	 * @param  string $username The username for binding
	 * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
	 */
	public function setUsername($username) {
		$this->_username = strtoupper(( string ) $username);
		return $this;
	}
	
	/**
	 * Returns the password of the account being authenticated, or
	 * NULL if none is set.
	 *
	 * @return string|null
	 */
	public function getPassword() {
		return $this->_password;
	}
	
	/**
	 * Sets the passwort for the account
	 *
	 * @param  string $password The password of the account being authenticated
	 * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
	 */
	public function setPassword($password) {
		$this->_password = ( string ) $password;
		return $this;
	}
	
	/**
	 * setIdentity() - set the identity (username) to be used
	 *
	 * Proxies to {@see setUsername()}
	 *
	 * Closes ZF-6813
	 *
	 * @param  string $identity
	 * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
	 */
	public function setIdentity($identity) {
		return $this->setUsername ( $identity );
	}
	
	/**
	 * setCredential() - set the credential (password) value to be used
	 *
	 * Proxies to {@see setPassword()}
	 *
	 * Closes ZF-6813
	 *
	 * @param  string $credential
	 * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
	 */
	public function setCredential($credential) {
		return $this->setPassword ( $credential );
	}
	
	/**
	 * Authenticate the user
	 *
	 * @throws Zend_Auth_Adapter_Exception
	 * @return Zend_Auth_Result
	 */
	public function authenticate() {
		
		$messages = array ();
		$messages [0] = ''; // reserved
		$messages [1] = ''; // reserved
		

		$username = $this->_username;
		$password = $this->_password;
		
		if (! $username) {
			$code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
			$messages [0] = 'A username is required';
			return new Zend_Auth_Result ( $code, '', $messages );
		}
		if (! $password) {
			/* A password is required because some servers will
             * treat an empty password as an anonymous bind.
             */
			$code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
			$messages [0] = 'A password is required';
			return new Zend_Auth_Result ( $code, '', $messages );
		}
		
		$objZend = new Zend_5250 ( );
		$objResource = $objZend->connect ();
		
		try {
			$objZend->setInputField ( 0, "ZEND" );
		} catch ( Exception $e ) {
			$objResource = $objZend->submit ();
			Zend_Debug::dump ( $e, null, false );
		}
		
		try {
			// Log On
			if (mb_stristr ( $objResource->getOutputField ( "FLD_01_023" ), "sign" ) !== false) {
				$objZend->setInputField ( 0, $username );
				$objZend->setInputField ( 1, $password );
				$objZend->setInputField ( 2, " " );
				$objZend->setInputField ( 3, " " );
				$objZend->setInputField ( 4, " " );
				
				$objResponce = $objZend->submit ();
				
				$strError = $objResponce->getApplicationError ();
				if (empty ( $strError )) {
					$messages [0] = '';
					$messages [1] = '';
					$messages [] = "Authentication successful";
					// rebinding with authenticated user
					return new Zend_Auth_Result ( Zend_Auth_Result::SUCCESS, 'Bridge_5250', $messages );
				
				} else {
					
					$messages [0] = '';
					$messages [1] = '';
					$messages [] = "Authentication successful";
					// rebinding with authenticated user
					return new Zend_Auth_Result ( Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, 'Bridge_5250', $strError );
				}
			} else {
				$messages [0] = '';
				$messages [1] = '';
				$messages [] = "Authentication successful";
				// rebinding with authenticated user
				return new Zend_Auth_Result ( Zend_Auth_Result::SUCCESS, 'Bridge_5250', $messages );
			}
		} catch ( Exception $zle ) {
			
			$err = $zle->getCode ();
			
			$code = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
			$messages [0] = 'Error Error Error' . $err;
			$messages [1] = $zle->getMessage ();
		}
		
		$msg = isset ( $messages [1] ) ? $messages [1] : $messages [0];
		$messages [] = "$username authentication failed: $msg";
		
		return new Zend_Auth_Result ( $code, $username, $messages );
	}
	
	/**
	 * getResultRowObject() - Returns the result row as a stdClass object
	 *
	 * @param  string|array $returnColumns
	 * @param  string|array $omitColumns
	 * @return stdClass|boolean
	 */
	public function getResultRowObject() {
		if (! $this->_resultRow) {
			return false;
		}
		
		$returnObject = new stdClass ( );
		$returnObject->username = $this->getUsername ();
		$returnObject->password = $this->getPassword ();
		
		return $returnObject;
	
	}

}
?>