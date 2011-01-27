<?php
/**
 * Class that checks, if a user HAS Identity, if he has none, forward him to the authentication.
 * 
 * Made in the default MVC structure
 * 
 * @author sirshurf
 *
 */
class Openiview_Plugin_AccessCheck extends Zend_Controller_Plugin_Abstract {
	private $_acl = null;
	
	public function __construct() {
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		$module = $request->getModuleName ();
		$resource = $request->getControllerName ();
		$action = $request->getActionName ();
		$params = $request->getUserParams ();
		
		if ('contact' !== $resource) {
			
			if ($resource !== 'authentication') {
				$redirect = array ("module" => $module, "resorce" => $resource, "action" => $action, "params" => $params );
				
				$session = new Zend_Session_Namespace ( "uri" );
				$session->url = $redirect;
			}
			
			$request->setControllerName ( 'authentication' )->setActionName ( 'login' );
		
		}
	
	}
}