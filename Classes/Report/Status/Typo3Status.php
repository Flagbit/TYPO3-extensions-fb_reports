<?php
namespace Flagbit\FbReports\Report\Status;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Reports\Status;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class Typo3Status extends \TYPO3\CMS\Reports\Report\Status\Typo3Status {

	/**
	 * Simply gets the current TYPO3 version.
	 *
	 * @return array with release information or false if
	 */
	protected function getTypo3VersionStatus() {
		$typo3ReleaseInformation = $this->getTypo3ReleaseInformation();

		// only write out current version if fetching the release information fails
		if ($typo3ReleaseInformation === FALSE) {
			return GeneralUtility::makeInstance('TYPO3\\CMS\\Reports\\Status', 'TYPO3', TYPO3_version, '', Status::NOTICE);
		}

		// no release information available for this version. Maybe on a development release.
		$releaseInformation = $typo3ReleaseInformation[$this->getCurrentMajorVersion()];
		if (empty($releaseInformation)) {
			return GeneralUtility::makeInstance('TYPO3\\CMS\\Reports\\Status', 'TYPO3', TYPO3_version, LocalizationUtility::translate('status_noReleaseInformation', 'fb_reports'), Status::NOTICE);
		}

		$releasesOfCurrentVersion = $releaseInformation['releases'];
		$latestVersionNumber = $releaseInformation['stable'];

		// report an error when current installation is not on latest minor version release
		$currentRelease = $releasesOfCurrentVersion[TYPO3_version];
		$latestRelease = $releasesOfCurrentVersion[$latestVersionNumber];
		if (array_diff($currentRelease, $latestRelease)) {
			return GeneralUtility::makeInstance('TYPO3\\CMS\\Reports\\Status', 'TYPO3', TYPO3_version, sprintf(LocalizationUtility::translate('status_typo3VersionOld', 'fb_reports'), $latestRelease['version']), Status::ERROR);
		}

		// report an error when major version is outdated
		if (!$releaseInformation['active']) {
			return GeneralUtility::makeInstance('TYPO3\\CMS\\Reports\\Status', 'TYPO3', TYPO3_version, sprintf(LocalizationUtility::translate('status_typo3VersionOutdated', 'fb_reports'), $typo3ReleaseInformation['latest_lts']), Status::ERROR);
		}

		return GeneralUtility::makeInstance('TYPO3\\CMS\\Reports\\Status', 'TYPO3', TYPO3_version, LocalizationUtility::translate('status_typo3VersionUpToDate', 'fb_reports'), Status::OK);
	}

	/**
	 * Fetches the release information online.
	 *
	 * @return array with release information or false if an error comes up
	 */
	private function getTypo3ReleaseInformation() {
		$extConf =  unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['fb_reports']);
		$url = $extConf['versionInformationJson'];

		if (! GeneralUtility::isValidUrl($url)) {
			return FALSE;
		}

		$json = GeneralUtility::getUrl($url);
		return json_decode($json, TRUE);
	}

	/**
	 * Returns the current major version number as String (format e.g. 6.2)
	 *
	 * @return string version number
	 */
	private function getCurrentMajorVersion() {
		$currentVersion = VersionNumberUtility::convertVersionStringToArray(TYPO3_version);
		return $currentVersion['version_main'] . '.' . $currentVersion['version_sub'];
	}
}
