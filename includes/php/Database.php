<?php
require_once PHP_INCLUDES_DIR.'Database_Connection.php';
require_once PHP_INCLUDES_DIR.'Genome.php';

class Database extends Database_Connection {

	function __construct($new_link = true) {
		return parent::__construct('db_name', 'db_user', 'db_pass', 'db_address', $new_link);
	}

	// create a new genome object by passing array corresponding to database columns
	private function newGenome($arr) {
		if (!is_array($arr)) return false;
		$genome = new Genome($arr['DNA']);
		$genome->setID($arr['ID']);
		$genome->setParents($arr['parent1'], $arr['parent2']);
		$genome->setGeneration($arr['generation']);
		if (!is_null($arr['validity'])) $genome->setValidity($arr['validity']);
		if (!is_null($arr['fitness'])) $genome->setFitness($arr['fitness']);
		return $genome;
	}

	function getGenome($id) {
		$genome = $this->q("SELECT * FROM `genomes` WHERE `ID` = ".sprintf("%d", $id)." LIMIT 1");
		if (empty($genome)) return false;
		else {
			return $this->newGenome($genome[0]);
		}
	}

	// select a random genome from the latest generation
	function getRandomGenomeID() {
		$row = $this->q("SELECT * FROM `genomes` WHERE `generation` = (SELECT MAX(`generation`) FROM `genomes`) ORDER BY RAND() LIMIT 1");
		return $row[0]['ID'];
	}

	// select genomes from current generation in sequence
	function getNextGenomeID() {
		$row = $this->q("SELECT `ID` FROM `genomes` WHERE `ID` > (SELECT `genome` FROM `analytics` ORDER BY `ID` DESC LIMIT 1) AND `generation` = (SELECT MAX(`generation`) FROM `genomes`) LIMIT 1");
		if (empty($row)) $row = $this->q("SELECT `ID` FROM `genomes` WHERE `generation` = (SELECT MAX(`generation`) FROM `genomes`) LIMIT 1");
		return $row[0]['ID'];
	}

	function saveGenome($genome) {
		if (!($genome instanceof Genome)) return;
		$values = $genome->toDatabase();
		$q = "REPLACE INTO `genomes` SET ";
		foreach($values as $key => $val) $q .= "`$key`=$val,";
		return $this->q(substr($q, 0, -1));
	}

	// must pass an array in which arr[0] is the element to prep
	private function prepElement($rows, $closing = false) {
		$element = false;
		if (!empty($rows)) $element = $rows[0];
		if (!is_array($element) || !isset($element['ID'], $element['text'], $element['type'])) return array('ID' => 0, 'text' => '', 'type' => 'val');// return empty element if invalid input
		else {
			$element['closing'] = (boolean)$closing;
			if (strpos($element['text'], '{hexcolor}') !== false) $element['replacement'] = str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT);// random hex from 000000 to FFFFFF
			else if (strpos($element['text'], '{int}') !== false) $element['replacement'] = rand(0, 1000);
			return $element;
		}
	}

	function getElement($id) {
		$rows = $this->q("SELECT * FROM `elements` WHERE `ID` = ".sprintf("%d", abs($id))." LIMIT 1");
		return $this->prepElement($rows, $id < 0);
	}

	function getRandomElement() {
		$element = $this->q("SELECT * FROM `elements` ORDER BY RAND() LIMIT 1");
		return $this->prepElement($element, rand(0, 1));
	}

	function getHTMLTag() {
		$rows = $this->q("SELECT * FROM `elements` WHERE `text` = 'html' AND `type` = 'tag' LIMIT 1");
		return $this->prepElement($rows);
	}

	// inserts analytics row into table. $data should be array
	function saveAnalytics($data = array()) {
		if (!isset($_SESSION['user'], $_SESSION['genome'], $_SESSION['page']) || !is_array($data)) return false;
		return $this->q("INSERT INTO `analytics` SET `time` = ".time().", `user` = '{$_SESSION['user']}', `genome` = {$_SESSION['genome']}, `page` = (SELECT `ID` FROM `pages` WHERE `name` = '".mysql_real_escape_string($_SESSION['page'])."'), `data` = '".mysql_real_escape_string(json_encode($data))."'");
	}

	function getPageContent($name) {
		$row = $this->q("SELECT * FROM `pages` WHERE `name` = '".mysql_real_escape_string($name)."'");
		if (!$row) return '';
		return $row[0]['content'];
	}

	function getCurrentGenerationGenomes() {
		$rows = $this->q("SELECT * FROM `genomes` WHERE `generation` = (SELECT MAX(`generation`) FROM `genomes`)");
		$return = array();
		foreach($rows as $row) $return[$row['ID']] = $this->newGenome($row);
		return $return;
	}

	function countGenomeViews($generation = false) {
		$rows = $this->q("SELECT `genome`, COUNT(*) AS `views` FROM (SELECT `user`, `genome` FROM `analytics` WHERE `genome` IN (SELECT `ID` FROM `genomes` WHERE `generation` = ".($generation === false ? "(SELECT MAX(`generation`) FROM `genomes`)" : $generation).") GROUP BY `user`) `t` GROUP BY `genome`");
		$views = array();
		foreach($rows as $row) $views[$row['genome']] = $row['views'];
		return $views;
	}

	function getPages() {
		$rows = $this->q("SELECT * FROM `pages`");
		$pages = array();
		foreach($rows as $row) $pages[$row['ID']] = $row;
		return $pages;
	}
}
?>