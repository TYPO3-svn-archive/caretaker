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
 * A series of nodeResults over a timerange. The class is mainly
 * used for generatig graph- and log-views.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker
 */
abstract class tx_caretaker_NodeResultRange implements Iterator {
	
	/**
	 * Array of testResults
	 * @var array
	 */
	private $array = array();
	
	/**
	 * Minimal Timestamp of this Result Range
	 * 
	 * @var integer 
	 */
	private $start_timestamp = NULL;
	
	/**
	 * Maximal Timestamp of this Result Range
	 * 
	 * @var integer
	 */
	private $end_timestamp = NULL;
	
	/**
	 * timestamp of last existing value
	 *   
	 * @var unknown_type
	 */
	private $last_timestamp = 0;
	
	/**
	 * Constructur
	 * 
	 * @param integer $start_timestamp
	 * @param integer $end_timestamp
	 */
	public function __construct($start_timestamp, $end_timestamp){
		$this->end_timestamp = $end_timestamp;
		$this->start_timestamp = $start_timestamp;
	}
	
	/**
	 * Add a Result to Range
	 * 
	 * @param tx_caretaker_NodeResult $result
	 */
	public function addResult($result){
		
		$ts = (int)$result->getTstamp();
		$this->array[$ts] = $result;
		
		if ($ts < $this->last_timestamp){
			ksort( $this->array );
		} else {
			$this->last_timestamp = $ts;
		}
		
		if ($this->start_timestamp !== NULL && $ts < $this->start_timestamp ){
			$this->start_timestamp  = $ts;
		} 
		
		if ($this->end_timestamp  !== NULL && $ts > $this->end_timestamp){
			$this->end_timestamp = $ts;
		}
	}
	
	/**
	 * Return minimal timestamp
	 * 
	 * @return integer
	 * @depricated
	 * @todo remove this method  
	 */
	public function getMinTstamp(){
		return $this->start_timestamp;
	}
	
	/**
	 * Return minimal timetamp
	 * 
	 * @return unknown_type
	 */
	public function getStartTimestamp(){
		return $this->start_timestamp;
	}
	
	/**
	 * Return maximal timestamp
	 * 
	 * @return unknown_type
	 * @depricated
	 * @todo remove this method
	 */
	public function getMaxTstamp(){
		return $this->end_timestamp;
	}
	
	/**
	 * Return maximal timestamp
	 * 
	 * @return unknown_type
	 * @depricated
	 * @todo remove this method
	 */
	public function getEndTimestamp(){
		return $this->end_timestamp;
	}
	
	/**
	 * Get the number of Results 
	 *  
	 * @return integer
	 */
	public function getLength(){
		return count($this->array);	
	}
	
	/**
	 * Get the first Result
	 * 
	 * @return tx_caretaker_NodeResult
	 */
	public function getFirst(){
		return reset($this->array);
	}
	
	/**
	 * Get the last Result
	 * 
	 * @return tx_caretaker_NodeResult
	 */
	public function getLast(){
		return end($this->array);
	}
	
	/**
	 * Get some Statistic Information as an array
	 * 
	 * @return array
	 */
	public function getInfos(){
		
		$SecondsTotal     = $this->end_timestamp - $this->start_timestamp;
		$SecondsUNDEFINED = 0;
		$SecondsOK        = 0;
		$SecondsWARNING   = 0;
		$SecondsERROR     = 0;
		$SecondsACK       = 0;
		$SecondsDUE       = 0;
		
		$lastTS    = NULL;
		$lastSTATE = NULL;
		foreach( $this->array as $ts=>$result ){
			if ($lastTS){
				
				$range = $ts - $lastTS;
				
				switch ($lastSTATE){
					case tx_caretaker_Constants::state_due :
						$SecondsDUE += $range;
						break;
					case tx_caretaker_Constants::state_ack :
						$SecondsACK += $range;
						break;	
					case tx_caretaker_Constants::state_undefined :
						$SecondsUNDEFINED += $range;
						break;
					case tx_caretaker_Constants::state_ok :
						$SecondsOK += $range;
						break;
					case tx_caretaker_Constants::state_warning:
						$SecondsWARNING += $range;
						break;
					case tx_caretaker_Constants::state_error:
						$SecondsERROR += $range;
						break;
				}
			}
			$lastTS    = $ts;
			$lastSTATE = $result->getState();
			
		}
		
		return array(
			'SecondsTotal'      =>$SecondsTotal,
			'SecondsUNDEFINED'  =>$SecondsUNDEFINED,
			'SecondsOK'         =>$SecondsOK,
			'SecondsWARNING'    =>$SecondsWARNING,
			'SecondsERROR'      =>$SecondsERROR,
			'SecondsACK'        =>$SecondsACK,
			'SecondsDUE'        =>$SecondsACK,
		
			'PercentAVAILABLE'  => ($SecondsTotal - $SecondsERROR - $SecondsWARNING - $SecondsUNDEFINED )/$SecondsTotal,
			'PercentUNDEFINED'  => $SecondsUNDEFINED/$SecondsTotal,
			'PercentOK'         => $SecondsOK/$SecondsTotal,
			'PercentWARNING'    => $SecondsWARNING/$SecondsTotal,
			'PercentERROR'      => $SecondsERROR/$SecondsTotal,
			'PercentACK'        => $SecondsACK/$SecondsTotal,
			'PercentDUE'        => $SecondsDUE/$SecondsTotal,
 		);
		
	}	
	
		// Iterator methods
	/**
	 * Reset the counter and return the first result
	 */
	public function rewind() {
		reset($this->array);
	}

	/**
	 * Get the current Result
	 * @return tx_caretaker_NodeResult
	 */
	public function current() {
		return current($this->array);
	}

	/**
	 * Return then current result number 
	 * @return integer
	 */
	public function key() {
		return key($this->array);
	}

	/**
	 * Go to the next result and return it
	 */
	public function next() {
		return next($this->array);
	}

	/**
	 * Check if the current value really exists
	 * @return boolean
	 */
	public function valid() {
		return isset( $this->array[key($this->array)] );
	}
	
	/**
	 * Reverses the array of results
	 * @return void
	 */
	public function reverse() {
		
		$this->array = array_reverse($this->array);
	}

	/**
	 * 
	 * @return integer
	 */
	public function count(){
		return count($this->array);
	}
}

?>