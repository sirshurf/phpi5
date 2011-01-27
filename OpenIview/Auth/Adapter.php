<?php
class Openiview_Auth_Adapter implements Zend_Auth_Adapter_Interface {
	protected $_username;
	protected $_password;
	
		
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
	
	
	
	
	public function authenticate() {
		$users = array ('Alex', 'Alex1' );
		if (in_array ( $this->_username, $users ) && ! in_array ( $this->password, $users )) {
			return new Zend_Auth_Result ( Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $this->_username );
		}
		if (! in_array ( $this->_username, $users ) && in_array ( $this->password, $users )) {
			return new Zend_Auth_Result ( Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $this->_username );
		}
		return new Zend_Auth_Result ( Zend_Auth_Result::SUCCESS );
	}
}
?>