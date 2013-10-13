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
	private $performanceGraphData = NULL;

	/**
	 *
	 */
	public function fetchClubData($zps) {
		$this->members = unserialize(file_get_contents('http://www.schachbund.de/php/dewis/verein.php?zps=' . $zps . '&format=array', 'r'));

		if ($this->members !== false) {
			foreach($this->members as $key => $row) {
				$this->dateOfLastUpdate = max($this->dateOfLastUpdate, $row['turnierende']);
				$dwz[$key]  = $row['dwz'];
				$dwzIndex[$key]  = $row['dwzIndex'];
			}
			array_multisort($dwz, SORT_DESC, SORT_NUMERIC, $this->members);
		} else {
			$this->members = unserialize(file_get_contents('http://www.schachbund.de/php/dewis/verband.php?zps=' . $zps . '&format=array', 'r'));
		}
	}

	/**
	 *
	 */
	public function fetchMemberData($pkz) {
		$this->member = unserialize(file_get_contents('http://www.schachbund.de/php/dewis/spieler.php?pkz=' . $pkz . '&format=array', 'r'));

		foreach($this->member['turnier'] as &$turnier) {
			$turnier['dwzchange'] = ($turnier['dwzalt'] > 0 && $turnier['dwzneu'] > 0) ? $turnier['dwzneu'] - $turnier['dwzalt'] : 0;
			if ($turnier['dwzneuindex'] > 0) {
				$this->dwzGraphData['values'][] = array('X' => (int)$turnier['dwzneuindex'], 'Y' => (int)$turnier['dwzneu']);
				if ($turnier['leistung'] > 0) {
					$this->performanceGraphData['values'][] = array('X' => (int)$turnier['dwzneuindex'], 'Y' => (int)$turnier['leistung']);
				}
			}
		}
	}

	/**
	 * List all members of a Club
	 *
	 * @param string $zps
	 * @return void
	 */
	public function listAction($zps = NULL) {
		if ($zps === NULL) {
			$zps = $this->settings['zps'];
		}
		$this->fetchClubData($zps);
		$this->view->assignMultiple(array(
			'zps' => $zps,
			'members' => $this->members,
			'dateOfLastUpdate' => $this->dateOfLastUpdate
		));
	}

	/**
	 * Show a member
	 * @param string $pkz
	 * @param string $zps The zps of the calling list
	 * @param string $dateOfLastUpdate
	 * @return void
	 */
	public function showAction($pkz = NULL, $zps = NULL, $dateOfLastUpdate = NULL) {
		if ($pkz === NULL) {
			$pkz = $this->settings['pkz'];
		}
		$this->fetchMemberData($pkz);
		$this->view->assignMultiple(array(
			'spieler' => $this->member['spieler'],
			'rang' => $this->member['rang'],
			'mitgliedschaft' => $this->member['mitgliedschaft'],
			'turniere' => $this->member['turnier'],
			'zps' => $zps,
			'dateOfLastUpdate' => $dateOfLastUpdate,
			'dwzGraphData' => json_encode($this->dwzGraphData),
			'performanceGraphData' => json_encode($this->performanceGraphData)
		));
	}
}

?>