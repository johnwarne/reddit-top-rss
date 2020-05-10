<?php

// Create cache directories if they don't exist and remove expired cache files
if(CACHE_REDDIT_JSON == true) {
	if (!file_exists("cache/reddit")) {
		mkdir("cache/reddit", 0755, true);
	}
	// Remove Reddit JSON files older than 5 minutes
	$dir = "cache/reddit/";
	foreach (glob($dir . "*") as $file) {
		if(time() - filectime($file) > 60 * 5) {
			unlink($file);
		}
	}
}
if(CACHE_MERCURY_CONTENT == true) {
	if (!file_exists("cache/mercury")) {
		mkdir("cache/mercury", 0755, true);
	}
	// Remove Mercury JSON files older than 7 days
	$dir = "cache/mercury/";
	foreach (glob($dir . "*") as $file) {
		if(time() - filectime($file) > 60 * 60 * 24 * 7) {
			unlink($file);
		}
	}
}
if(CACHE_RSS_FEEDS == true) {
	if (!file_exists("cache/rss")) {
		mkdir("cache/rss", 0755, true);
	}
	// Remove RSS feed files older than 1 hour
	$dir = "cache/rss/";
	foreach (glob($dir . "*") as $file) {
		if(time() - filectime($file) > 60 * 60 ) {
			unlink($file);
		}
	}
}