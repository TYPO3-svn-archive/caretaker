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

require_once (t3lib_extMgm::extPath('caretaker').'/services/class.tx_caretaker_TestServiceBase.php');

class tx_caretakerdummyTestService extends tx_caretaker_TestServiceBase {
	
	public function runTest(){

		$result    = $this->getConfigValue('result');
		
		if ($result == 'random'){
			$result = rand(-1,2);
		}

		$info_array = array(
			'values'  => array('foo'=>' foobar '),
			'details' => array(
				array ( 'message' => 'LLL:EXT:caretaker_dummy/locallang_fe.xml:detail', 'values'=>array ('bar'=>'blubber')),
				'this is a plain message',
				array ( 'message' => 'LLL:EXT:caretaker_dummy/locallang_fe.xml:detail', 'values'=>array ('baz'=>'blubber2')),
			)
		);

		switch ($result) {
			case 0:
				return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0, 'LLL:EXT:caretaker_dummy/locallang_fe.xml:message' , $info_array );
			case 1;
				return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, 0, 'LLL:EXT:caretaker_dummy/locallang_fe.xml:message' , $info_array );
			case 2;
				return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, 'LLL:EXT:caretaker_dummy/locallang_fe.xml:message' , $info_array );
			default:
				return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_UNDEFINED, 0, 'LLL:EXT:caretaker_dummy/locallang_fe.xml:message' , $info_array );
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php']);
}
?>