<?php
/**
 * ACL plugin, checkes that the user has access to this page
 * 
 * Made on the default MVC structure with single change that a user can have more the one role.
 * The roles are checked in iteration untill 1 allowed.
 * 
 * @author sirshurf
 *
 */
class Openiview_Plugin_Acl extends Zend_Controller_Plugin_Abstract {
	private $_acl = null;
	
	public function __construct(Zend_Acl $acl) {
		$this->_acl = $acl;
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		
		//For this example, we will use the controller as the resource:
		$resource = $request->getControllerName ();
		$privilege = $request->getActionName ();
		
		// checking permission our special way
		$boolFlag = $this->_acl->checkPermissions ( $resource, $privilege );
		
		if (empty ( $boolFlag )) {
			//If the user has no access we send him elsewhere by changing the request
			$request->setControllerName ( 'authentication' )->setActionName ( 'unauthorized' );
//			throw new Zend_Acl_Exception("Unauthorized");
		}
	
	}
}