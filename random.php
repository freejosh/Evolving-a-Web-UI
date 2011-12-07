<?php
require_once 'common.php';

ignore_user_abort(true);
set_time_limit(0);

for ($i = 0; $i < 50; $i++) {
	$g = new Genome('');
	$g->reValidate();
	$db->saveGenome($g);
}

print 'done';
?>