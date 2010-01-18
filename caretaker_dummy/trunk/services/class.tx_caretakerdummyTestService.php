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

		if ($result == 'random') {
			$result = rand(-1,2);
		}

		$message = new tx_caretaker_ResultMessage( 'foo ###VALUE_FOO### baz' , array('foo'=>'bar') );

		$submessages = array();
		$submessages[] =  new tx_caretaker_ResultMessage( 'LLL:EXT:caretaker_dummy/locallang_fe.xml:detail' , array('foo'=>'bar', 'bar'=>'baz') );
		$submessages[] =  new tx_caretaker_ResultMessage( 'LLL:EXT:caretaker_dummy/locallang_fe.xml:detail'  );
		$submessages[] =  new tx_caretaker_ResultMessage( 'foo {LLL:EXT:caretaker_dummy/locallang_fe.xml:detail}' , array('foo'=>'bar', 'bar'=>'baz') );

		switch ($result) {
			case 0:
				return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0, $message , $submessages );
			case 1;
				return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, 0, $message , $submessages  );
			case 2;
				return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, $message , $submessages  );
			default:
				return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_UNDEFINED, 0, $message , $submessages  );
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php']);
}
?>