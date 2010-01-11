<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Register Caretaker Services
if (t3lib_extMgm::isLoaded('caretaker') ){
	include_once(t3lib_extMgm::extPath('caretaker') . 'classes/helpers/class.tx_caretaker_ServiceHelper.php');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerrsml',  'RSML Test', 'Remote Status Markup Language Test.');
}

?>