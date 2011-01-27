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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FormText.php 18951 2009-11-12 16:26:19Z alexander $
 */

/**
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';

/**
 * Helper to generate a "text" element
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Openiview_View_Helper_FormDate extends ZendX_JQuery_View_Helper_DatePicker {
	
	public function formDate($id, $value = null, array $params = array(), array $attribs = array()) {
		/**
		 * :TODO Change to dynamic varieble
		 */
		$value = date('d/m/Y',$value);
		return $this->datePicker ( $id, $value, $params, $attribs );
	}
	
	/**
	 * Generates a 'text' element with values converted from TimeStamp to Dates (d/m/y)
	 *
	 * @access public
	 *
	 * @param string|array $name If a string, the element name.  If an
	 * array, all other parameters are ignored, and the array elements
	 * are used in place of added parameters.
	 *
	 * @param mixed $value The element value.
	 *
	 * @param array $attribs Attributes for the element tag.
	 *
	 * @return string The element XHTML.
	 */
	public function formText1($name, $value = null, array $params = array(), array $attribs = array()) {
		
		$attribs = $this->_prepareAttributes ( $name, $value, $attribs );
		
		if (! isset ( $params ['dateFormat'] ) && Zend_Registry::isRegistered ( 'Zend_Locale' )) {
			$params ['dateFormat'] = self::resolveZendLocaleToDatePickerFormat ();
		}
		
		// TODO: Allow translation of DatePicker Text Values to get this action from client to server
		$params = ZendX_JQuery::encodeJson ( $params );
		
		$js = sprintf ( '%s("#%s").datepicker(%s);', ZendX_JQuery_View_Helper_JQuery::getJQueryHandler (), $attribs ['id'], $params );
		
		$this->jquery->addOnLoad ( $js );
		
		$info = $this->_getInfo ( $name, $value, $attribs );
		extract ( $info ); // name, value, attribs, options, listsep, disable
		

		// build the element
		$disabled = '';
		if ($disable) {
			// disabled
			$disabled = ' disabled="disabled"';
		}
		
		// XHTML or HTML end tag?
		$endTag = ' />';
		if (($this->view instanceof Zend_View_Abstract) && ! $this->view->doctype ()->isXhtml ()) {
			$endTag = '>';
		}
		
		/**
		 * :TODO Change to dynamic varieble
		 */
		$value = date ( 'd/m/y', $value );
		
		$xhtml = '<input type="text"' . ' name="' . $this->view->escape ( $name ) . '"' . ' id="' . $this->view->escape ( $id ) . '"' . ' value="' . $this->view->escape ( $value ) . '"' . $disabled . $this->_htmlAttribs ( $attribs ) . $endTag;
		
		return $xhtml;
	}
}
