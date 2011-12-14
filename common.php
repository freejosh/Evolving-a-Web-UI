<?php
ini_set('display_errors', 0);
if ($_SERVER['SERVER_NAME'] == 'evolve.joshfreeman.ca') ini_set('include_path', '/home/jfreem/php'.PATH_SEPARATOR.ini_get('include_path'));

define("INCLUDES_DIR", $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR);
define("PHP_INCLUDES_DIR", INCLUDES_DIR.'php'.DIRECTORY_SEPARATOR);
define("PAGES_INCLUDES_DIR", INCLUDES_DIR.'pages'.DIRECTORY_SEPARATOR);
define("CACHE_DIR", $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR);
define("RECOMBINING_FLAG", CACHE_DIR.'RECOMBINING_FLAG');
define("ABOUT_PAGE_CACHE", CACHE_DIR.'about-cache.html');
define("GENOME_PAGE", PAGES_INCLUDES_DIR.'genome.php');
define("ABOUT_PAGE", PAGES_INCLUDES_DIR.'about.php');
define("NEW_GENERATION_PAGE", PAGES_INCLUDES_DIR.'new-generation.html');
define("NOT_FOUND_PAGE", PAGES_INCLUDES_DIR.'404.html');

if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755);
require_once PHP_INCLUDES_DIR.'Database.php';
session_start();

function newSession($genomeID) {
	clearSession();
	$_SESSION['genome'] = $genomeID;
	$_SESSION['user'] = md5(time() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . rand(0, 1000));
}

function clearSession() {
	session_destroy();
	session_start();
	session_regenerate_id();
}
?>