<?php
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Performs basic checks about the TYPO3 install
 *
 * @author Frederic Gaus <frederic.gaus@flagbit.de>
 */

class tx_fbreports_report_status_typo3statusv4 extends tx_reports_reports_status_Typo3Status {

	/**
	 * Simply gets the current TYPO3 version.
	 *
	 * @return array with release information or false if
	 */
	protected function getTypo3VersionStatus() {
		$this->getLanguageService()->includeLLFile('EXT:fb_reports/Resources/Private/Language/locallang.xml');
		$typo3ReleaseInformation = $this->getTypo3ReleaseInformation();

		// only write out current version if fetching the release information fails
		if ($typo3ReleaseInformation === FALSE) {
			return t3lib_div::makeInstance('tx_reports_reports_status_Status', 'TYPO3', TYPO3_version, '', tx_reports_reports_status_Status::NOTICE);
		}
		$releaseInformation = $typo3ReleaseInformation[$this->getCurrentMajorVersion()];

		$releasesOfCurrentVersion = $releaseInformation['releases'];
		$latestVersionNumber = $releaseInformation['stable'];

		// report an error when current installation is not on latest minor version release
		$currentRelease = $releasesOfCurrentVersion[TYPO3_version];
		$latestRelease = $releasesOfCurrentVersion[$latestVersionNumber];
		if (array_diff($currentRelease, $latestRelease)) {
			return t3lib_div::makeInstance('tx_reports_reports_status_Status', 'TYPO3', TYPO3_version, sprintf($this->getLanguageService()->getLL('status_typo3VersionOld'), $latestRelease['version']), tx_reports_reports_status_Status::ERROR);
		}

		// report an error when major version is outdated
		if (!$releaseInformation['active']) {
			return t3lib_div::makeInstance('tx_reports_reports_status_Status', 'TYPO3', TYPO3_version, sprintf($this->getLanguageService()->getLL('status_typo3VersionOutdated'), $typo3ReleaseInformation['latest_lts']), tx_reports_reports_status_Status::ERROR);
		}

		return t3lib_div::makeInstance('tx_reports_reports_status_Status', 'TYPO3', TYPO3_version, $this->getLanguageService()->getLL('status_typo3VersionUpToDate'), tx_reports_reports_status_Status::OK);
	}

	/**
	 * Fetches the release information online.
	 *
	 * @return array with release information or false if an error comes up
	 */
	private function getTypo3ReleaseInformation() {
		$extConf =  unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['fb_reports']);
		$url = $extConf['versionInformationJson'];

		if (! t3lib_div::isValidUrl($url)) {
			return FALSE;
		}

		$json = t3lib_div::getUrl($url);
		return json_decode($json, TRUE);
	}

	/**
	 * Returns the current major version number as String (format e.g. 6.2)
	 *
	 * @return string version number
	 */
	private function getCurrentMajorVersion() {
		$versionParts = explode('.', TYPO3_version);
		return $versionParts[0] . '.' . $versionParts[1];
	}

	/**
	 * Returns Language Service
	 *
	 * @return language
	 */
	private function getLanguageService() {
		return $GLOBALS['LANG'];
	}
}
