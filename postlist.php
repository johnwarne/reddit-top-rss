<?php

if (file_exists("config.php")) {
	require_once "config.php";
} else {
	require_once "config-default.php";
}


// Get axios data, if this is an axios request
$data = json_decode(file_get_contents("php://input"), TRUE);
if($data) {
  $subreddit = $data['subreddit'];
  $filterType = $data['filterType'];
  $score = $data['score'];
  $threshold = $data['threshold'];
  $averagePostsPerDay = $data['averagePostsPerDay'];
}


// Sort and filter
include "sort-and-filter.php";


// Set up axios response
$response = [
  'subredditValid' => true,
  'thresholdScore' => $thresholdScore,
  'postList' => [],
];


// Check if requested subreddit is valid
$requestedSubreddit = "https://www.reddit.com/r/" . $subreddit . "/about.json";
function get_http_response_code($requestedSubreddit) {
  if(cache_get($requestedSubreddit . 'Status.txt')) {
    return cache_get($requestedSubreddit . 'Status.txt');
  } else {
    $headers = get_headers($requestedSubreddit);
    $headersStatus = substr($headers[0], 9, 3);
    cache_set($requestedSubreddit . 'Status.txt', $headersStatus);
    return $headersStatus;
  }
}
if(get_http_response_code($requestedSubreddit) != '200'){
  $response['subredditValid'] = false;
} else {
  // Get info about requested subreddit
  $jsonAboutFile = getFile("https://www.reddit.com/r/" . $subreddit . "/about.json", "redditJSON", "cache/reddit/$subreddit.json", 60 * 5, null);
  $response['jsonAboutFile'] = $jsonAboutFile;
  $response['subredditIcon'] = $jsonAboutFile['data']['icon_img'];
  // $response['subredditBannerImage'] = $jsonAboutFile['data']['header_img'];
  $response['subredditTitle'] = $jsonAboutFile['data']['title'];

  // Get subreddit hot posts
  $jsonFeedFile = getFile("https://www.reddit.com/r/" . $subreddit . ".json", "redditJSON", "cache/reddit/$subreddit.json", 60 * 5, null);
  $response['jsonFeedFile'] = $jsonFeedFile;
  // $jsonFeedFileParsed = json_decode($jsonFeedFile, true);
  $jsonFeedFileParsed = $jsonFeedFile;
  $response['jsonFeedFileParsed'] = $jsonFeedFileParsed;
  $items = $jsonFeedFileParsed["data"]["children"];
  if(!empty($items)) {
    usort($items, "sortByScoreDesc");
  }


  // List posts
  if(!empty($items)) {
    foreach ($items as $item) {
      // Only show posts at or above score threshold
      if ($item["data"]["score"] >= $thresholdScore) {
        $itemDataUrl = "https://www.reddit.com" .  $item["data"]["permalink"];
        $itemDataUrl = preg_replace("/&?utm_(.*?)\=[^&]+/", "", $itemDataUrl);
        $thumbnailURL = "https://www.redditstatic.com/mweb2x/favicon/76x76.png";
        if(!in_array($item["data"]["thumbnail"], ['default', 'nsfw', 'self', 'spoiler'])) {
          $thumbnailURL = $item["data"]["thumbnail"];
        }
        $score = $item["data"]["score"];
        $scoreRaw = $item["data"]["score"];
        if($score > 1000) {
          $score = number_format($score / 1000, 1) . "k";
          if(strpos($score, ".0") !== FALSE) {
            $score = str_replace(".0", "", $score);
          }
        }

        $response['postList'][] = [
          'url' => $itemDataUrl,
          'title' => $item["data"]["title"],
          'score' => $score,
          'scoreRaw' => $scoreRaw,
          'dateTime' => date("F jS", $item["data"]["created_utc"]) . " at " . date("g:i a", $item["data"]["created_utc"]),
          'dateTime2822' => date("r", $item["data"]["created_utc"]),
          'imgSrc' => $thumbnailURL,
        ];
      }
    }
  }


  // Echo cache size
  if(CACHE_REDDIT_JSON == true || CACHE_MERCURY_CONTENT == true || CACHE_RSS_FEEDS == true) {
    $response['cacheSize'] = sizeFormat(directorySize("cache"));
  }


  // Echo JSON output to axios
  echo json_encode($response);


}