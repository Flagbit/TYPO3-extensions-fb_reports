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
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Reports\Status;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class SecurityStatus implements \TYPO3\CMS\Reports\StatusProviderInterface {

	protected $filesExtensionsToFind = array ('*.sql','*.sql.gz','*.sql.gzip','*.sql.bzip2','*.sql.bz2','*.tar','*.tar.gz','*.tar.bz2');

	/**
	 * Return status checks
	 *
	 * @return array List of statuses
	 */
	public function getStatus() {
		$statuses = array();
		$statuses['sensitiveFilesDownloadable'] = $this->sensitiveFilesDownloadableStatus();
		return $statuses;
	}

	/**
	 * Checks if in root folder and in the the first level of folders there are sensitive files that can be downloaded
	 *
	 * @return Status An object representing whether there are sensitive files in root folder or in the the first level of folders that can be downloaded
	 */
	protected function sensitiveFilesDownloadableStatus() {
		$value = $this->getLanguageService()->getLL('status_none');
		$severity = Status::OK;
		$message = '';

		$foldersToSearchForFiles = iterator_to_array(new \FilesystemIterator(PATH_site));
		$foldersToSearchForFiles[] = new \SplFileInfo(PATH_site);

		$filesToTestDownload = array();
		foreach ($foldersToSearchForFiles as $folderToSearchForFile) {
			if($folderToSearchForFile->isDir()) {
				foreach ($this->filesExtensionsToFind as $fileExtensionToFind) {
					$iterator = new \GlobIterator($folderToSearchForFile . DIRECTORY_SEPARATOR . trim($fileExtensionToFind));
					/** @var \SplFileInfo $splFileInfo */
					foreach ($iterator as $splFileInfo) {
						$filesToTestDownload[] = substr($splFileInfo->getPathname(), strlen(PATH_site));
						break(1);
					}
				}
			}
		}

		$typo3RequestHost = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
		$insecureFilesToDownload = array();
		foreach ($filesToTestDownload as $fileToTestDownload) {
			$downloadTestFileUrl = $typo3RequestHost . '/' . $fileToTestDownload;
			$headers = GeneralUtility::getUrl($downloadTestFileUrl, 2);
			if (preg_match('/^.*200.*\\n/', $headers)) {
				$insecureFilesToDownload[] = $downloadTestFileUrl;
			}
		}

		if (!empty($insecureFilesToDownload)) {
			$value = $this->getLanguageService()->getLL('status_insecure');
			$severity = Status::ERROR;
			$insecureFileToDownloadLinks = array();
			foreach ($insecureFilesToDownload as $insecureFileToDownload) {
				$insecureFileToDownloadLinks[] = '<a href="' . htmlspecialchars($insecureFileToDownload) . '" >' . $insecureFileToDownload . '</a>';
			}
			$message = sprintf(LocalizationUtility::translate('status_sensitiveFilesDownloadableInfo', 'fb_reports'), implode(', ', $this->filesExtensionsToFind), implode('<br />', $insecureFileToDownloadLinks));
		}

		return GeneralUtility::makeInstance('TYPO3\\CMS\\Reports\\Status', LocalizationUtility::translate('status_sensitiveFilesDownloadable', 'fb_reports'), $value, $message, $severity);
	}

	/**
	 * Returns Language Service
	 *
	 * @return LanguageService
	 */
	private function getLanguageService() {
		return $GLOBALS['LANG'];
	}

}
