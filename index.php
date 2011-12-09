<?php
require_once 'common.php';

$page = isset($_GET['page']) ? preg_replace('/[^a-z0-9-]/i', '', $_GET['page']) : false;

if ($page == 'genome') {
	// check if new generation is being generated. if so print loading screen which rechecks every 5 seconds.
	if (file_exists(RECOMBINING_FLAG)) {
		include NEW_GENERATION_PAGE;
		exit();
	}

	$genomeID = preg_replace('/[^0-9]/', '', @$_GET['genome']);
	// don't allow users to go directly to another /genome/ url
	// restart if no ID is set, or doesn't match session ID
	if (empty($genomeID) || !isset($_SESSION['genome']) || $genomeID != $_SESSION['genome']) newSession($db->getNextGenomeID());
	$genomeID = preg_replace('/[^0-9]/', '', $_SESSION['genome']);

	include GENOME_PAGE;

}  else if ($page == 'view') {// render a genome without frame
	$genomeID = preg_replace('/[^0-9]/', '', @$_GET['genome']);
	if (empty($genomeID)) {
		header('HTTP/1.1 404 Not Found');
		echo 'Not Found';
		exit();
	}
	$_SESSION['page'] = isset($_GET['content']) ? preg_replace('/[^a-z0-9.-]/i', '', $_GET['content']) : 'about.html';
	$genomePageCache = CACHE_DIR.md5($genomeID.$_SESSION['page']).'.html';
	$db->saveAnalytics(array('type' => 'load'));
	if (!file_exists($genomePageCache)) {
		$genome = $db->getGenome($genomeID);
		$genome->setURLPrefix("/view/$genomeID/");
		$genome->setPageContent($db->getPageContent($_SESSION['page']));
		file_put_contents($genomePageCache, $genome->getHTML());
	}
	include_once $genomePageCache;

} else if ($page == 'about') {
	clearSession();
	if (!file_exists(RECOMBINING_FLAG) && (!file_exists(ABOUT_PAGE_CACHE))) {
		ob_start();
		include ABOUT_PAGE;
		$aboutpage .= ob_get_clean();
		file_put_contents(ABOUT_PAGE_CACHE, $aboutpage);
	}
	include_once ABOUT_PAGE_CACHE;

} else newSession($db->getNextGenomeID());// any other url redirects to genome

function newSession($genomeID) {
	clearSession();
	$_SESSION['genome'] = $genomeID;
	$_SESSION['user'] = md5(time() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . rand(0, 1000));
	header('HTTP/1.1 302 Found');
	header('Location: http://'.$_SERVER['SERVER_NAME'].'/genome/'.$genomeID);
	exit();
}

function clearSession() {
	session_destroy();
	session_start();
	session_regenerate_id();
}
?>