<?php

// Functions
include 'functions.php';

?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link href="dist/css/main.css" rel="stylesheet">
	<link rel="shortcut icon" href="//www.redditstatic.com/favicon.ico" type="image/x-icon">
</head>
<body>
	<div id="wrap">
		<div class="jumbotron">
			<div class="container">
				<h1 class="page-title">/r/<span class="subreddit"><?php echo $subreddit; ?></span><a href="//<?php echo $_SERVER['HTTP_HOST'] . str_replace("html", "rss", $_SERVER["REQUEST_URI"]); echo !isset($_GET["view"]) ? "&view=rss" : ""; ?>" class="rss-badge badge badge-secondary small" data-toggle="tooltip" data-placement="top" title="Subscribe" target="_blank"><span class="icon-rss"></span> RSS</a></h1>
			</div>
		</div>
		<div class="container">

			<form>
				<div class="row">
					<div class="col-12 col-sm form-group">
						<label for="subreddit">Subreddit</label>
						<input class="form-control" type="text" name="subreddit" value="<?php echo $subreddit; ?>">
					</div>
					<div class="col-12 col-sm form-group">
						<label for="score">Filter Type</label>
						<select class="form-control" name="filterType">
							<option value="score">Score</option>
							<option value="threshold">Threshold</option>
							<option value="averagePostsPerDay">Posts Per Day</option>
						</select>
					</div>
					<div id="score-group" class="col-12 col-sm d-none form-group filter-group">
						<label for="score">Score <span class="icon-info-circle" data-toggle="tooltip" data-placement="top" title="Posts below this score will be filtered out."></span></label>
						<input class="form-control" type="number" name="score" <?php if(isset($_GET["score"]) && !isset($_GET["threshold"])) { ?>value="<?php echo $_GET["score"]; ?>"<?php } ?> min="0" pattern="[0-9]*" inputmode="numeric">
					</div>
					<div id="threshold-group" class="col-12 col-sm d-none form-group filter-group">
						<label for="threshold">Percentage <span class="icon-info-circle" data-toggle="tooltip" data-placement="top" title="Posts below this percentage of the subreddit's monthly top posts' average score will be filtered out."></span></label>
						<input class="form-control" type="number" name="threshold" <?php if(isset($_GET["threshold"])) { ?>value="<?php echo $_GET["threshold"]; ?>"<?php } ?> min="0" pattern="[0-9]*" inputmode="numeric">
					</div>
					<div id="averagePostsPerDay-group" class="col-12 col-sm d-none form-group filter-group">
						<label for="averagePostsPerDay">Posts Per Day <span class="icon-info-circle" data-toggle="tooltip" data-placement="top" title="The RSS feed will output roughly this many posts per day."></span></label>
						<input class="form-control" type="number" name="averagePostsPerDay" <?php if(isset($_GET["averagePostsPerDay"])) { ?>value="<?php echo $_GET["averagePostsPerDay"]; ?>"<?php } ?> min="0" pattern="[0-9]*" inputmode="numeric">
					</div>
					<div class="col-12 col-sm form-group">
						<button type="submit" class="btn btn-primary btn-block">Submit</button>
					</div>
				</div>
			</form>

			<div id="post-list" data-default-subreddit="<?php echo DEFAULT_SUBREDDIT; ?>"></div>

		</div>
	</div>


	<footer class="bd-footer text-muted">
		<div class="container">
			<div class="row">
				<div class="col-lg-2"></div>
				<div class="col-lg-8 text-center">
					<p>Reddit Top RSS - <a href="https://github.com/johnwarne/reddit-top-rss/" target="_blank">https://github.com/johnwarne/reddit-top-rss/</a></p>
					<?php if(CACHE_REDDIT_JSON == true || CACHE_MERCURY_CONTENT == true || CACHE_RSS_FEEDS == true) {
						$url = "//" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
						$query = parse_url($_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], PHP_URL_QUERY);
						if ($query) {
							$url .= "&clearCache";
						} else {
							$url .= "?clearCache";
						}
					?>
						<p class="clear-cache"><button class="btn btn-link">Clear cached results (<span><?php echo sizeFormat(directorySize("cache")); ?></span>)</button></p>
					<?php } ?>
				</div>
			</div>
		</div>
	</footer>

	<script src="dist/js/scripts.min.js"></script>

</body>
</html>