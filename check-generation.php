<?php
// this page should be run every hour to recombine genomes if necessary
require_once 'common.php';
ignore_user_abort(true);
set_time_limit(0);
file_put_contents(RECOMBINING_FLAG, 1);

$db = new Database();
$currentGenomes = $db->getCurrentGenerationGenomes();// select genome IDs from current generation

$generationIDs = '';
foreach($currentGenomes as $gid => $g) $generationIDs .= $gid.',';// concatenate into string for query below
$generationIDs = substr($generationIDs, 0, -1);

$views = $db->countGenomeViews();// select number of views each genome has gotten

$newgeneration = true;
foreach($currentGenomes as $gid => $genome) {
	if (!isset($views[$gid]) || $views[$gid] < 5) {// don't recombine if any genome has < 5 views
		$newgeneration = false;
		break;
	}
}

if ($newgeneration) {
	// select analytics rows
	$rows = $db->q("SELECT * FROM `analytics` WHERE `genome` IN ($generationIDs) ORDER BY `genome`, `user`, `time` ASC");
	$analytics = array();
	foreach($rows as $row) {
		$row['data'] = json_decode($row['data'], true);
		$analytics[] = $row;
	}

	$fitness = $currentGenomes;// initialize fitness keys with keys of genomes
	foreach($fitness as &$f) $f = 0;// set each value to 0

	$allPages = $db->getPages();

	$timeStarted = 0;
	$pagesVisited = array();
	foreach($analytics as $i => $row) {
		$f = 0;// amount to add to fitness for this row

		if ($timeStarted == 0) $timeStarted = $row['time'];
		if (!isset($analytics[$i - 1]) || $analytics[$i - 1]['page'] != $row['page']) $scrolledAll = false;

		if ($row['data']['type'] == 'scrollAll') {
			$f += 10;// +10 for seeing all the content
			$scrolledAll = true;
		}

		$pagesVisited[$row['page']] = true;// record which page was visited

		if (!isset($analytics[$i + 1]) || ($analytics[$i + 1]['page'] == 1 && $analytics[$i + 1]['data']['type'] == 'load')) {// check if this is the last trial
			if (!$scrolledAll && isset($row['data']['scroll'])) $f += $row['data']['scroll'] * 10;// Points depend on amount of content seen. Max of 10 for 100%
			$f += ($row['time'] - $timeStarted) / 10;// Points depend on time spent on site. 1 point per 10 seconds
			$f += count($pagesVisited) / count($allPages) * 10;// Points depend on number of pages visited, as percent of total, scaled to 10.
			$timeStarted = 0;
			$pagesVisited = array();
		}

		if ($row['data']['type'] == 'click') {
			if ($row['data']['target'] == 'A') {
				$f += 10;// +10 for clicking on an actual link
				if (isset($analytics[$i + 1]) && $analytics[$i + 1]['data']['type'] == 'load') $f += 10;// +10 for that link going to a new page
			} else $f -= 10;// -10 for clicking somewhere other than a link
		}

		$fitness[$row['genome']] += $f;
	}

	$maxViews = 0;
	foreach($views as $v) $maxViews = max($maxViews, $v);// scale fitnesses based on number of views
	foreach($currentGenomes as $gid => $g) {// set and save fitness for each genome
		$g->setFitness(intval($fitness[$gid] * $views[$gid] / $maxViews));
		$db->saveGenome($g);
	}

	// get top 2 genomes
	arsort($fitness, SORT_NUMERIC);
	$parent1 = key($fitness);
	$parent1 = $currentGenomes[$parent1];
	next($fitness);
	$parent2 = key($fitness);
	$parent2 = $currentGenomes[$parent2];
	// make 50 children - 25 using each half of parent
	for($i = 0; $i < 10; $i++) $db->saveGenome($parent1->mateWith($parent2));
	for($i = 0; $i < 10; $i++) $db->saveGenome($parent2->mateWith($parent1));
}
if (file_exists(ABOUT_PAGE_CACHE)) unlink(ABOUT_PAGE_CACHE);
unlink(RECOMBINING_FLAG);
?>