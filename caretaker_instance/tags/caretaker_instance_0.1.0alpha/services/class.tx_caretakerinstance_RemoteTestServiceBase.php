<?php

/***************************************************************
* Copyright notice
*
* (c) 2009-2010 by n@work GmbH and networkteam GmbH
*
* All rights reserved
*
* This script is part of the Caretaker project. The Caretaker project
* is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This is a file of the caretaker project.
 * http://forge.typo3.org/projects/show/extension-caretaker
 *
 * Project sponsored by:
 * n@work GmbH - http://www.work.de
 * networkteam GmbH - http://www.networkteam.com/
 *
 * $Id$
 */


require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_ServiceFactory.php'));

/**
 * AbstractClass for remote executed tests
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
abstract class tx_caretakerinstance_RemoteTestServiceBase extends tx_caretaker_TestServiceBase {
	/**
	 * Execute a list of operations on the configured instance.
	 * 
	 * The operations must be of the form
	 * <code>
	 * array(array("SomeOperationWithParams", array("foo" => "bar")), array("OperationWithoutParams"))
	 * </code>
	 * 
	 * @param $operations Array of array of operations
	 * @return tx_caretakerinstance_CommandResult|boolean
	 */
	protected function executeRemoteOperations($operations) {
		$instanceUrl = $this->instance->getUrl();
		$instancePublicKey = $this->instance->getPublicKey();
		$curlOptions = $this->instance->getCurlOptions();

		$factory = tx_caretakerinstance_ServiceFactory::getInstance();
		$connector = $factory->getRemoteCommandConnector();
		
		return $connector->executeOperations($operations, $instanceUrl, $instancePublicKey, $curlOptions);
	}

	/**
	 * Is the command result successful
	 * @param $commandResult
	 * @return tx_caretakerinstance_CommandResult|boolean
	 */
	protected function isCommandResultSuccessful($commandResult) {
		return is_object($commandResult) && $commandResult->isSuccessful();
	}

	/**
	 * Get the test result for a failed command result
	 * @param $commandResult
	 * @return tx_caretaker_TestResult
	 */
	protected function getFailedCommandResultTestResult($commandResult) {
		return tx_caretaker_TestResult::create(
			tx_caretaker_Constants::state_error,
			0,
			'Command execution failed: ' . (is_object($commandResult) ? $commandResult->getMessage() : 'undefined')
		);
	}

	/**
	 * Get the test result for a failed operation result
	 * @param $operationResult
	 * @return tx_caretaker_TestResult
	 */
	protected function getFailedOperationResultTestResult($operationResult) {
		return tx_caretaker_TestResult::create(
			tx_caretaker_Constants::state_error,
			0,
			'Operation execution failed: ' . $operationResult->getValue()
		);
	}
	
	public function checkVersionRange($actualVersion, $minVersion, $maxVersion, $versionParts = 3) {
		$actualVersionCombined = $this->versionToInt($actualVersion, $versionParts);
		if ($minVersion != '') {
			$minVersionCombined = $this->versionToInt($minVersion, $versionParts);
			if ($actualVersionCombined < $minVersionCombined) return FALSE;
		}
		if ($maxVersion != '') {
			$maxVersionCombined = $this->versionToInt($maxVersion, $versionParts);
			if ($actualVersionCombined > $maxVersionCombined) return FALSE;
		}
		
		return TRUE;
	}
	
	protected function versionToInt($version, $versionParts = 3, $versionBase = 1000) {
		$versionDigits = explode('.', $version, $versionParts);
		$versionCombined = 0;
		for ($i = 0; $i < $versionParts; $i++) {
			$versionCombined = ($versionCombined * $versionBase) + $versionDigits[$i];
		}
		return $versionCombined;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretakerinstance_RemoteTestServiceBase.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretakerinstance_RemoteTestServiceBase.php']);
}
?>