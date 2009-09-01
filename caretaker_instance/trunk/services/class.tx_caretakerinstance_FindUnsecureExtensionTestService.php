<?php
/**
 * This is a file of the caretaker project.
 * Copyright 2008 by n@work Internet Informationssystem GmbH (www.work.de)
 * 
 * @Author	Thomas Hempel 		<thomas@work.de>
 * @Author	Martin Ficzel		<martin@work.de>
 * @Author	Patrick Kollodzik	<patrick@work.de>
 * 
 * $$Id: class.tx_caretaker_typo3_extensions.php 33 2008-06-13 14:00:38Z thomas $$
 */

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Patrick Kollodzik <patrick@work.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_RemoteTestServiceBase.php'));

class tx_caretakerinstance_FindUnsecureExtensionTestService extends tx_caretakerinstance_RemoteTestServiceBase{
	
	public function runTest() {
		
		$location_list = $this->getLocationList();
		
		$operation = array('GetExtensionList', array('locations'=>$location_list));
		$operations = array($operation);

		$commandResult = $this->executeRemoteOperations($operations);
		if (!$this->isCommandResultSuccessful($commandResult)) {
			return $this->getFailedCommandResultTestResult($commandResult);
		}
		
		$results = $commandResult->getOperationResults();
		$operationResult = $results[0];
		
		if (!$operationResult->isSuccessful()) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_UNDEFINED, 0, 'remote Operation failed');
		} 
	
		$extensionList = $operationResult->getValue();
				
		$errors =  array();
		$warnings = array();
		foreach ($extensionList as $extension){
			$this->checkExtension( $extension , &$errors, &$warnings);
		}
		
			// throw error if insecure extensions are installed
		if (count($errors)>0){
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0,  'ERRORS: '.implode(",",$errors).' WARNINGS: '.implode(",",$warnings));
		}
		
			// throw warning if insecure extensions are present
		if (count($warnings)>0){
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, 0, 'WARNINGS: '.implode(",",$warnings) );
		}
		
		$testResult = tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0, '');

		return $testResult;
	}
	
	
	public function getLocationList(){
		$location_code = (int)$this->getConfigValue('check_extension_locations');
		$location_list = array();
		if ($location_code & 1) $location_list[] = 'system';
		if ($location_code & 2) $location_list[] = 'global';
		if ($location_code & 4) $location_list[] = 'local';
		return $location_list;
	}
	 
	public function checkExtension( $extension , &$errors, &$warnings ){
				
		$ext_key       = $extension->ext_key;
		$ext_version   = $extension->version;
		$ext_installed = $extension->installed; 
		
			// find extension in TER
		$ter_info = $this->getExtensionTerInfos($ext_key, $ext_version);
		if ($ter_info){ 	
				// ext is in TER
				
				// ext is secure
			if ($ter_info['reviewstate'] > -1 ) return array( 0, '' );
				
			if ($ext_installed){
				$handling = $this->getInstalledExtensionErrorHandling();
			} else {
				$handling = $this->getUninstalledExtensionErrorHandling();
			}
			
				// return result
			switch ($handling) {
				case 0: #ignore
					return;
				case 1: #warning
					$warnings[] = 'Extension '.$ext_key.' is installed in version '.$ext_version;
					return;
				case 2: #error
					$errors[]  = 'Extension '.$ext_key.' is installed in version '.$ext_version;
					return;
			}
		} else {
				// ext is not in TER
				
				// check whitelist
			$ext_whitelist = $this->getCustomExtensionWhitelist();
			if (in_array($ext_key, $ext_whitelist) ) return; 
			
			$handling = $this->getCustomExtensionErrorHandling();
				// return result
			switch ($handling) {
				case 0: #ignore
					return;
				case 1: #warning
					$warnings[] = 'Extension '.$ext_key.' is installed in version '.$ext_version;
					return;
				case 2: #error
					$errors[]  = 'Extension '.$ext_key.' is installed in version '.$ext_version;
					return;
			
			}
		}
	}
	
	public function getExtensionTerInfos( $ext_key, $ext_version ){
		$ext_infos = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('extkey, version, reviewstate','cache_extensions','extkey = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($ext_key,'cache_extensions' ).' AND version = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($ext_version,'cache_extensions'), '', '' , 1 );
		if (count($ext_infos)==1){
			return $ext_infos[0];
		} else {
			return false;
		}
	}
	
	public function getInstalledExtensionErrorHandling(){
		return (int)$this->getConfigValue('status_of_installed_insecure_extensions');
	}
	
	public function getUninstalledExtensionErrorHandling(){
		return (int)$this->getConfigValue('status_of_uninstalled_insecure_extensions');
	}
	
	public function getCustomExtensionErrorHandling(){
		return (int)$this->getConfigValue('status_of_custom_extensions');
	}
	
	public function getCustomExtensionWhitelist(){
		return explode( chr(10) ,$this->getConfigValue('custom_extkey_whitlelist') );
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']);
}
?>