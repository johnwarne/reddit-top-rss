<?php

// Config
if (file_exists('config.php')) {
	require_once 'config.php';
} else {
	require_once 'config-default.php';
}


// Globals
global $subreddit;
global $thresholdScore;
global $thresholdPercentage;
global $thresholdPostsPerDay;
global $mercuryJSON;


// Cache
include 'cache.php';


// View format
if(isset($_GET['view']) && $_GET['view'] == 'rss') {
	include 'rss.php';
} else {
	include 'html.php';
}