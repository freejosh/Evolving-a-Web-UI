<?php
require_once PHP_INCLUDES_DIR.'Database.php';
require_once 'Services/W3C/HTMLValidator.php';

class Genome {

	private $id = 0, $genome, $pageContent, $urlPrefix, $parent1 = 0, $parent2 = 0, $html = '', $generation = 0, $validity = "NULL", $fitness = "NULL";

	function __construct($arg) {
		$db = new Database(false);
		if (empty($arg)) {
			// generate new genome
			// always use <html> as first element, run loop until </html> is selected
			$element = $db->getHTMLTag();
			$htmlTag = $element;
			while ($element['ID'] != $htmlTag['ID'] || !$element['closing']) {
				$this->genome[] = $element;
				$element = $db->getRandomElement();
			}
			$this->genome[] = $element;
		} else if (is_string($arg)) {
			// parse genome string in form:
			// [-]{element ID}[:{replacement value}][,[-]{element ID}[:{replacement value}]]...
			$genes = explode(',', $arg);
			$this->genome = array();
			foreach($genes as $element) {
				$element = explode(':', $element);
				$row = $db->getElement($element[0]);
				if ($row) {
					if (isset($element[1])) $row['replacement'] = $element[1];
					$this->genome[] = $row;
				}
			}
		} else if (is_array($arg)) {
			// construct from genome array
			// check that each element has at least an ID, type, and text. if not empty genome is created.
			foreach($arg as $element) if (!isset($element['text'], $element['type'], $element['ID'])) return;
			$this->genome = $arg;
		}
	}

	function getID() {
		return $this->id;
	}

	function setID($int) {
		$this->id = intval($int);
	}

	function setPageContent($content) {
		if (!is_string($content)) return false;
		$this->pageContent = $content;
		$this->reRender(false);
	}

	function setURLPrefix($str) {
		if (!is_string($str)) return false;
		$this->urlPrefix = $str;
		$this->reRender(false);
	}

	function reRender($revalidate = true) {
		$this->html = "<!doctype html>\n";
		for ($i = 0; $i < count($this->genome); $i++) $this->generateHTML($this->genome, $i);
		if ($revalidate) $this->reValidate();
	}

	private function generateHTML($arr, &$index) {
		$element = $arr[$index];
		if (isset($element['replacement'])) $element['text'] = preg_replace('/{[^}]+}/', $element['replacement'], $element['text']);

		if ($element['type'] == 'tag') {
			$this->html .= '<';
			if (isset($element['closing']) && $element['closing']) $this->html .= '/'.$element['text'];
			else {
				$this->html .= $element['text'];
				while (isset($arr[$index + 1]) && $arr[$index + 1]['type'] == 'atr') $this->generateHTML($arr, ++$index);
			}
			$this->html .= '>';
			if ($element['closing']) $this->html .= "\n";
		} else if ($element['type'] == 'atr') {
			$this->html .= ' '.$element['text'].'="';
			if (isset($arr[$index + 1]) && ($arr[$index + 1]['type'] == 'url' || $arr[$index + 1]['type'] == 'val')) $this->generateHTML($arr, ++$index);
			else while (isset($arr[$index + 1]) && ($arr[$index + 1]['type'] == 'css')) $this->generateHTML($arr, ++$index);
			$this->html .= '"';
		} else if ($element['type'] == 'val') {
			$element['text'] = str_replace('{content}', $this->pageContent, $element['text']);
			$this->html .= $element['text'];
		} else if ($element['type'] == 'css') $this->html .= $element['text'].';';
		else if ($element['type'] == 'url') $this->html .= $this->urlPrefix.$element['text'];

		return $element['type'];
	}

	// merges $this with $mate, combining first half with last half as well as mutating randomly
	function mateWith($mate) {
		if (!($mate instanceof Genome)) return false;
		// merge genomes
		$parent1 = $this->getGenome();
		$parent2 = $mate->getGenome();
		$child = array_merge(array_slice($parent1, 0, $this->countGenes() / 2), array_slice($parent2, $mate->countGenes() / 2));
		$child = new Genome($child);
		$numGenes = $child->countGenes();
		$child->setParents($this->getID(), $mate->getID());
		$child->setGeneration(max($this->getGeneration(), $mate->getGeneration()) + 1);
		// change random gene to random element, random number of times
		$db = new Database(false);
		for ($i = 0; $i < rand(0, $numGenes) - 2; $i++) $child->setGene(rand(1, $numGenes - 2), $db->getRandomElement(), false);
		$child->reRender();
		return $child;
	}

	function setGene($i, $element, $rerender = true) {
		if (!is_array($element) || !isset($element['ID'], $element['type'], $element['text'])) return false;
		$this->genome[$i] = $element;
		if ($rerender) $this->reRender();
	}

	function getGenome() {
		return $this->genome;
	}

	function countGenes() {
		return count($this->genome);
	}

	function getParents() {
		return array($this->parent1, $this->parent2);
	}

	function setParents($parent1, $parent2) {
		$this->parent1 = intval($parent1);
		$this->parent2 = intval($parent2);
	}

	function setGeneration($int) {
		$this->generation = intval($int);
	}

	function getGeneration() {
		return $this->generation;
	}

	function getHTML() {
		if ($this->html == '') $this->reRender(false);
		return $this->html;
	}

	function reValidate() {
		$validator = new Services_W3C_HTMLValidator();
		$response = $validator->validateFragment($this->getHTML());
		$this->validity = intval(0 - count($response->errors) / $this->countGenes() * 100);// validity measured as a percent of errors over total elements
		return $this->getValidity();
	}

	function getValidity() {
		return $this->validity;
	}

	function setValidity($arg) {
		$this->validity = intval($arg);
	}

	function getFitness() {
		return $this->fitness;
	}

	function setFitness($arg) {
		$this->fitness = intval($arg);
	}

	function getUsability() {
		return $this->getFitness() - $this->getValidity();
	}

	// returns genome string
	function toString() {
		$str = "";
		foreach($this->genome as $element) {
			if (isset($element['closing']) && $element['closing']) $str .= '-';
			$str .= $element['ID'];
			if (isset($element['replacement'])) $str .= ':'.$element['replacement'];
			$str .= ',';
		}
		return substr($str, 0, -1);
	}

	// returns array that can be used directly in database to update genomes table
	function toDatabase() {
		$arr = array();
		$id = $this->getID();
		if ($id) $arr['ID'] = $id;
		$arr['DNA'] = "'".mysql_real_escape_string($this->toString())."'";
		$parents = $this->getParents();
		$arr['parent1'] = $parents[0];
		$arr['parent2'] = $parents[1];
		$arr['generation'] = $this->getGeneration();
		$arr['validity'] = $this->getValidity();
		$arr['fitness'] = $this->getFitness();
		return $arr;
	}
}
?>