<?php
/**
 * This is a file of the caretaker project.
 * Copyright 2009 by n@work Internet Informationssystem GmbH (www.work.de)
 * 
 * @Author	Thomas Hempel 		<thomas@work.de>
 * @Author	Martin Ficzel		<martin@work.de>
 * @Author	Patrick Kollodzik	<patrick@work.de>
 * @Author	Thorben Kapp		<thorben@work.de>
 * 
 * $$Id: class.tx_caretaker_typo3_extensions.php 33 2008-06-13 14:00:38Z thomas $$
 */

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Thorben Kapp <thorben@work.de>
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

class tx_caretakeranalyzerTestService extends tx_caretaker_TestServiceBase {
	
	protected $valueDescription = 'Matches';
	
	/**
	 * Returns the given value description
	 * @return string
	 */
	public function getValueDescription(){
		
		return $this->valueDescription;
	}
	
	/**
	 * Runs the test. It goes through the log file line per line. For each line
	 * it checks for every pattern if it matches and if it does it puts the
	 * information into the $foundErrors array
	 * 
	 * @return tx_caretaker_TestResult
	 */
	public function runTest(){
		
		// loading the configuration
		$dateFrom = $this->getConfigValue('date_from');
		$dateTo = $this->getConfigValue('date_to');
		$dateHours = $this->getConfigValue('date_hours');
		$datePattern = $this->getConfigValue('date_pattern');
		$logFile = $this->getConfigValue('log_file');
		$patterns = explode("\n",$this->getConfigValue('patterns_configuration'));
		
		// eval start and end time
		switch($dateHours) {
			case 0:
				$startTime = $dateFrom;
				$endTime = $dateTo;
				break;
			case -1:
				$startTime = 0;
				$endTime = time();
				break;
			case 24:
				$startTime = time()-3600*24;
				$endTime = time();
				break;
			case 72:
				$startTime = time()-3600*72;
				$endTime = time();
				break;
			case 168:
				$startTime = time()-3600*168;
				$endTime = time();
				break;
			case 720:
				$startTime = strtotime(date('Y').(date('m')-1).date('d H:i:s'));
				$endTime = time();
				break;
			case 2160:
				$startTime = strtotime(date('Y').(date('m')-3).date('d H:i:s'));
				$endTime = time();
				break;
		}
		
		// open, read and afterwards close the file
		$fileHandle = fopen($logFile, 'r');
		$fileContent = fread($fileHandle, filesize($logFile));
		fclose($fileHandle);
		
		// explode the file content into lines
		$lines = explode("\n", $fileContent);
		
		$foundErrors = array();
		
		// go through each line
		foreach($lines as $line) {
			
			if(empty($line)) continue;
			
			preg_match($datePattern, $line, $timeMatch);
			$lineTimeStamp = strtotime($timeMatch[0]);
			
			if($lineTimeStamp >= $startTime && $lineTimeStamp <= $endTime) {
			
				// through each pattern
				foreach($patterns as $pattern) {
					
					// and check if it matches
					if(preg_match($pattern, $line, $match)) {
						
						// if it does, put it into the $foundErrors array
						$foundErrors[] = array($match[1], $match[2]);
					}
				}
			}
		}
		
		// if no errors were found return result with state ok
		if(empty($foundErrors)) {
			
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0, 'No errors found');
			
		// if there were errors, then return a result with state error, the number of errors found
		// and the list of errors... might be a long list
		} else {
			
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, count($foundErrors), $this->getErrorMessage($foundErrors));
		}
	}
	
	/**
	 * Combines the errors found to a human readable message including the numbers of errors
	 * @param $foundErrors The array with found matches
	 * @return string The combined error message
	 */
	private function getErrorMessage($foundErrors) {
		
		$message = 'There were '.count($foundErrors).' found.'."\n";
		
		foreach($foundErrors as $err) {
			
			$message .= 'Missing resource '.$err[0].' referenced by '.$err[1]."\n";
		}
		
		return $message;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php']);
}
?>