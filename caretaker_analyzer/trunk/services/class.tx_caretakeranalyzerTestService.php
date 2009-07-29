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
 * This service runs the log file tests.
 * A logfile is read using the system functions wc, head and tail.
 * It reads the file in blocks of lines to avoid a memory overflow.
 * @todo The existance of the three needed functions should be checked.
 * Eventually you can override the isExecutable function from the parent class
 * and use it for that. Than this should return some kind of result or exception.
 * 
 * After reading one block the readed lines are processed, that means that they
 * are checked against the given date and if this matches also against the given pattern
 * how the error message, you are looking for, looks like in the log file.
 * 
 * If the pattern matches the information of the error message is put into an array.
 * 
 * NOTE: The script is currently used to find missing resources so the error message
 * that is produced at the end of the test looks like that and only includes the first to saved
 * subpatterns from the error message pattern.
 * @todo This should be improved that there can be used an own error messages that uses numbered markers
 * to insert the saved parts from the subpatterns in.
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
	
	private $logFile = '';
	
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
		
		$startTime = 0;
		$endTime = time();
		
		// loading the configuration
		$dateFrom = $this->getConfigValue('date_from');
		$dateTo = $this->getConfigValue('date_to');
		$dateHours = $this->getConfigValue('date_hours');
		$datePattern = $this->getConfigValue('date_pattern');
		$this->logFile = $this->getConfigValue('log_file');
		$patterns = explode("\n",$this->getConfigValue('patterns_configuration'));
		
		// if the file is not available return a result with state error
		if(!file_exists($this->logFile)) return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, 'File doesn\'t exist.');
		
		// eval start and end time
		switch($dateHours) {
			case 0:
				$startTime = $dateFrom;
				$endTime = $dateTo;
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
			default:
				// return an error result to indicate that something went wrong
				return tx_caretaker_TestResult(TX_CARETAKER_STATE_ERROR, 0, 'No valid option for log time was selected!');
				break;
			/*
			 * this could easily leed to memory overflow, so it is disabled
			default:
				$startTime = 0;
				$endTime = time();
				break;
			*/
		}
		
		// get the line count of the file
		$lineCount = $this->getLineCount($logFile);
		
		// if the line count could not be retreived return a result with state error
		if(!$lineCount) return tx_caretaker_TestResukt(TX_CARETAKER_STATE_ERROR, 0, 'Line count of the file could not be retrieved!');
		
		$foundErrors = array();
		
		// read the lines in blocks from the file
		while($lineCount >= 0) {
			
			// get the lines and explode them to an array
			$lines = $this->readLines($lineCount);
			$lines = explode("\n", $lines);
			
			$readedLinesCount = count($lines);
			$lineCount -= $readedLinesCount;
			
			// proceed with the error check
			foreach($lines as $line) {
				
				preg_match($datePattern, $line, $timeMatch);
				$lineTimeStamp = strtotime($timeMatch[0]);
				
				// if the time from the log file is in the requested range check if there is an error
				if($lineTimeStamp >= $startTime && $lineTimeStamp <= $endTime) {
					
					// check each pattern
					foreach($patterns as $pattern) {
						
						// and if it matches
						if(preg_match($pattern, $line, $match)) {
							
							/**
							 * here might happen a memory overflow when there are many errors
							 * to put into the array: If this is the case you should set the
							 * time for the log entries to a lower option
							 */
							// put it into the $foundErrors array
							$foundErrors[] = array($match[1], $match[2]);
						}
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
	 * This function does a system call. It calls wc -l filename to get the lines in the file
	 * @return int The line count of the file
	 */
	private function getLineCount() {
		
		$logFile = $this->logFile;
		
		$descriptorSpecs = array(
			0 => array("pipe","r"),
			1 => array("pipe","w"),
			2 => array("pipe","w")
		);
		$output = '';
		
		$wc = proc_open('wc -l '.$logFile, $descriptorSpecs, $pipes);
		
		if(is_resource($wc)) {
			
			$output = stream_get_contents($pipes[1]);
			fclose($pipes[0]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			
			// important to close all pipes before closeing the process
			// more information: see in php manual
			proc_close($wc);
		}
		
		if(preg_match('/(\d*)/', $output, $match)) {
			
			return (int)$match[1];
		}
		
		return false;
	}
	
	/**
	 * Also calls system functions. It uses head and tail to cut out some lines of the file.
	 * Currently it starts at position provided by line count. It reads from the end of the file
	 * to this position. Tail reads the last - currently 10000 - lines from this part. The lines are
	 * return as a string.
	 * 
	 * @param integer $lineCount
	 * @return string The retreived lines.
	 */
	private function readLines($lineCount) {
		
		$logFile = $this->logFile;
		
		$descriptorSpecs = array(
			0 => array("pipe","r"),
			1 => array("pipe","w"),
			2 => array("pipe","w")
		);
		$output = '';
		
		$headTail = proc_open('head -n -'.$lineCount.' '.$logFile.' | tail -n 10000', $descriptorSpecs, $pipes);
		
		if(is_resource($headTail)) {
			
			$output = stream_get_contents($pipes[1]);
			fclose($pipes[0]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			
			proc_close($headTail);
		}
		
		return $output;
	}
	
	/**
	 * Combines the errors found to a human readable message including the numbers of errors
	 * 
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