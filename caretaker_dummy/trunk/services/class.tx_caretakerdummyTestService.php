<?php

/***************************************************************
* Copyright notice
*
* (c) 2009 by n@work GmbH and networkteam GmbH
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

/**
 * Dummy Test-Service to provide random or userdefined results for testing of caretaker and its components
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_dummy
 */
class tx_caretakerdummyTestService extends tx_caretaker_TestServiceBase {

	public function runTest() {

		$result    = $this->getConfigValue('result');
		$value     = $this->getConfigValue('value');

		if ($result == 'random') {
			$result = rand(-1,2);
		}
		
		if ( $value ){
			$returnValue = rand (0, $value); 
 		} else {
 			$returnValue = 0;
 		}

		$message = new tx_caretaker_ResultMessage( 'foo ###VALUE_FOO### baz' , array('foo'=>'bar') );

		$submessages = array();
		$submessages[] =  new tx_caretaker_ResultMessage( 'LLL:EXT:caretaker_dummy/locallang_fe.xml:detail' , array('foo'=>'bar', 'bar'=>'baz') );
		$submessages[] =  new tx_caretaker_ResultMessage( 'LLL:EXT:caretaker_dummy/locallang_fe.xml:detail'  );
		$submessages[] =  new tx_caretaker_ResultMessage( 'foo {LLL:EXT:caretaker_dummy/locallang_fe.xml:detail}' , array('foo'=>'bar', 'bar'=>'baz') );

		switch ($result) {
			case 0:
				return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, $returnValue0, $message , $submessages );
			case 1;
				return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_warning, $returnValue, $message , $submessages  );
			case 2;
				return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, $returnValue, $message , $submessages  );
			default:
				return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_undefined, $returnValue, $message , $submessages  );
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php']);
}
?>