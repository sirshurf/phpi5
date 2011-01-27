<?php

require_once ('Zend/Form/Decorator/Abstract.php');

class Openiview_Decorator_FormText extends Zend_Form_Decorator_Abstract {

	public function render($content){
		
        $element = $this->getElement();
        $view = $element->getView();
        $helper = $element->helper;
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        if (null === $element->getView()) {
            return $content;
        }
		
        $id = $element->getId();
        $name = $element->getName();
        $value = $element->getUnfilteredValue();
        $attribs = $element->getAttribs();
        
        
//		$info = $element->_getInfo($name, $value, $attribs);
//        extract($info); // name, value, attribs, options, listsep, disable

        // build the element
        $disabled = '';
        if ($disable) {
            // disabled
            $disabled = ' disabled="disabled"';
        }

        // XHTML or HTML end tag?
        $endTag = ' />';
//        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
//            $endTag= '>';
//        }
        
        /**
         * :TODO Change to dynamic varieble
         */
//        $value = date('d/m/y',$value);

        $xhtml = '<input type="text"'
                . ' name="' . $element->getView()->escape($name) . '"'
                . ' id="' . $element->getView()->escape($id) . '"'
                . ' value="' . $element->getView()->escape($value) . '"'
                . $disabled
                . $view->$helper()->_htmlAttribs($attribs)
                . $endTag;

        return $xhtml;
		
//		return date("d/m/y",$content);
	}
}

?>