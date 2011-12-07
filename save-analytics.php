<?php
require_once 'common.php';

if (!isset($_POST['data'])) exit();

$analytics = array();
parse_str($_POST['data'], $analytics);

print $db->saveAnalytics($analytics);
?>