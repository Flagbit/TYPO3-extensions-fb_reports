<?php

if (!defined('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE === 'BE') {
	if (class_exists('t3lib_div') && (t3lib_div::int_from_ver(TYPO3_version) < t3lib_div::int_from_ver('6.0.0'))) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['typo3'][] = 'tx_fbreports_report_status_typo3statusv4';
	} else {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['typo3'][] = 'Flagbit\\FbReports\\Report\\Status\\Typo3Status';
	}
}

?>