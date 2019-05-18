<?php

if (file_exists("config.php")) {
	require_once "config.php";
} else {
	require_once "config-example.php";
}


// Check if requested subreddit is valid
$requestedSubreddit = "https://www.reddit.com/r/" . $_GET["subreddit"] . "/top/.json";
function get_http_response_code($requestedSubreddit) {
  $headers = get_headers($requestedSubreddit);
  return substr($headers[0], 9, 3);
}
if(get_http_response_code($requestedSubreddit) != "200"){
  echo "<div class='alert alert-warning' role='alert'>/r/" . $_GET["subreddit"] . " is not a valid subreddit</div>";
} else {


  // Sort and filter
  include "sort-and-filter.php";


  // Description text
  $postListDescription = "<p>Hot posts in <strong>/r/";
  $postListDescription .= $_GET["subreddit"];
  $postListDescription .= "</strong>";
  if($thresholdScore) {
    $postListDescription .= " at or above a score of <strong>";
    $postListDescription .= $thresholdScore;
    $postListDescription .= "</strong>";
    if(isset($_GET["threshold"]) && $_GET["threshold"]) {
      $postListDescription .= " <em>(";
      $postListDescription .= $thresholdPercentage;
      $postListDescription .= "% of monthly top posts' average score)</em>";
    }
    if(isset($_GET["averagePostsPerDay"]) && $_GET["averagePostsPerDay"]) {
      $postListDescription .= " <em>(giving a rough average of ";
      $postListDescription .= $_GET["averagePostsPerDay"];
      $postListDescription .= " posts per day)</em>";
    }
  }
  $postListDescription .= "</p>";


  // Get subreddit hot posts
  $jsonFeedFile = getFile("https://www.reddit.com/r/" . $subreddit . ".json", "redditJSON", "cache/reddit/$subreddit.json", 60 * 5);
  $jsonFeedFileParsed = json_decode($jsonFeedFile, true);
  $items = $jsonFeedFileParsed["data"]["children"];
  usort($items, "sortByCreatedDate");


  // List posts
  $postListText = "";
  foreach ($items as $item) {
    // Only show posts at or above score threshold
    if ($item["data"]["score"] >= $thresholdScore) {
      $itemDataUrl = "https://www.reddit.com" .  $item["data"]["permalink"];
      $itemDataUrl = preg_replace("/&?utm_(.*?)\=[^&]+/", "", $itemDataUrl);
      $thumbnailURL = "https://www.redditstatic.com/mweb2x/favicon/76x76.png";
      if($item["data"]["thumbnail"] && $item["data"]["thumbnail"] != "self" && $item["data"]["thumbnail"] != "nsfw") {
        $thumbnailURL = $item["data"]["thumbnail"];
      }
      $score = $item["data"]["score"];
      if($score > 1000) {
        $score = number_format($score / 1000, 1) . "k";
        if(strpos($score, ".0") !== FALSE) {
          $score = str_replace(".0", "", $score);
        }
      }
      $postListText .= "<a class='media' href='$itemDataUrl' target='_blank'><div class='thumbnail'><img class='d-flex mr-3' data-src='$thumbnailURL'></div><div class='media-body'><h5 class='mt-0'><span class='badge badge-secondary'>$score</span><span class='hide' hidden>" . date("r", $item["data"]["created_utc"]) . "</span>" . $item["data"]["title"] . "</h5><time datetime='" . date("r", $item["data"]["created_utc"]) . "'>" . date("F jS", $item["data"]["created_utc"]) . " at " . date("g:i a", $item["data"]["created_utc"]) . "</time></div></a>";
    }
  }


  // Echo post list or no posts message
  if($postListText) {
    echo $postListDescription . $postListText;
  } else {
    echo "<div class='alert alert-warning' role='alert'>No hot posts in <strong>/r/" . $subreddit . "</strong> match the filter above.</div>";
  }


  // Echo cache size
  if(CACHE_REDDIT_JSON || CACHE_MERCURY_CONTENT || CACHE_RSS_FEEDS) {
    echo "<div class='cache-size d-none' data-cache-size='" . sizeFormat(directorySize("cache")) . "'></div>";
  }


}