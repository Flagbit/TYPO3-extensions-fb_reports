<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

#Define XCLASS for TSFE
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Install\\SystemEnvironment\\Check'] = array(
	'className' => 'Flagbit\\FbReports\\Xclass\\Install\\SystemEnvironmentCheck'
);

?>