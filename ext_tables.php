<?php

if (!defined('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE === 'BE') {
	if (class_exists('\\TYPO3\\CMS\\Core\\Utility\\VersionNumberUtility') && (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger('6.0.0'))) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['typo3'][] = 'Flagbit\\FbReports\\Report\\Status\\Typo3Status';
	} else {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['typo3'][] = 'tx_fbreports_report_status_typo3statusv4';
	}
}