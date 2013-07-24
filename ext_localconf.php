<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'MFG.' . $_EXTKEY,
	'Dwzlist',
	array(
		'Dwzlist' => 'show'
	),
	// non-cacheable actions
	array(
		
	)
);

?>
