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
require_once (t3lib_extMgm::extPath('caretaker_selenium').'classes/class.tx_caretakerselenium_SeleniumTest.php');

class tx_caretakerseleniumTestService extends tx_caretaker_TestServiceBase {
	
	protected $valueDescription = 'LLL:EXT:caretaker_selenium/locallang.xml:seconds';
	
	public function getValueDescription(){
		
		return $this->valueDescription;
	}
	
	/**
	 * Checks if all selenium servers that are needed for this test are free
	 * and returns the result. If only one server is busy the test must not be run
	 * to avoid parallel execution of seleniumtests on one machine.
	 * 
	 * @return boolean
	 */
	public function isExecutable() {
		
		$server = $this->getConfigValue('selenium_server');
		
		$servers = array();
		
		if (is_array($server)){
			$inUseSince = $server['inUseSince'];
			
			if($inUseSince + 3600 > time()) {
				
				return false; // server is busy and can NOT be used
			}
			
			return true; // server is free and can be used
			
		} else {
			
			$server_ids = explode(',',$server);
			
			foreach($server_ids as $sid) {
				
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_caretakerselenium_server', 'deleted=0 AND hidden=0 AND uid='.$sid);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				
				if ($row['inUseSince'] + 3600 > time()){
					
					return false; // server is in use and can NOT be used
				}
			}
			
			return true; // servers are free and can be used
		}
	}
	
	public function runTest(){
		
		//echo 'This is the retreived configuration:'."\n";
		//print_r($this->flexform_configuration);
				
		$commands     = $this->getConfigValue('selenium_configuration');
		
		//print_r($commands);
		
		$error_time   = $this->getConfigValue('response_time_error');
		$warning_time = $this->getConfigValue('response_time_warning');
		
		$server       = $this->getConfigValue('selenium_server');
		
		$servers = array();
		
		if (is_array($server)){
			$servers[] = array(
				'uid' => $server['uid'],
				'title' => $server['title'],
				'host'    => $server['host'],
				'browser' => $server['browser']
			);
		} else {
			$server_ids = explode(',',$server);
			
			foreach($server_ids as $sid) {
				
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_caretakerselenium_server', 'deleted=0 AND hidden=0 AND uid='.$sid);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				if ($row){
					$servers[] = array(
						'uid' => $sid,
						'title' => $row['title'],
						'host'    => $row['hostname'],
						'browser' => $row['browser']
					);
				}
			}			
		}

		if (count($servers) == 0 ) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, 'LLL:EXT:caretaker_selenium/locallang.xml:selenium_configuration_error');
		}
		
		// set the servers busy
		$this->setServersBusy($servers);
		
		$baseURL = $this->instance->getUrl(); 
		
		$results  = array();
		
		$num_servers = 0;
		$num_ok      = 0;
		$num_warning = 0;
		$num_error   = 0;
		$whole_time  = 0;

		$details = array();
		
		foreach ($servers as $server){
			$num_servers ++;

			$test = new tx_caretakerselenium_SeleniumTest($commands,$server['browser'],$baseURL,$server['host']);
			list($success, $msg, $time) = $test->run();
		
			$detail_info  = array(
				'values' => array (
					'success'	=> $success,
					'host'		=> $server['host'],
					'title'     => $server['title'],
					'browser' 	=> $server['browser'],
					'message'  => $msg,
					'time'     => $time
				)
			);

			$whole_time += $time;
			
			if (!$success){
				$detail_info['selenium_detail_error'] = 'LLL:EXT:caretaker_selenium/locallang.xml:selenium_detail_error';
				$num_error ++;
			} else {
				if ($whole_time > $error_time ){
					$detail_info['selenium_detail_timout_error'] = 'LLL:EXT:caretaker_selenium/locallang.xml:selenium_detail_timeout';
					$num_error ++;
				} else if  ($whole_time > $warning_time){
					$detail_info['selenium_detail_timout_warning'] = 'LLL:EXT:caretaker_selenium/locallang.xml:selenium_detail_timeout';
					$num_warning ++;
				} else {
					$detail_info['messsage'] = 'LLL:EXT:caretaker_selenium/locallang.xml:selenium_detail_ok';
					$num_ok ++;
				}
			}

			$details[] = $detail_info;
		}
		
			// set the servers free
		$this->setServersBusy($servers, false);


			// 
		$info_array = array(
			'values'  => array( 'num_servers'=>$num_servers, 'num_ok' => $num_ok, 'num_warning'=>$num_warning, 'num_error' =>$num_error, 'time'=>$whole_time ),
			'details' => $details
		);

			// calculate res
		if ( $num_errors > 0 )  {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, $whole_time, 'LLL:EXT:caretaker_selenium/locallang.xml:selenium_info_problems'  , $info_array);
		} else if ( $num_warnings > 0 ) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, $whole_time, 'LLL:EXT:caretaker_selenium/locallang.xml:selenium_info_problems' , $info_array);
		} else {
			return tx_caretaker_TestResult::create( TX_CARETAKER_STATE_OK , $whole_time, 'LLL:EXT:caretaker_selenium/locallang.xml:selenium_info_ok' , $info_array );
		}

		return $testResult;
		
	}
	
	private function setServersBusy($servers, $state = true) {
		
		$serverIds = array();
		
		foreach($servers as $server) {
			
			$serverIds[] = $server['uid'];
		}
		
		foreach($serverIds as $sid) {
			
			if($state) {
				
				// set the selenium servers needed for that test to busy state
				// for that set the inUseSince timestamp to the current time
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_caretakerselenium_server', 'uid='.$sid, array('inUseSince' => time()));
				
			} else {
				
				// set the selenium servers needed for that test to free state
				// for that set the inUseSince timestamp to the current time minus one hour and one second
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_caretakerselenium_server', 'uid='.$sid, array('inUseSince' => time() - 3601));
			}
			
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php']);
}
?>