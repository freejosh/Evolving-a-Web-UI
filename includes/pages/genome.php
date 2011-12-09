<?php if (!isset($genomeID)) throw new Exception("No genome ID specified"); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Evolving a Web UI</title>
		<link href="/includes/css/framework.css" rel="stylesheet" media="screen">
		<script src="/includes/js/jquery-1.7.min.js" type="text/javascript"></script>
		<script src="/includes/js/analytics.js" type="text/javascript"></script>
		<script src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4ed5206f21a08ce4" type="text/javascript"></script>
		<script type="text/javascript">var addthis_config = { data_track_clickback: false }, addthis_share = { url: "http://evolve.joshfreeman.ca/" }</script>
	</head>
	<body>
		<iframe id="render" src="/view/<?php echo $genomeID; ?>"></iframe>
		<div id="toolbar">
			<a id="about" href="/about">This site is evolving. Click here to learn more</a>
			<a id="restart" href="/">New Genome</a>
			<div class="addthis_toolbox addthis_default_style">
				<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
				<a class="addthis_button_tweet" tw:via="freejosh" tw:counturl="http://evolve.joshfreeman.ca/"></a>
				<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
			</div>
		</div>
	</body>
</html>