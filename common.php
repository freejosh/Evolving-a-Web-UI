<?php
ini_set('display_errors', 0);
if ($_SERVER['SERVER_NAME'] == 'evolve.joshfreeman.ca') ini_set('include_path', '/home/jfreem/php'.PATH_SEPARATOR.ini_get('include_path'));
define("PHP_INCLUDES_DIR", $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR);
define("CACHE_DIR", $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR);
define("RECOMBINING_FLAG", CACHE_DIR.'RECOMBINING_FLAG');
define("ABOUT_PAGE_CACHE", CACHE_DIR.'about-cache.html');
require_once PHP_INCLUDES_DIR.'Database.php';
session_start();
$db = new Database();
?>