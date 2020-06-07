<?php

// Functions
include 'functions.php';

$filterType = 'score';
$includeComments = false;
// Get requested subreddit
// If none is specified, set a default
if(isset($_GET["subreddit"])) {
	$subreddit = strip_tags(trim($_GET["subreddit"]));
} else {
	$subreddit = DEFAULT_SUBREDDIT;
}
if(isset($_GET["score"])) {
	$score = strip_tags(trim($_GET["score"]));
} else {
	$score = 1000;
}
if(isset($_GET["threshold"])) {
	$percentage = strip_tags(trim($_GET["threshold"]));
	$filterType = 'percentage';
} else {
	$percentage = 100;
}
if(isset($_GET["averagePostsPerDay"])) {
	$postsPerDay = strip_tags(trim($_GET["averagePostsPerDay"]));
	$filterType = 'postsPerDay';
} else {
	$postsPerDay = 3;
}
if(isset($_GET["comments"])) {
	$comments = strip_tags(trim($_GET["comments"]));
	$includeComments = true;
} else {
	$comments = 5;
}

?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link href="dist/css/styles.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;700&display=swap" rel="stylesheet">
	<link rel="shortcut icon" href="//www.redditstatic.com/favicon.ico" type="image/x-icon">
	<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
</head>

<body class="bg-gray-300 font-family-karla text-gray-900">
	<div id="app" class="flex flex-col md:flex-row">

		<aside class="flex flex-col justify-between relative bg-sidebar bg-gray-900 md:h-screen w-full md:w-64 flex-shrink-0">
			<div class="w-full">
				<form class="rounded px-8 pt-6 pb-8 mb-4" ref="form">
					<h2 class="text-gray-100 text-2xl mb-4 lg:text-3xl">Filter posts</h2>
					<div class="mb-6">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="subreddit">
							Subreddit
						</label>
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="subreddit" type="text" placeholder="subreddit" v-model="subreddit">
					</div>
					<div class="mb-6 hidden">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="subreddit">
							Filter Type
						</label>
						<div class="relative">
							<select class="block appearance-none w-full shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline cursor-pointer">
								<option>Score</option>
								<option>Threshold</option>
								<option>Posts Per Day</option>
							</select>
							<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
								<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
							</div>
						</div>
					</div>

					<div class="radio-buttons flex flex-col mb-6 text-gray-500 text-sm">
						<label class="block text-gray-500 text-sm font-bold mb-2">
							Filter Type
						</label>
						<label class="cursor-pointer">
							<input type="radio" class="form-radio" name="accountType" value="score" v-model="filterType">
							<span class="ml-2">Score</span>
						</label>
						<label class="cursor-pointer">
							<input type="radio" class="form-radio" name="accountType" value="percentage" v-model="filterType">
							<span class="ml-2">Threshold</span>
						</label>
						<label class="cursor-pointer">
							<input type="radio" class="form-radio" name="accountType" value="postsPerDay" v-model="filterType">
							<span class="ml-2">Posts Per Day</span>
						</label>
					</div>

					<div class="mb-6" v-if="filterType === 'score'">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="score">
							Score
						</label>
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="score" type="number" v-model="score" on-keyup="formSubmit | debounce 500" min="1">
					</div>
					<div class="mb-6" v-if="filterType === 'percentage'">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="percentage">
							Threshold
						</label>
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="percentage" type="number" v-model="percentage" min="1">
					</div>
					<div class="mb-6" v-if="filterType === 'postsPerDay'">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="postsPerDay">
							Posts Per Day
						</label>
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="postsPerDay" type="number" v-model="postsPerDay" min="1">
					</div>

					<div class="radio-buttons flex flex-col mb-2 text-gray-500 text-sm">
						<label class="cursor-pointer flex items-start">
							<input type="checkbox" class="form-radio mt-1" name="includeComments" value="includeComments" v-model="includeComments">
							<span class="ml-2">Include top comments in RSS feed</span>
						</label>
					</div>

					<div class="mb-4" v-if="includeComments">
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="includedComments" type="number" v-model="includedComments" on-keyup="formSubmit | debounce 500" ref="includedComments" min="1">
					</div>

					<button id="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 mt-8 w-full rounded focus:outline-none focus:shadow-outline transition-all transition duration-100 opacity-50 cursor-not-allowed" type="button" v-on:click="formSubmit">
						Submit
					</button>
				</form>
			</div>
			<footer class="border-t border-gray-700 px-8 pt-6 pb-8">
				<p class="text-center text-gray-500 text-xs">
				Reddit Top RSS
				</p>
				<p class="text-center text-gray-500 text-xs">
					<a href="https://github.com/johnwarne/reddit-top-rss/" target="_blank" class="underline transition-colors transition duration-100 hover:text-blue-700">GitHub</a>
				</p>
				<?php if(CACHE_REDDIT_JSON == true || CACHE_MERCURY_CONTENT == true || CACHE_RSS_FEEDS == true) {
					$url = "//" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
					$query = parse_url($_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], PHP_URL_QUERY);
					if ($query) {
						$url .= "&clearCache";
					} else {
						$url .= "?clearCache";
					}
				?>
					<p class="clear-cache text-center">
						<button class="bg-transparent hover:bg-blue-500 text-gray-500 hover:text-white mt-4 py-2 px-4 border border-gray-500 hover:border-transparent rounded text-xs transition-all transition duration-100" v-on:click="clearCache">Clear cached results<br>(<span>{{ cacheSize }}</span>)</button>
					</p>
				<?php } ?>
			</footer>
		</aside>

		<div class="w-full h-screen md:flex md:flex-col">

			<div class="banner bg-gray-600 text-white shadow-lg lg:shadow-2xl md:flex-shrink-0 z-20">
				<div class="inner mx-auto px-8 pt-6 pb-8 flex justify-between items-end">
					<h1 class="page-title flex-1 leading-none">
						<a :href="'https://www.reddit.com/r/' + subreddit + '/'" target="_blank">
						/r/<span class="subreddit">{{subreddit}}</span></a>
					</h1>
					<p class="flex-1 text-center">Hot posts at or above a score of <strong>{{ computedScore }}</strong><span v-if="filterType === 'percentage'"> ({{ percentage }}% of monthly top posts' average score)</span><span v-if="filterType === 'postsPerDay'"> (giving a rough average of <strong>{{ postsPerDay }}</strong> posts per day)</span></p>
					<span class="flex-1 text-right">
						<button class="bg-transparent hover:bg-blue-500 text-white font-semibold py-2 px-4 border border-white hover:border-blue-500 rounded transition-all transition duration-100" :title="generatedRssUrl" target="_blank" v-on:click="myFunction" v-on:mouseout="outFunc">
							<input type="text" :value="generatedRssUrl" id="myInput" class="" style="height: 0; width: 0; opacity: 0;"><div class="tooltip">
								<span class="tooltiptext" id="myTooltip">Copy to clipboard</span>
							</div><svg style="position: absolute; width: 0; height: 0; overflow: hidden" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
								<defs>
									<symbol id="icon-feed" viewBox="0 0 22 28">
										<path d="M6 21c0 1.656-1.344 3-3 3s-3-1.344-3-3 1.344-3 3-3 3 1.344 3 3zM14 22.922c0.016 0.281-0.078 0.547-0.266 0.75-0.187 0.219-0.453 0.328-0.734 0.328h-2.109c-0.516 0-0.938-0.391-0.984-0.906-0.453-4.766-4.234-8.547-9-9-0.516-0.047-0.906-0.469-0.906-0.984v-2.109c0-0.281 0.109-0.547 0.328-0.734 0.172-0.172 0.422-0.266 0.672-0.266h0.078c3.328 0.266 6.469 1.719 8.828 4.094 2.375 2.359 3.828 5.5 4.094 8.828zM22 22.953c0.016 0.266-0.078 0.531-0.281 0.734-0.187 0.203-0.438 0.313-0.719 0.313h-2.234c-0.531 0-0.969-0.406-1-0.938-0.516-9.078-7.75-16.312-16.828-16.844-0.531-0.031-0.938-0.469-0.938-0.984v-2.234c0-0.281 0.109-0.531 0.313-0.719 0.187-0.187 0.438-0.281 0.688-0.281h0.047c5.469 0.281 10.609 2.578 14.484 6.469 3.891 3.875 6.188 9.016 6.469 14.484z"></path>
									</symbol>
								</defs>
							</svg><svg class="icon icon-feed"><use xlink:href="#icon-feed"></use></svg>
							<span class="text">Copy RSS feed link</span>
						</button>
					</span>
				</div>
			</div>

			<div class="inner md:overflow-y-scroll w-full px-4 pt-10 pb-8 z-10 sm:px-6 md:px-10 lg:px-20">

				<div id="post-list" class="max-w-screen-md mx-auto border border-gray-400 rounded bg-white flex flex-col shadow-xl loading mb-8 lg:mb-12">
					<a :href="post.url" class="media" target="_blank" v-for="post in posts">
						<div class="thumbnail" :class="{'default-image': !post.imgSrc}">
							<img v-if="post.imgSrc" :src="post.imgSrc">
							<img v-else src="https://www.redditstatic.com/mweb2x/favicon/76x76.png">
						</div>
						<div class="media-body">
							<h5>
								<span class="badge">{{ post.score }}</span>
								<span class="title">{{ post.title }}</span>
							</h5>
							<time :datetime="post.dateTime2822">{{ post.dateTime }}</time>
						</div>
					</a>
				</div>

			</div>

		</div>


	</div>

	<script>
		var subreddit       = '<?php echo $subreddit; ?>';
		var filterType      = '<?php echo $filterType; ?>';
		var score           = '<?php echo $score; ?>';
		var percentage      = '<?php echo $percentage; ?>';
		var postsPerDay     = '<?php echo $postsPerDay; ?>';
		var includeComments = '<?php echo $includeComments; ?>';
		var comments        = '<?php echo $comments; ?>';
	</script>
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
	<script src="js/vue-scripts.js"></script>

</body>
</html>