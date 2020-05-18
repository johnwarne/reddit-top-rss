<?php

// Functions
include 'functions.php';

?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link href="dist/css/styles.css" rel="stylesheet">
	<link rel="shortcut icon" href="//www.redditstatic.com/favicon.ico" type="image/x-icon">
	<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
</head>

<body class="bg-gray-300 font-family-karla text-gray-900">
	<div id="app" class="flex flex-col md:flex-row">

		<aside class="flex flex-col justify-between relative bg-sidebar bg-gray-800 md:h-screen w-full md:w-64 flex-shrink-0">
			<div class="w-full">
				<form class="rounded px-8 pt-6 pb-8 mb-4">
					<div class="mb-4">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="subreddit">
							Subreddit
						</label>
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="subreddit" type="text" placeholder="subreddit" v-model="subreddit">
					</div>
					<div class="mb-4 hidden">
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

					<div class="radio-buttons flex flex-col mb-4 text-gray-500 text-sm">
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
							<input type="radio" class="form-radio" name="accountType" value="postsperday" v-model="filterType">
							<span class="ml-2">Posts Per Day</span>
						</label>
					</div>

					<div class="mb-4" v-if="filterType === 'score'">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="score">
							Score
						</label>
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="score" type="number">
					</div>
					<div class="mb-4" v-if="filterType === 'percentage'">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="percentage">
							Threshold
						</label>
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="percentage" type="number">
					</div>
					<div class="mb-4" v-if="filterType === 'postsperday'">
						<label class="block text-gray-500 text-sm font-bold mb-2" for="postsperday">
							Posts Per Day
						</label>
						<input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="postsperday" type="number">
					</div>

					<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 w-full rounded focus:outline-none focus:shadow-outline transition-colors transition duration-100" type="submit">
						Submit
					</button>
				</form>
			</div>
			<footer class="border-t border-gray-700 px-8 pt-6 pb-8">
				<p class="text-center text-gray-500 text-xs">
				Reddit Top RSS
				</p>
				<p class="text-center text-gray-500 text-xs">
					<a href="https://github.com/johnwarne/reddit-top-rss/" target="_blank" class="underline">GitHub</a>
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
					<p class="clear-cache text-center"><button class="bg-transparent hover:bg-blue-500 text-gray-500 hover:text-white mt-4 py-2 px-4 border border-gray-500 hover:border-transparent rounded text-xs transition-all transition duration-100">Clear cached results (<span><?php echo sizeFormat(directorySize("cache")); ?></span>)</button></p>
				<?php } ?>
			</footer>
		</aside>

		<div class="w-full h-screen overflow-y-scroll">

			<div class="inner max-w-screen-lg mx-auto px-8 pt-6 pb-8">

				<div class="jumbotron">
					<div class="container">
						<h1 class="page-title">/r/<span class="subreddit">{{subreddit}}</span><a href="//<?php echo $_SERVER['HTTP_HOST'] . str_replace("html", "rss", $_SERVER["REQUEST_URI"]); echo !isset($_GET["view"]) ? "&view=rss" : ""; ?>" class="rss-badge badge badge-secondary small" data-toggle="tooltip" data-placement="top" title="Subscribe" target="_blank"><span class="icon-rss"></span> RSS</a></h1>
					</div>
				</div>

				<div id="post-list" data-default-subreddit="homeassistant" class=""><p>Hot posts in <strong>/r/pics</strong> at or above a score of <strong>40000</strong></p><a class="media" href="https://www.reddit.com/r/pics/comments/gl7tul/my_fiancée_says_im_handsomelike_a_japanese/" target="_blank"><div class="thumbnail"><img class="d-flex mr-3" data-src="https://b.thumbs.redditmedia.com/CWq2CpY98atD10QNx0OySQraXBDaLo8U3NtxZZoIzDM.jpg" src="https://b.thumbs.redditmedia.com/CWq2CpY98atD10QNx0OySQraXBDaLo8U3NtxZZoIzDM.jpg"></div><div class="media-body"><h5 class="mt-0"><span class="badge badge-secondary">110.6k</span><span class="hide" hidden="">Sun, 17 May 2020 02:49:02 +0000</span>My fiancée says I'm handsome...like a Japanese woodblock print. Here's to the Getty Museum Challenge</h5><time datetime="Sun, 17 May 2020 02:49:02 +0000">May 17th at 2:49 am</time></div></a><a class="media" href="https://www.reddit.com/r/pics/comments/gld6ed/wonder_woman_cosplay/" target="_blank"><div class="thumbnail"><img class="d-flex mr-3" data-src="https://b.thumbs.redditmedia.com/NW2a33_XSTMBlZKJZpfyUWRave2-ME5uMT4Jmwl-oTQ.jpg" src="https://b.thumbs.redditmedia.com/NW2a33_XSTMBlZKJZpfyUWRave2-ME5uMT4Jmwl-oTQ.jpg"></div><div class="media-body"><h5 class="mt-0"><span class="badge badge-secondary">76.8k</span><span class="hide" hidden="">Sun, 17 May 2020 10:25:50 +0000</span>wonder woman cosplay</h5><time datetime="Sun, 17 May 2020 10:25:50 +0000">May 17th at 10:25 am</time></div></a><a class="media" href="https://www.reddit.com/r/pics/comments/glgnaw/stunning_sand_sculpture_by_andoni_bastorrika/" target="_blank"><div class="thumbnail"><img class="d-flex mr-3" data-src="https://b.thumbs.redditmedia.com/unkeZvHrDm15dnskkFb6aq_iJ8Mr2hl8mIlFBwOLN-g.jpg" src="https://b.thumbs.redditmedia.com/unkeZvHrDm15dnskkFb6aq_iJ8Mr2hl8mIlFBwOLN-g.jpg"></div><div class="media-body"><h5 class="mt-0"><span class="badge badge-secondary">88.6k</span><span class="hide" hidden="">Sun, 17 May 2020 14:50:43 +0000</span>Stunning sand sculpture by Andoni Bastorrika.</h5><time datetime="Sun, 17 May 2020 14:50:43 +0000">May 17th at 2:50 pm</time></div></a><a class="media" href="https://www.reddit.com/r/pics/comments/gllyle/a_street_in_mykonos_greece/" target="_blank"><div class="thumbnail"><img class="d-flex mr-3" data-src="https://b.thumbs.redditmedia.com/aqb5aAoe2V05Zju_98NnbhShfGBTyn0bvtZBf6v46-Q.jpg" src="https://b.thumbs.redditmedia.com/aqb5aAoe2V05Zju_98NnbhShfGBTyn0bvtZBf6v46-Q.jpg"></div><div class="media-body"><h5 class="mt-0"><span class="badge badge-secondary">45k</span><span class="hide" hidden="">Sun, 17 May 2020 19:46:59 +0000</span>A street in Mykonos, Greece</h5><time datetime="Sun, 17 May 2020 19:46:59 +0000">May 17th at 7:46 pm</time></div></a><div class="cache-size d-none" data-cache-size="7 MB"></div></div>

			</div>


	</div>

	<script src="js/vue-scripts.js"></script>

</body>
</html>