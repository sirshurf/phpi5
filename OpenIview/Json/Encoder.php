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
 * @category   OpenIview
 * @package    Zend_Json
 * @copyright  Copyright (c) 2009 OpenIview LTD. (http://www.OpenIview.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Encoder.php 16541 2009-07-07 06:59:03Z bkarwin $
 */

/**
 * Encode PHP constructs to JSON
 *
 * @category   OpenIview
 * @package    Zend_Json 
 * @copyright  Copyright (c) 2009 OpenIview LTD. (http://www.OpenIview.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class OpenIview_Json_Encoder extends Zend_Json_Encoder {
	
    /**
     * Use the JSON encoding scheme for the value specified
     *
     * @param mixed $value The value to be encoded
     * @param boolean $cycleCheck Whether or not to check for possible object recursion when encoding
     * @param array $options Additional options used during encoding
     * @return string  The encoded value
     */
    public static function encode($value, $cycleCheck = false, $options = array())
    {
        $encoder = new self(($cycleCheck) ? true : false, $options);

        return $encoder->_encodeValue($value);
    }
	
	
	/**
	 * JSON encode a string value by escaping characters as necessary
	 *
	 * @param $value string
	 * @return string
	 */
	protected function _encodeString(&$string) {
		// Escape these characters with a backslash:
		

		//$string = hebrev ( $string );
		$string = mb_convert_encoding ( $string, "UTF-8", "ISO-8859-8" );
		$string = trim($string);
		
		// " \ / \n \r \t \b \f
		$search = array ('\\', "\n", "\t", "\r", "\b", "\f", '"' );
		$replace = array ('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\"' );
		$string = str_replace ( $search, $replace, $string );
		
		// Escape certain ASCII characters:
		// 0x08 => \b
		// 0x0c => \f
		$string = str_replace ( array (chr ( 0x08 ), chr ( 0x0C ) ), array ('\b', '\f' ), $string );
		//$string = self::encodeUnicodeString ( $string );
		
		return '"' . $string . '"';
	}
	

    /**
     * Encode an object to JSON by encoding each of the public properties
     *
     * A special property is added to the JSON object called '__className'
     * that contains the name of the class of $value. This is used to decode
     * the object on the client into a specific class.
     *
     * @param $value object
     * @return string
     * @throws Zend_Json_Exception If recursive checks are enabled and the object has been serialized previously
     */
    protected function _encodeObject(&$value)
    {
        if ($this->_cycleCheck) {
            if ($this->_wasVisited($value)) {

                if (isset($this->_options['silenceCyclicalExceptions'])
                    && $this->_options['silenceCyclicalExceptions']===true) {

                    return '"* RECURSION (' . get_class($value) . ') *"';

                } else {
                    require_once 'Zend/Json/Exception.php';
                    throw new Zend_Json_Exception(
                        'Cycles not supported in JSON encoding, cycle introduced by '
                        . 'class "' . get_class($value) . '"'
                    );
                }
            }

            $this->_visited[] = $value;
        }

        $props = '';

        if ($value instanceof Iterator) {
            $propCollection = $value;
        } else {
            $propCollection = get_object_vars($value);
        }

        foreach ($propCollection as $name => $propValue) {
            if (isset($propValue)) {
            	
            	if (!empty($props)){
            		$props .= ',';
            	}            	
                $props .= $this->_encodeValue($name)
                        . ':'
                        . $this->_encodeValue($propValue);
            }
        }

        return '{'  . $props . '}';
    }
	

}

