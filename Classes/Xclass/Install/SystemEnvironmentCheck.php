<?php
namespace Flagbit\FbReports\Xclass\Install;

/*
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Status\StatusInterface;

/**
 * XClass for system environment check of Install tool
 * Simply adds a devlog to debug system environment check. Debugging
 * environment errors during cronjobs is simplified
 *
 * @author Frederic Gaus <frederic.gaus@flagbit.de>
 */
class SystemEnvironmentCheck extends \TYPO3\CMS\Install\SystemEnvironment\Check {

	/**
	 * Get all status information as array with status objects
	 *
	 * @return \TYPO3\CMS\Install\Status\StatusInterface[]
	 */
	public function getStatus() {
		$statusObjects = parent::getStatus();
		$filteredObjects = array();

		/** @var $status StatusInterface */
		foreach ($statusObjects as $status) {
			if ($status->getSeverity() === 'warning' || $status->getSeverity() === 'error') {
				$filteredObjects[] = $status;
			}
		}

		if (!empty($filteredObjects)) {
			GeneralUtility::devLog('StatusReport', 'fb_reports', '2', $filteredObjects);
		}

		return $statusObjects;
	}

}
