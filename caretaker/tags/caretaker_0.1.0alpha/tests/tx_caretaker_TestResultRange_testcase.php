<?php 

require_once (t3lib_extMgm::extPath('caretaker').'/classes/results/class.tx_caretaker_TestResult.php');
require_once (t3lib_extMgm::extPath('caretaker').'/classes/results/class.tx_caretaker_TestResultRange.php');

class tx_caretaker_TestResultRange_testcase extends tx_phpunit_testcase  {

	var $test_result_range;
	
	function setUp(){
		
		$this->test_result_range = new tx_caretaker_TestResultRange(500, 1000);
		
		$this->test_result_range->addResult(
			new tx_caretaker_TestResult(500, tx_caretaker_Constants::state_ok, 0, '')
		);
		
		$this->test_result_range->addResult(
			new tx_caretaker_TestResult(600, tx_caretaker_Constants::state_error, 0, '')
		);
		
		$this->test_result_range->addResult(
			new tx_caretaker_TestResult(650, tx_caretaker_Constants::state_ok, 0, '')
		);
		
		$this->test_result_range->addResult(
			new tx_caretaker_TestResult(800, tx_caretaker_Constants::state_warning, 0, '')
		);
		
		$this->test_result_range->addResult(
			new	tx_caretaker_TestResult(900, tx_caretaker_Constants::state_ok, 0, '')
		);
		
		$this->test_result_range->addResult(
			new tx_caretaker_TestResult(930, tx_caretaker_Constants::state_undefined, 0, '')
		);
		
		$this->test_result_range->addResult(
			new tx_caretaker_TestResult(950, tx_caretaker_Constants::state_ok, 0, '')
		);
		
	}
	
	function test_MinMaxTS(){
		$this->assertEquals( $this->test_result_range->getMinTstamp() ,    500 );
		$this->assertEquals( $this->test_result_range->getMaxTstamp() ,    1000 );
	}
	
	function test_get_state_infos(){
	
		$info = $this->test_result_range->getInfos();
		
		$this->assertEquals( $info['SecondsTotal'],    500 );
		$this->assertEquals( $info['SecondsOK'],       280 );
		$this->assertEquals( $info['SecondsUNDEFINED'], 20 );
		$this->assertEquals( $info['SecondsERROR'],     50 );
		$this->assertEquals( $info['SecondsWARNING'],  100 );
		
	}
	

	function test_get_availability_infos(){
	
		$info = $this->test_result_range->getInfos();
		
		$this->assertEquals( $info['PercentAVAILABLE'],330/500 );
		$this->assertEquals( $info['PercentOK'],       280/500 );
		$this->assertEquals( $info['PercentERROR'],     50/500 );
		$this->assertEquals( $info['PercentWARNING'],  100/500 );
		$this->assertEquals( $info['PercentUNDEFINED'], 20/500 );
		
	}
	
	function test_get_length(){
		$this->assertEquals( $this->test_result_range->getLength(), 7 );
	}
	
	
	
}
?>