<?php
require_once('Zend/5250.php');

class Openiview_5250 extends Zend_5250{

	private $_defaultLoginIni = '/configs/5250/login.ini';
		
	private $_responce;

	public function __construct($jobId = null, Zend_5250_Handler $storageHandler = null, $arrOption = array()){
		
		parent::__construct($jobId, $storageHandler);
		
	}
		
	public function connect(){

		$objDb = Zend_Db_Table_Abstract::getDefaultAdapter ();		
		$objDbRes = $objDb->getConnection();
		$objWrapper = new Openiview_5250_I5Wrapper($objDbRes);
		
		$this->_responce = parent::connect($objDb->getUsername(),$objDb->getPassword(),$objWrapper);
		
		return;
	}
	
	public function emulationLogin($strConfigFile = null){
	
		if (empty($strConfigFile)){
			$arrConfigIni = new Zend_Config_Ini ( APPLICATION_PATH.$this->_defaultLoginIni );
		} else {
			$arrConfigIni = new Zend_Config_Ini ( APPLICATION_PATH.$strConfigFile );
		}
		
		// check that login data should be inserted
		if (isset($arrConfigIni->username)){
		
			$sessionUser = new Zend_Session_Namespace ( "usercredential" );
			
			$this->setInputField($arrConfigIni->username,$sessionUser->username );
			$this->setInputField($arrConfigIni->password,$sessionUser->password );
		}
		$this->submit();
		$this->submit();
		return;
	}
	
	public function submit($command = Zend_5250::ENTER){
		//Zend_Debug::dump($command);
		//Zend_Debug::dump($this->_responce);
		$this->_responce = parent::submit($command);
		return;
	}
	
	public function getResponce(){
		return $this->_responce;
	}	
	
	public function getMapedArray($strConfigFile){
		
		$arrConfigIni = new Zend_Config_Ini ( APPLICATION_PATH.$strConfigFile );
		
		$strRowStart = $arrConfigIni->row->start;
		$strRowEnd = $arrConfigIni->row->end;
		
		$arrOutputData = array();
		
		//getInputFields()
		$arrOutputFields = $this->_responce->getOutputFields();
		foreach ($arrOutputFields as $objOutputField){
			$strFieldName = $objOutputField->getName();
			// Check if thie field is needed
			$arrData = explode("_",$strFieldName);						
			if ($arrData[1]>$strRowStart && $arrData[1]<$strRowEnd){
				$intRowNum = (int)$arrData[1];
				foreach ($arrConfigIni->column as $strKey=>$strColumn){
					if ($arrData[2] == $strColumn){
						$arrOutputData[$intRowNum]['id'] = intRowNum;
						$arrOutputData[$intRowNum][$strKey]= trim($objOutputField->getValue());
					}
					if (empty($arrOutputData[$intRowNum][$strKey])){
						$arrOutputData[$intRowNum][$strKey] = "";
					}
				}
			}
		}
		
		$arrInputFields = $this->_responce->getInputFields();
		foreach ($arrInputFields as $objInputField){
			$strFieldName = $objInputField->getName();
			// Check if thie field is needed
			$arrData = explode("_",$strFieldName);						
			if ($arrData[1]>$strRowStart && $arrData[1]<$strRowEnd){
				$intRowNum = (int)$arrData[1];
				foreach ($arrConfigIni->column as $strKey=>$strColumn){
					if ($arrData[2] == $strColumn){
						$arrOutputData[$intRowNum]['id'] = intRowNum;
						$arrOutputData[$intRowNum][$strKey]= trim($objInputField->getValue());
					}
					if (empty($arrOutputData[$intRowNum][$strKey])){
						$arrOutputData[$intRowNum][$strKey] = "";
					}
				}
			}
		}
		
		return $arrOutputData;
	}

}
