<?php
namespace MFG\MfgDwzlist\Controller;

/***************************************************************
 *  Copyright notice
 *  (c) 2013 Matthias Gugel <mail@matthias-gugel.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Base controller
 *
 * @package TYPO3
 * @subpackage mfg_dwzlist
 */
class DwzlistController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * 
	 */
	private $dateOfLastUpdate = NULL;

	/**
	 * 
	 */
	private $members = NULL;

	/**
	 * 
	 */
	public function parseClubCSV($zps) {
		$fp = fopen('http://www.schachbund.de/dwz/db/verein-csv.php?zps=' . $zps, 'r');
		if ($fp !== FALSE) {
			$keys = array('clubNumber', 'memberNumber', 'state', 'name', 'gender', 'birthYear', 'fideTitle', 'weekOfLastEvaluation', 'dwz', 'dwzIndex', 'elo');
			$this->dateOfLastUpdate = fgets($fp);
			while(!feof($fp)) {
				$tmp = array_combine($keys, fgetcsv($fp, 1024, '|'));
				if ($tmp !== FALSE) {
					$this->members[] = $tmp;
				}
			}
			fclose($fp);
		}
	}

	/**
	 * 
	 */
	public function convert2utf8() {
		foreach($this->members as &$member) {
			foreach($member as &$data) {
				$data = iconv('ISO-8859-1', 'UTF-8', $data);
			}
		}
	}

	/**
	 * List all members of a Club
	 *
	 * @return void
	 */
	public function listAction() {
		$this->parseClubCSV('C0308');
		$this->convert2utf8();
		$this->view->assignMultiple(array(
			'members' => $this->members,
			'dateOfLastUpdate' => $this->dateOfLastUpdate
		));
	}
}

?>
