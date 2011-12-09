<?php
ini_set('display_errors', 0);
if ($_SERVER['SERVER_NAME'] == 'evolve.joshfreeman.ca') ini_set('include_path', '/home/jfreem/php'.PATH_SEPARATOR.ini_get('include_path'));
define("INCLUDES_DIR", $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR);
define("PHP_INCLUDES_DIR", INCLUDES_DIR.'php'.DIRECTORY_SEPARATOR);
define("PAGES_INCLUDES_DIR", INCLUDES_DIR.'pages'.DIRECTORY_SEPARATOR);
define("CACHE_DIR", $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR);
if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755);
define("RECOMBINING_FLAG", CACHE_DIR.'RECOMBINING_FLAG');
define("ABOUT_PAGE_CACHE", CACHE_DIR.'about-cache.html');
define("GENOME_PAGE", PAGES_INCLUDES_DIR.'genome.php');
define("ABOUT_PAGE", PAGES_INCLUDES_DIR.'about.php');
define("NEW_GENERATION_PAGE", PAGES_INCLUDES_DIR.'new-generation.html');
require_once PHP_INCLUDES_DIR.'Database.php';
session_start();
$db = new Database();
?>