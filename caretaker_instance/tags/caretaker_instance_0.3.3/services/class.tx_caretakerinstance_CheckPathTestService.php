<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 by n@work GmbH and networkteam GmbH
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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_RemoteTestServiceBase.php'));

/**
 * Check for TYPO3 version
 *
 * @author Felix Oertel <oertel@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_CheckPathTestService extends tx_caretakerinstance_RemoteTestServiceBase {
	public function runTest() {
			// fetch config values
		$paths = $this->getConfigValue('cppaths');
		$inverse = $this->getConfigValue('cpinverse');
		$type = $this->getConfigValue('cptype');
		
			// catch required fields
		if (!$paths) {
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_undefined, 0, 'Cannot test without path.');
		}
		
			// prepare tests
		$paths = explode(chr(10), $paths);
		foreach ($paths AS $path) {
			$operations[] = array('CheckPathExists', $path);
		}
			// run
		$commandResult = $this->executeRemoteOperations($operations);

			// catch errors
		if (!$this->isCommandResultSuccessful($commandResult)) {
		 	return $this->getFailedCommandResultTestResult($commandResult);
		}
		
			// process resultset
		$resultset = $commandResult->getOperationResults();
		foreach ($resultset AS $result) {
			$resValue = $result->getValue();
			
			if (!$result->isSuccessful() && !$inverse) {	
				$msg[] = $resValue['path'] . ' does not exist';
			} elseif ($result->isSuccessful() && $inverse) {
				$msg[] = $resValue['path'] . ' does exist';
			} elseif ($result->isSuccessful() && $type && ($type != $resValue['type'])) {
					// type
				$msg[] = $resValue['path'] . ' exists, but is a ' . $resValue['type'];
			}
		}

		if (is_array($msg)) {
			return tx_caretaker_TestResult::create(
				tx_caretaker_Constants::state_error, 
				0, 
				implode(chr(10), $msg)
			);
		} else {
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 1);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_CheckPathTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_CheckPathTestService.php']);
}
?>