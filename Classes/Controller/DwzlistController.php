<?php
namespace MFG\Dwzlist\Controller;

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
 * @subpackage dwzlist
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
	private $member = NULL;

	/**
	 *
	 */
	private $dwzGraphData = NULL;

	/**
	 * 
	 */
	public function fetchClubData($zps) {
		$this->members = unserialize(file_get_contents('http://www.schachbund.de/php/dewis/verein.php?zps=' . $zps . '&format=array', 'r'));

		foreach($this->members as $key => $row) {
			$this->dateOfLastUpdate = max($this->dateOfLastUpdate, $row['turnierende']);
			$dwz[$key]  = $row['dwz'];
			$dwzIndex[$key]  = $row['dwzIndex'];
		}
		array_multisort($dwz, SORT_DESC, SORT_NUMERIC, $this->members);
	}

	/**
	 *
	 */
	public function fetchMemberData($pkz) {
		$this->member = unserialize(file_get_contents('http://www.schachbund.de/php/dewis/spieler.php?pkz=' . $pkz . '&format=array', 'r'));
		foreach($this->member['turnier'] as $turnier) {
			if ($turnier['dwzneuindex'] > 0) {
				$this->dwzGraphData['values'][] = array('X' => (int)$turnier['dwzneuindex'], 'Y' => (int)$turnier['dwzneu']);
			}
		}
	}

	/**
	 * List all members of a Club
	 *
	 * @return void
	 */
	public function listAction() {
		$zps = $this->settings['zps'];
		$this->fetchClubData($zps);
		$this->view->assignMultiple(array(
			'members' => $this->members,
			'dateOfLastUpdate' => $this->dateOfLastUpdate
		));
	}

	/**
	 * Show a member
	 * @param string $pkz
	 * @param string $dateOfLastUpdate
	 * @return void
	 */
	public function showAction($pkz = NULL, $dateOfLastUpdate = NULL) {
		if ($pkz === NULL) {
			$pkz = $this->settings['pkz'];
		}
		$this->fetchMemberData($pkz);
		$this->view->assignMultiple(array(
			'spieler' => $this->member['spieler'],
			'rang' => $this->member['rang'],
			'mitgliedschaft' => $this->member['mitgliedschaft'],
			'turniere' => $this->member['turnier'],
			'dateOfLastUpdate' => $dateOfLastUpdate,
			'graphData' => json_encode($this->dwzGraphData)
		));
	}
}

?>
