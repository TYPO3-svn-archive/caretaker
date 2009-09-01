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
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_UNDEFINED, 'remote Operation failed');
		} 
	
		$extensionList = $operationResult->getValue();
		$errors =  array();
		$warnings = array();
		foreach ($extensionList as $extensionInfo){
			$this->checkExtension( $extensionInfo , $errors, $warnings);
		}
		
			// throw error if insecure extensions are installed
		if (count($errors)>0){
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 'ERRORS:'.implode(",",$errors).'WARNINGS:'.implode(",",$warnings));
		}
		
			// throw warning if insecure extensions are present
		if (count($warnings)>0){
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, 'remote Operation failed');
		}
		
		$testResult = tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, '');

		return $testResult;
	}
	
	public function checkExtension( $extensionInfo , &$errors, &$warnings){
		return ;
	}
	
	public function getLocationList(){
		$location_code = (int)$this->getConfigValue('check_extension_locations');
		$location_list = array();
		if ($location_code & 1) $location_list[] = 'system';
		if ($location_code & 2) $location_list[] = 'global';
		if ($location_code & 4) $location_list[] = 'local';
		return $location_list;
	} 
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']);
}
?>