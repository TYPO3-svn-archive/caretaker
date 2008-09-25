<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_caretakerpm_flexinfo" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:caretaker_pm/locallang_db.xml:tx_caretaker_instance.tx_caretakerpm_flexinfo",		
		"config" => Array (
			'type' => 'flex',
			'ds'=> Array (
				'default' => 'FILE:EXT:caretaker_pm/flexform_instance_flexinfo.xml', 
			)
		)
	),
);


t3lib_div::loadTCA("tx_caretaker_instance");
t3lib_extMgm::addTCAcolumns("tx_caretaker_instance",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_caretaker_instance","--div--;LLL:EXT:caretaker_pm/locallang_db.xml:tx_caretaker_instance.tab.flexinfo,tx_caretakerpm_flexinfo;;;;1-1-1");

$tempColumns = Array (
	"tx_caretakerpm_flexinfo" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:caretaker_pm/locallang_db.xml:tx_caretaker_group.tx_caretakerpm_flexinfo",		
		"config" => Array (
			'type' => 'flex',
			'ds'=> Array (
				'default' => 'FILE:EXT:caretaker_pm/flexform_group_flexinfo.xml', 
			)
		)
	),
);


t3lib_div::loadTCA("tx_caretaker_group");
t3lib_extMgm::addTCAcolumns("tx_caretaker_group",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_caretaker_group","--div--;LLL:EXT:caretaker_pm/locallang_db.xml:tx_caretaker_group.tab.flexinfo,tx_caretakerpm_flexinfo;;;;1-1-1");
?>