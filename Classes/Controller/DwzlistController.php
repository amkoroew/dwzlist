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
	public function parseClubCSV($zps) {
		$fp = fopen('http://www.schachbund.de/dwz/db/verein-csv.php?zps=' . $zps, 'r');
		if ($fp !== FALSE) {
			$keys = array('clubNumber', 'memberNumber', 'state', 'name', 'gender', 'birthYear', 'fideTitle', 'weekOfLastEvaluation', 'dwz', 'dwzIndex', 'elo');
			$this->dateOfLastUpdate = fgets($fp);
			while(!feof($fp)) {
				$tmp = array_combine($keys, fgetcsv($fp, 1024, '|'));
				if ($tmp !== FALSE) {
					$tmp['zps'] = $tmp['clubNumber'] . '-' . $tmp['memberNumber'];
					$tmp['yearOfLastEvaluation'] = substr($tmp['weekOfLastEvaluation'], 0, 4);
					$tmp['weekOfLastEvaluation'] = substr($tmp['weekOfLastEvaluation'], -2);
					$this->members[] = $tmp;
				}
			}
			fclose($fp);
			$this->convertAndCleanArray($this->members);
		}
	}

	/**
	 * 
	 */
	public function parseMemberCSV($zps) {
		$fp = fopen('http://www.schachbund.de/dwz/db/spieler-csv.php?zps=' . $zps, 'r');
		if ($fp !== FALSE) {
			$memberKeys = array('clubNumber', 'memberNumber', 'state', 'name', 'gender', 'birthYear', 'fideTitle', 'weekOfLastEvaluation', 'dwz', 'dwzIndex');
			$eloKeys = array('elo', 'games', 'title', 'id', 'country');
			$tournamentKeys = array('entryNumber', 'tournamentCode', 'tournamentName', 'points', 'games', 'expectedValue', 'opponents', 'performance', 'dwz', 'dwzIndex');
			$this->dateOfLastUpdate = fgets($fp);
			$tmp = array_combine($memberKeys, fgetcsv($fp, 1024, '|'));
			if ($tmp !== FALSE) {
				$tmp['zps'] = $tmp['clubNumber'] . '-' . $tmp['memberNumber'];
				$tmp['yearOfLastEvaluation'] = substr($tmp['weekOfLastEvaluation'], 0, 4);
				$tmp['weekOfLastEvaluation'] = substr($tmp['weekOfLastEvaluation'], -2);
				$this->member['member'] = $tmp;
			}
			$tmp = array_combine($eloKeys, fgetcsv($fp, 1024, '|'));
			if ($tmp !== FALSE) {
				$this->member['elo'] = $tmp;
			}
			while(!feof($fp)) {
				$tmp = array_combine($tournamentKeys, fgetcsv($fp, 1024, '|'));
				if ($tmp !== FALSE) {
					$this->member['tournaments'][] = $tmp;
				}
			}
			fclose($fp);
			$this->convertAndCleanArray($this->member);
		}
	}

	/**
	 *
	 */
	public function convertAndCleanArray(&$array) {
		if ($array === NULL) {
			$array = array();
		} elseif (is_array($array)) {
			foreach($array as &$a) {
				if (is_array($a)) {
					$this->convertAndCleanArray($a);
				} else {
					$a = iconv('ISO-8859-1', 'UTF-8', $a);
					$a = strip_tags($a);
					$a = str_replace(',', ', ', $a);
					$a = str_replace('&frac12;', '1/2', $a);
					$a = str_replace('&nbsp;', '', $a);
				}
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
		$this->parseClubCSV($zps);
		$this->view->assignMultiple(array(
			'members' => $this->members,
			'dateOfLastUpdate' => $this->dateOfLastUpdate
		));
	}

	/**
	 * Show a member
	 * @param string $zps
	 * @return void
	 */
	public function showAction($zps = NULL) {
		if ($zps === NULL) {
			$zps = $this->settings['zps'];
		}
		$this->parseMemberCSV($zps);
		$this->view->assignMultiple(array(
			'member' => $this->member['member'],
			'elo' => $this->member['elo'],
			'tournaments' => $this->member['tournaments'],
			'dateOfLastUpdate' => $this->dateOfLastUpdate
		));
	}
}

?>
