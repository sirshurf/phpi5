<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	private $_acl = null;
	
	protected function _initHeader() {
		$this->bootstrap ( 'FrontController' );
		$front = $this->getResource ( 'FrontController' );
		
		$response = new Zend_Controller_Response_Http ();
		$response->setHeader ( 'Content-Type', 'text/html; charset=utf-8' );
		
		$front->setResponse ( $response );
	}
	
	protected function _initConfigIni() {
		Zend_Registry::set ( 'config', new Zend_Config_Ini ( APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV ) );
		
		$arrRoles = Zend_Registry::get ( "config" )->acl->roles->toArray ();
		
		/**
		 * Guest Role
		 * Global permission
		 * @var string Guest Role
		 */
		define ( "ROLE_GUEST", $arrRoles [0] ['name'] );
		/**
		 * Student Role
		 * Global permission
		 * @var string
		 */
		define ( "ROLE_STUDENT", $arrRoles [2] ['name'] );
		/**
		 * Supervisor Role
		 * @var string
		 */
		define ( "ROLE_SUPER", $arrRoles [7] ['name'] );
		/**
		 * Lab Academic Supervisor
		 * @var string
		 */
		define ( "ROLE_LABACADSUP", $arrRoles [10] ['name'] );
		/**
		 * Lab Eng.
		 * @var string
		 */
		define ( "ROLE_LABENG", $arrRoles [8] ['name'] );
		/**
		 * Lab Cluster Coordinator
		 * @var string
		 */
		define ( "ROLE_LABCLUSTERCOORD", $arrRoles [12] ['name'] );
		/**
		 * Main Academic Supervisor
		 * Global permission
		 * @var string
		 */
		define ( "ROLE_MAINACADSUP", $arrRoles [11] ['name'] );
		/**
		 * Main Supervisor
		 * Global permission
		 * @var string
		 */
		define ( "ROLE_MAINSUPER", $arrRoles [9] ['name'] );
		/**
		 * System Administrator - Alex Sherman! Dont Touch!
		 * Global permission
		 * @var string
		 */
		define ( "ROLE_SYSADMIN", $arrRoles [3] ['name'] );
		
	}
	
	protected function _initAuthentication() {
		
		$fc = Zend_Controller_Front::getInstance ();
		
		if ( ! Zend_Auth::getInstance ()->hasIdentity ()) {
			$fc->registerPlugin ( new Openiview_Plugin_AccessCheck () );
		}
		$this->_acl = new Openiview_Acl ();
		
		$fc->registerPlugin ( new Openiview_Plugin_Acl ( $this->_acl ) );
	}
	
	protected function _initNav() {
		
		$this->bootstrap ( 'view' );
		$view = $this->view;
		
		$view->getHelper ( 'navigation' )->setAcl ( $this->_acl );
		$view->getHelper ( 'menu' );
		
		if (APPLICATION_ENV !== "production"){
			$view->headTitle ( 'LabAdmin' ." - ".APPLICATION_ENV);
		} else {
			$view->headTitle ( 'LabAdmin' );
		}
		
		$strBaseUrl = $view->baseUrl ();
		$strJsUrl = $strBaseUrl . '/js/';
		$strCssUrl = $strBaseUrl . '/CSS/';
		
		// setting content type and character set
		$view->headMeta ()->appendHttpEquiv ( 'Content-Type', 'text/html; charset=UTF-8' )
			->appendHttpEquiv ( 'Content-Language', 'en-US' );
		
		$view->jQuery ()->setLocalPath ( $strJsUrl . 'jquery-1.4.2.js' );
		$view->jQuery ()->setUiLocalPath ( $strJsUrl . 'jquery-ui-1.8.4.custom.min.js' );
		$view->jQuery ()->uiEnable();
		
		$view->jQuery ()->addOnLoad ( '$("textarea").ckeditor();' );
//		config.baseFloatZIndex = 9000;
		
		$arrDefaultsDatePicker = Zend_Registry::get("config")->datepick->default->toArray();
		$arrDefaultsDatePicker['showButtonPanel'] = true;
		 
		$view->jQuery ()->addOnLoad ( '$.datepicker.setDefaults('.ZendX_JQuery::encodeJson($arrDefaultsDatePicker).' );' );
		
		// JS files
		$view->headScript ()->appendFile ( $strJsUrl . 'jquery.ckeditor.fix.js', 'text/javascript' );
		
		$view->headScript ()->appendFile ( $strJsUrl . 'jquery.jgrowl.js', 'text/javascript' );
		$view->headScript ()->appendFile ( $strJsUrl . 'i18n/grid.locale-en.js', 'text/javascript' );
		$view->headScript ()->appendFile ( $strJsUrl . 'jquery.jqGrid.min.js', 'text/javascript' );
		$view->headScript ()->appendFile ( $strJsUrl . 'ckeditor/ckeditor.js', 'text/javascript' );
		$view->headScript ()->appendFile ( $strJsUrl . 'ckeditor/config.js', 'text/javascript' );
		$view->headScript ()->appendFile ( $strJsUrl . 'ckeditor/adapters/jquery.js', 'text/javascript' );
		$view->headScript ()->appendFile ( $strJsUrl . 'menu_ie6.js', 'text/javascript' );
//		$view->headScript ()->appendFile ( $strJsUrl . 'jquery.ckeditor.fix.js', 'text/javascript' );
		
		
		// CSS
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'styles.css' );
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'menu.css' );
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'smoothness/jquery-ui-1.8.2.custom.css' );
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'jquery.jgrowl.css' );
		$view->headLink ()->appendStylesheet ( $strCssUrl . 'ui.jqgrid.css' );
	
	}
	
	protected function _initLables() {
		
		$this->bootstrap ( 'multidb' );
		
		Labadmin_Models_SystemNotification::initLables();
			
		//Zend_Registry::set ( 'lables', new Zend_Config_Ini ( APPLICATION_PATH . '/configs/messages.ini' ) );
	}
}

