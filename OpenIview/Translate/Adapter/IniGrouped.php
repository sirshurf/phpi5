<?php

/** Zend_Translate_Adapter_Ini */
require_once 'Zend/Translate/Adapter/Ini.php';

/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Openiview_Translate_Adapter_IniGrouped extends Zend_Translate_Adapter_Ini
{
    private $_data = array();

    /**
     * Load translation data
     *
     * @param  string|array  $data
     * @param  string        $locale  Locale/Language to add data for, identical with locale identifier,
     *                                see Zend_Locale for more information
     * @param  array         $options OPTIONAL Options to use
     * @throws Zend_Translate_Exception Ini file not found
     * @return array
     */
    protected function _loadTranslationData($data, $locale, array $options = array())
    {
        $this->_data = array();
        if (!file_exists($data)) {
            require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception("Ini file '".$data."' not found");
        }

		$translation = new Zend_Config_Ini ( $data, $locale );
		
        if (!isset($this->_data[$locale])) {
            $this->_data[$locale] = array();
        }

        $this->_data[$locale] = array_merge($this->_data[$locale], $translation->toArray());
        return $this->_data;
    }

    /**
     * returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "Ini";
    }
}
