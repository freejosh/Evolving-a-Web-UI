<?php
require_once 'common.php';

$db = new Database();
if (!isset($_POST['data'])) exit();

$analytics = array();
parse_str($_POST['data'], $analytics);

echo $db->saveAnalytics($analytics);
?>