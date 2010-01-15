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
 * $Id: class.tx_caretaker_Cli.php 28420 2010-01-05 16:51:51Z martoro $
 */

/**
 * The caretaker RSML Test.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker
 */
class tx_caretakerrsmlTestService extends tx_caretaker_TestServiceBase {
	
	private $state = TX_CARETAKER_STATE_OK;

	/**
	 * 
	 * @return tx_caretaker_TestResult
	 */
	public function runTest(){

		$config = $this->getConfiguration();

		$rsmlUrl   = $config['rsmlUrl'];
		if (strpos( $rsmlUrl, '://' ) === false  && $this->instance) {
			$rsmlUrl = $this->instance->getUrl() . '/' . $rsmlUrl;
		}

		$expectedRsmlId      = $config['expectedRsmlId'];
		$expectedRsmlVersion = $config['expectedRsmlVersion'];
		$expectedStatus      = $config['expectedStatus'];
		$expectedValue       = $config['expectedValue'];

		print_r ( $config);
		
		if ( ! ( $rsmlUrl && $expectedRsmlId && $expectedRsmlVersion ) ) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, 'You have to define URL ID and Version conditions for this test'.chr(10).var_export($config, true) );
		} else {
			$httpResult = $this->executeHttpRequest($rsmlUrl);
			if ( $httpResult['response'] && $httpResult['info']['http_code'] == 200 ){

				try {
					$xml = new SimpleXMLElement( $httpResult['response'] );
				} catch (Exception $e ){
					return tx_caretaker_TestResult::create( TX_CARETAKER_STATE_ERROR, 0, 'Returened result xml could not be parsed. ' . chr(10) . htmlspecialchars($httpResult['response']) );
				}
				
				$returnedRsmlId      = ( isset($xml->rsmlId) ) ? (string)$xml->rsmlId : false;
				$returnedRsmlVersion = ( isset($xml->rsmlVersion) ) ?  (string)$xml->rsmlVersion : false;

				$returnedStatus  = ( isset($xml->status)  ) ? (string)$xml->status  : false;
				$returnedValue   = ( isset($xml->value)   ) ? (string)$xml->value   : 0;
				$returnedMessage = ( isset($xml->message) ) ? (string)$xml->message : false;
				$returnedDescription = ( isset($xml->description) ) ? (string)$xml->description : false;

				$message = '';
				$submessages = array();
				
					// script id is wrong
				if ( !$returnedRsmlId || $returnedRsmlId != $expectedRsmlId ) {
					$this->decreaseState( TX_CARETAKER_STATE_ERROR );
					$submessages[] = new tx_caretaker_ResultMessage(
						'Script ID was wrong. Expected ###VALUE_EXPECTED### returned ###VALUE_RETURNED###',
						array( 'expected'=>$expectedRsmlId, 'returned' =>$returnedRsmlId )
					);
				}

					// script version is wrong
				if ( !$returnedRsmlVersion || t3lib_div::int_from_ver($returnedRsmlVersion) != t3lib_div::int_from_ver( $expectedRsmlVersion ) ) {
					$this->decreaseState( TX_CARETAKER_STATE_ERROR );
					$submessages[] = new tx_caretaker_ResultMessage(
						'Script Version was wrong. Expected ###VALUE_EXPECTED### returned ###VALUE_RETURNED###',
						array( 'expected'=>$expectedRsmlVersion, 'returned' =>$returnedRsmlVersion )
					);
				}

					// if the checks until now were ok
				if ( $this->status == 0  ){
					
						// show description
					if ($returnedDescription) {
						$message = $returnedDescription;
					}

					if ( $expectedStatus || (int)$returnedStatus !== (int)$expectedStatus ){
						if ($returnedStatus){
							$this->decreaseState( $returnedStatus );
						} else {
							$this->decreaseState( TX_CARETAKER_STATE_ERROR );
						}
						$submessages[] = new tx_caretaker_ResultMessage(
							'Status was wrong. Expected ###VALUE_EXPECTED### returned ###VALUE_RETURNED###',
							array( 'expected'=>$expectedStatus, 'returned' =>$returnedStatus )
						);
					}

						// value
					if ( $expectedValue && !$this->isValueInRange( $returnedValue, $expectedValue)  ){
						$this->decreaseState( TX_CARETAKER_STATE_ERROR );
						$submessages[] = new tx_caretaker_ResultMessage(
							'Value was wrong. Expected ###VALUE_EXPECTED### returned ###VALUE_RETURNED###',
							array( 'expected'=>$expectedValue, 'returned' =>$returnedValue )
						);
					}

						// submessages
					if ($returnedMessage){
						$submessages[] = new tx_caretaker_ResultMessage( 'Messages:' . chr(10) . $returnedMessage );
					}
				}

				return tx_caretaker_TestResult::create( $this->state, $returnedValue, $message, $submessages );

			} else {

				return tx_caretaker_TestResult::create( TX_CARETAKER_STATE_ERROR, 0, 'Unexpected Script Response' . chr(10) . $rsmlUrl . chr(10).var_export($httpResult, true) );
				
			}
		}

	}

	/**
	 * Get the configuration for the Test
	 * 
	 * @return array 
	 */
	public function getConfiguration(){
		
		print_r( array( $this->getConfigValue('expected_status') ) );
		
		$config = array(
			"instanceUrl"         => ( ($this->instance) ? $this->instance->getUrl() : '' ),
			"rsmlUrl"             => $this->getConfigValue('rsml_url'),
			"expectedRsmlId"      => $this->getConfigValue('rsml_id'),
			"expectedRsmlVersion" => $this->getConfigValue('rsml_version'),
			"expectedStatus"      => $this->getConfigValue('expected_status'),
			"expectedValue"       => $this->getConfigValue('expected_value')
		);

		return $config;
	}


	/**
	 * Save the new state if it is worse than current
	 * @param integer $new_state
	 */
	protected function decreaseState ( $new_state ){
		if ($this->state < $new_state){
			$this->state = $new_state;
		}
	}

	/**
	 *
	 * @param float Value to check
	 * @param string Value range: '<2:12.4..18:=25:>6'
	 * @rturn bolean
	 */
	function isValueInRange ($value , $range_definition){

		$result = false;
		$value  = (float)$value;
		$ranges = explode(':',$range_definition);
		foreach ( $ranges as $range	){
			$range = trim($range);
				// 12.3..456.89
			if ( strpos( $range, '..' ) !== FALSE ) {
				list($min, $max) = explode('..',$range, 2);
				if ( $value >= (float)$min && $value <= (float)$max ){
					$result = true;
				}
				// < 6.25
			} else if ( substr($range,0,1) == '<' ){
				$min = (float)substr($range,1);
				if ($value < $min ){
					$result = true;
				}
				// > 6.25
			} else if ( substr($range,0,1) == '>' ){
				$max = (float)substr($range,1);
				if ($value > $max ){
					$result = true;
				}
				// = 6.25
			} else if ( substr($range,0,1) == '=' ){
				$target = (float)substr($range,1);
				if ($target == $value ){
					$result = true;
				}
			}
		}
		return $result;
	}
	
	/**
	 * Execute a HTTP request for the POST values via CURL
	 *
	 * @param $requestUrl string The URL for the HTTP request
	 * @param $postValues array POST values with key / value
	 * @return array info/response
	 */
	protected function executeHttpRequest($requestUrl, $postValues = null) {
		$curl = curl_init();
        if (!$curl) {
        	return false;
        }

		curl_setopt($curl, CURLOPT_URL, $requestUrl);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$headers = array(
            "Cache-Control: no-cache",
            "Pragma: no-cache"
        );
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		if (is_array($postValues)) {
			foreach($postValues as $key => $value) {
				$postQuery .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			rtrim($postQuery, '&');

			curl_setopt($curl, CURLOPT_POST, count($postValues));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postQuery);
		}

		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		return array(
			'response' => $response,
			'info' => $info
		);
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_extensions.php']);
}
?>