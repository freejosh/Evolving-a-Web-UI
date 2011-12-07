<?php
require_once 'common.php';

$page = isset($_GET['page']) ? preg_replace('/[^a-z0-9-]/i', '', $_GET['page']) : false;

if ($page == 'genome') {
	// check if new generation is being generated. print loading screen if so and recheck every 5 seconds.
	if (file_exists(RECOMBINING_FLAG)) {
		print '
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="refresh" content="5">
		<title>Generating new generation</title>
	</head>
	<body>
		Generating new generation, please wait...<img src="/images/loading.gif" alt="loading">
	</body>
</html>';
		exit();
	}

	$genomeID = preg_replace('/[^0-9]/', '', @$_GET['genome']);
	// don't allow users to go directly to another /genome/ url
	// restart if no ID is set, or doesn't match session ID
	if (empty($genomeID) || !isset($_SESSION['genome']) || $genomeID != $_SESSION['genome']) newSession($db->getNextGenomeID());
	$genomeID = preg_replace('/[^0-9]/', '', $_SESSION['genome']);

	// render /view/ page inside iframe; surrounding framework monitors it
	print '
<!doctype html>
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
		<iframe id="render" src="/view/'.$genomeID.'"></iframe>
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
</html>';

}  else if ($page == 'view') {// render a genome without frame
	$genomeID = preg_replace('/[^0-9]/', '', @$_GET['genome']);
	if (empty($genomeID)) {
		header('HTTP/1.1 404 Not Found');
		print 'Not Found';
		exit();
	}
	$_SESSION['page'] = isset($_GET['content']) ? preg_replace('/[^a-z0-9.-]/i', '', $_GET['content']) : 'about.html';
	$genome = $db->getGenome($genomeID);
	$genome->setURLPrefix("/view/$genomeID/");
	$genome->setPageContent($db->getPageContent($_SESSION['page']));
	$db->saveAnalytics(array('type' => 'load'));
	print $genome->getHTML();
} else if ($page == 'about') {
	// destroy session
	session_destroy();
  session_start();
  session_regenerate_id();
  if (!file_exists(RECOMBINING_FLAG) && (!file_exists(ABOUT_PAGE_CACHE))) {
	  $aboutpage = '<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Evolving a Web UI</title>
		<link href="/includes/css/about.css" rel="stylesheet" media="screen">
		<script src="/includes/js/jquery-1.7.min.js" type="text/javascript"></script>
		<script src="/includes/js/about.js" type="text/javascript"></script>
	</head>
	<body>
		<a id="restart" href="/">Start again</a>
		<h1>Evolving a Web User Interface - Project for FACS4930 at York University</h1>
		<h2>Background</h2>
		<p>Much research has been done on user interfaces, particularly to do with the web. Since the medium allows very easy and fast manipulation of interfaces, website designs have gone through many trends in a short amount of time. Throughout these iterations usability has become a growing concern as it transcends the needs of the consumers - needing information clearly and actions easily recognized - the designers - needing their design to not only look good but fulfill the consumer\'s needs - and the advertisers - needing their ads to be just as usable as the website and desirable to click on. <a href="http://www.useit.com">Jakob Nielsen</a> is professed to be the <a href="http://www.nytimes.com/library/tech/98/07/cyber/articles/13usability.html">"guru of web page usability"</a> and continually writes articles about current issues in web and technology usability. Since design is qualitative by nature it is no surprise that little quantitative research is available. However a paper exploring interaction design of a system using a fixed number of buttons (Hardman, Colombi, Jacques, Hill, &amp; Miller, 2009) uses genetic algorithms and quantitative evaluation to produce optimal designs.</p>
		<h2>Concept</h2>
		<p>While Nielsen\'s, and similar authors\', rules and ideas come from years of research and have proven to yield positive results, they were likely obtained using what could be described as a top-down approach. This approach defines what users are trying to accomplish with the interface and observes how it fails, leading to the design of individual components. I wish to explore user interface design in a bottom-up fashion, allowing users to interact with a system and then allowing the system to change over many iterations to increase its usability, as determined by measuring a number of factors. These changes will be governed by a genetic algorithm, which combines and recombines individual components that make up a web page.</p>
		<h2>Implementation</h2>
		<p>I will be creating my own framework website which will display the generated HTML output and track users\' interactions. This will be made possible using a combination of MySQL, PHP, and jQuery for handling the database, server-side, and client-side programming, respectively.</p>
		<p>Predetermined HTML tags, attributes, and values will be stored in the database, creating the pool of possible genes. Tags will include those which are needed for standard pages, such as <span class="code">&lt;p&gt;</span>, <span class="code">&lt;div&gt;</span>, <span class="code">&lt;img&gt;</span>, etc. Attributes will include some which are common to multiple elements, such as <span class="code">style</span>, but also some which are specific to certain elements, such as <span class="code">src</span> and <span class="code">href</span>. Available values will include URLs to different pages, content for those pages, images, and a limited set of CSS formatting styles.</p>
		<p>Each genome will be stored in the database as a string, each character in which will correspond to an available tag/attribute/value. To construct the HTML from the string, each character will essentially be replaced with its corresponding element, and supporting syntax (characters such as <span class="code">&lt;</span>, <span class="code">&gt;</span>, and <span class="code">"</span>) will be automatically inserted.</p>
		<p>To determine the validity of each genome the resulting HTML will be validated using the <a href="http://validator.w3.org/docs/api.html">W3C Validator API</a>. The number of syntax errors will be saved as a percent of total genome length. Since syntax errors are a negative trait this number is always stored as a negative value, the best case contributing 0 points. The genome will also be rendered and presented to each user of the website in succession. The framework will track the user\'s interactions, such as time spent viewing each page, where they try to click vs. where they should click and how far into the page hierarchy they are able to traverse. The scores for each of these interactions will be added to the validity to make up the overall fitness. Upon receiving a certain number of users each, the next generation will be generated by recombining the highest-scoring genomes of the current generation, along with randomly mutating a random number of individual genes. The previous generation will be discarded from the population and the sequence will begin anew.</p>
		<p>I believe that users will be more engaged if they have the opportunity to read real content, as opposed to placeholder text such as <a href="http://www.lipsum.com">Lorem Ipsum</a>. The content for these pages will therefore be a subset of the text on the <a href="http://www.yorku.ca/web/about_yorku/">About York University</a> page. Each heading from the York U page will define a separate page, so that the content presented will be part of a cohesive topic and the user will clearly know which text is supposed to be content and which is a result of malformed HTML.</p>
		<h2>Works Cited</h2>
		<p>Hardman, N., Colombi, J., Jacques, D., Hill, R., &amp; Miller, J. (2009, October). Application of a seeded hybrid genetic algorithm for user interface design. IEEE International Conference on Systems, Man and Cybernetics, 462-467.</p>
		<h2>Image Credits</h2>
		<ul>
			<li>Favicon: Fugue Icons by <a href="http://p.yusukekamiyamane.com/">Yusuke Kamiyamane</a></li>
			<li>UI icons: <a href="http://thenounproject.com/">The Noun Project</a></li>
		</ul>
		<h2>Stats (updated hourly)</h2>
		';
			$stats = $db->q("SELECT * FROM `genomes` ORDER BY `generation` DESC, `parent1`, `parent2`");
			$curGeneration = $db->q("SELECT MAX(`generation`) AS `max` FROM `genomes`");
			$curGeneration = $curGeneration[0]['max'];
			foreach($stats as $i => $genome) {
				if (!isset($stats[$i - 1]) || $stats[$i - 1]['generation'] != $genome['generation']) {
					if (isset($stats[$i - 1])) $aboutpage .= '
					</tbody>
				</table>
			</div>
		</div>';
					$aboutpage .= "<div class=\"stats\" id=\"generation-{$genome['generation']}\"><h3>Generation {$genome['generation']}";
					if ($genome['generation'] == $curGeneration) $aboutpage .= ' (Current)';
					$views = $db->countGenomeViews($genome['generation']);
					$aboutpage .= '</h3>
			<div class="table">
				<table>
					<thead>
						<tr>
							<th>Genome</th>
							<th>Parent 1</th>
							<th>Parent 2</th>
							<th class="num">Validity</th>
							<th class="num"># Views</th>
							<th class="num">Usability</th>
							<th class="num">Fitness</th>
						</tr>
					</thead>
					<tbody>';
				}
				$aboutpage .= '
						<tr class="'.(($i + 1) % 2 == 0 ? 'even' : 'odd').'">
							<td><a href="/view/'.$genome['ID'].'">Genome '.$genome['ID'].'</a></td>
							<td>'.($genome['parent1'] == 0 ? 'N/A' : '<a href="/view/'.$genome['parent1'].'">'.$genome['parent1'].'</a>').'</td>
							<td>'.($genome['parent2'] == 0 ? 'N/A' : '<a href="/view/'.$genome['parent2'].'">'.$genome['parent2'].'</a>').'</td>
							<td class="num">'.$genome['validity'].'</td>
							<td class="num">'.(isset($views[$genome['ID']]) ? $views[$genome['ID']] : 0).'</td>
							<td class="num">'.(is_null($genome['fitness']) ? 'N/A' : $genome['fitness'] - $genome['validity']).'</td>
							<td class="num">'.(is_null($genome['fitness']) ? 'N/A' : $genome['fitness']).'</td>
						</tr>';
			}
			$aboutpage .= '
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>';
		file_put_contents(ABOUT_PAGE_CACHE, $aboutpage);
	}
	include_once ABOUT_PAGE_CACHE;

} else newSession($db->getNextGenomeID());// any other url redirects to genome

function newSession($genomeID) {
	session_destroy();
  session_start();
  session_regenerate_id();
  $_SESSION['genome'] = $genomeID;
  $_SESSION['user'] = md5(time() . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . rand(0, 1000));
	header('HTTP/1.1 302 Found');
	header('Location: http://'.$_SERVER['SERVER_NAME'].'/genome/'.$genomeID);
	exit();
}
?>