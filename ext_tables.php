<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'MFG.' . $_EXTKEY,
	'Dwzlist',
	'DWZ list'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('MFG.' . $_EXTKEY, 'Configuration/TypoScript', 'DWZ list');

?>
