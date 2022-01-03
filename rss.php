<?php
// With some help from https://www.sanwebe.com/2013/07/creating-valid-rss-feed-using-php

header("Content-Type: text/xml; charset=utf-8", true);


// Return cached RSS feed, if it exists and if it was cached in the past hour
if(CACHE_RSS_FEEDS) {
	if(file_exists("cache/rss/" . $_SERVER["REQUEST_URI"] . ".xml") && time() - filemtime("cache/rss/" . $_SERVER["REQUEST_URI"] . ".xml") < 60 * 60) {
		echo file_get_contents("cache/rss/" . $_SERVER["REQUEST_URI"] . ".xml", true);
		exit;
	}
}


// Sort and filter
include "sort-and-filter.php";


// Get Subreddit feed
$jsonFeedFile = getFile("https://www.reddit.com/r/" . $subreddit . ".json", "redditJSON", "cache/reddit/$subreddit.json", 60 * 5);
$jsonFeedFileParsed = json_decode($jsonFeedFile, true);
$jsonFeedFileItems = $jsonFeedFileParsed["data"]["children"];
usort($jsonFeedFileItems, "sortByCreatedDate");


// Feed description text
$feedDescriptionText = "Hot posts in /r/";
$feedDescriptionText .= $subreddit;
if($thresholdScore) {
	if(isset($_GET["score"]) && $_GET["score"]) {
		$feedDescriptionText .= " at or above a score of ";
		$feedDescriptionText .= $_GET["score"];
	}
	if(isset($_GET["threshold"]) && $_GET["threshold"]) {
		$feedDescriptionText .= " at or above ";
		$feedDescriptionText .= $thresholdPercentage;
		$feedDescriptionText .= "% of monthly top posts' average score";
	}
	if(isset($_GET["averagePostsPerDay"]) && $_GET["averagePostsPerDay"]) {
		$feedDescriptionText .= " (roughly ";
		$feedDescriptionText .= $_GET["averagePostsPerDay"];
		$feedDescriptionText .= " posts per day)";
	}
}


// Create new DOM document
$xml = new DOMDocument("1.0", "UTF-8");


// Create "RSS" element
$rss = $xml->createElement("rss");
$rssNode = $xml->appendChild($rss);
$rssNode->setAttribute("version", "2.0");


// Set attributes
$rssNode->setAttribute("xmlns:dc", "http://purl.org/dc/elements/1.1/");
$rssNode->setAttribute("xmlns:content", "http://purl.org/rss/1.0/modules/content/");
$rssNode->setAttribute("xmlns:atom", "https://www.w3.org/2005/Atom");


// Create RFC822 Date format to comply with RFC822
$dateF = date("D, d M Y H:i:s T", time());
$buildDate = gmdate(DATE_RFC2822, strtotime($dateF));


// Create "channel" element under "RSS" element
$channel = $xml->createElement("channel");
$channelNode = $rssNode->appendChild($channel);
$channelImageNode = $rssNode->appendChild($channel);


// A feed should contain an atom:link element
// http://j.mp/1nuzqeC
$channelAtomLink = $xml->createElement("atom:link");
$channelAtomLink->setAttribute("href", "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
$channelAtomLink->setAttribute("rel", "self");
$channelAtomLink->setAttribute("type", "application/rss+xml");
$channelNode->appendChild($channelAtomLink);


// Add general elements under "channel" node
$channelNode->appendChild($xml->createElement("title", "/r/" . $subreddit));
$channelNode->appendChild($xml->createElement("description", $feedDescriptionText));
$channelNode->appendChild($xml->createElement("link", "https://www.reddit.com/r/" . $subreddit));
$channelNode->appendChild($xml->createElement("language", "en-us"));
$channelNode->appendChild($xml->createElement("lastBuildDate", $buildDate));
$channelNode->appendChild($xml->createElement("generator", "PHP DOMDocument"));
$channelImageNode = $channelNode->appendChild($xml->createElement("image"));
$channelImageNode->appendChild($xml->createElement("url", "https://www.redditstatic.com/desktop2x/img/favicon/android-icon-192x192.png"));


// Loop through feed items
foreach($jsonFeedFileItems as $item) {


	// Only show posts at or above score threshold
	if ($item["data"]["score"] >= $thresholdScore) {


		// Remove any utm junk from main item link
		$itemDataUrl = $item["data"]["url"];
		$itemDataUrl = preg_replace("/&?utm_(.*?)\=[^&]+/","","$itemDataUrl");


		// Create a new node called "item"
		$itemNode = $channelNode->appendChild($xml->createElement("item"));


		// Item Title
		$itemTitle = $item["data"]["title"];
		if($item["data"]["domain"]) {
			$itemTitle .= " (" . $item["data"]["domain"] . ")";
		}
		if (
			(strpos($itemDataUrl,"imgur") !== false && strpos($itemDataUrl,"gallery") !== false) ||
			strpos($item["data"]["url"], "www.reddit.com/gallery/")
		) {
			$itemTitle .= " (Gallery)";
		}
		$titleNode = $itemNode->appendChild($xml->createElement("title", $itemTitle));


		// Item Link
		$linkNode = $itemNode->appendChild($xml->createElement("link", $itemDataUrl));


		// Unique identifier for the item (GUID)
		$guidLink = $xml->createElement("guid", "https://www.reddit.com" . $item["data"]["permalink"]);
		$guidLink->setAttribute("isPermaLink", "false");
		$guid_node = $itemNode->appendChild($guidLink);


		// Create "comments" node under "item"
		if(strpos($item["data"]["domain"], "self.") == false) {
			$commentsNode = $itemNode->appendChild($xml->createElement("comments", "https://www.reddit.com" . $item["data"]["permalink"]));
		}


		// Create "description" node under "item"
		$descriptionNode = $itemNode->appendChild($xml->createElement("description"));


		// Create description text for "description" node
		$itemDescription = "";


		// Description comments link
		$itemDescription .= "<p><a href='https://www.reddit.com" . $item["data"]["permalink"] . "'>Post permalink</a> </p>";


		// Add media if it exists
		switch (true) {

			// General embeds
			case isset($item["data"]["media_embed"]["content"]):
				$mediaEmbed = $item["data"]["media_embed"]["content"];
				$mediaEmbed = str_replace("&lt;", "<", $mediaEmbed);
				$mediaEmbed = str_replace("&gt;", ">", $mediaEmbed);
				$itemDescription .= $mediaEmbed;
			break;
			case isset($item["data"]["secure_media_embed"]["content"]):
				$mediaEmbed = $item["data"]["secure_media_embed"]["content"];
				$mediaEmbed = str_replace("&lt;", "<", $mediaEmbed);
				$mediaEmbed = str_replace("&gt;", ">", $mediaEmbed);
				$itemDescription .= $mediaEmbed;
			break;
			case isset($item["data"]["secure_media"]["oembed"]["html"]):
				$mediaEmbed = $item["data"]["secure_media"]["oembed"]["html"];
				$mediaEmbed = str_replace("&lt;", "<", $mediaEmbed);
				$mediaEmbed = str_replace("&gt;", ">", $mediaEmbed);
				$itemDescription .= $mediaEmbed;
			break;

			// Imgur gifv
			case strpos($item["data"]["url"], "imgur") && strpos($item["data"]["url"], "gifv"):
				$mediaEmbed = "<video poster='" . str_replace(".gifv", "h.jpg", $item["data"]["url"]) . "' controls='true' preload='auto' autoplay='false' muted='muted' loop='loop' webkit-playsinline='' style='max-width: 90vw;'>
					<source src='" . str_replace("gifv", "mp4", $item["data"]["url"]) . "' type='video/mp4'>
				</video>";
				$itemDescription .= $mediaEmbed;
			break;

			// Reddit videos
			case $item["data"]["domain"] == "v.redd.it":
				$mediaEmbed = "<iframe src='https://www.redditmedia.com" . $item["data"]["permalink"] . "?ref_source=embed&amp;ref=share&amp;embed=true&amp;theme=dark' sandbox='allow-scripts allow-same-origin allow-popups' style='border: none; min-height: 90% !important; overflow: scroll; width: 90% !important;' width='300' height='800' scrolling='yes'></iframe>";
				if(isset($item["data"]["thumbnail"])) {
					$mediaEmbed .= "<p><img src='" . $item["data"]["thumbnail"] . "' /></p>";
				}
				$itemDescription .= $mediaEmbed;
			break;

			// Reddit galleries
			case strpos($item["data"]["url"], "www.reddit.com/gallery/"):
				$jsonGalleryURL = str_replace("www.reddit.com/gallery/", "www.reddit.com/comments/", $item["data"]["url"]) . '.json';
				$jsonGalleryFileName = str_replace("https://www.reddit.com/comments/", "", $jsonGalleryURL);
				$jsonGalleryFile = getFile($jsonGalleryURL, "redditJSON", "cache/reddit/$jsonGalleryFileName", 60 * 5);
				$jsonGalleryFileParsed = json_decode($jsonGalleryFile, true);
				$jsonGalleryFileItems = $jsonGalleryFileParsed[0]["data"]["children"][0]["data"]["media_metadata"];
				$mediaEmbed = "";
				foreach ($jsonGalleryFileItems as $image) :
					$previewImageURL = str_replace("preview.redd.it", "i.redd.it", $image["p"]["3"]["u"]);
					$fullImageURL = str_replace("preview.redd.it", "i.redd.it", $image["s"]["u"]);
					$mediaEmbed .= "<p><a href='" . $fullImageURL . "'><img src='" . $previewImageURL . "'></a></p>";
				endforeach;
				$itemDescription .= $mediaEmbed;
				break;

			// Reddit images
			case $item["data"]["domain"] == "i.redd.it":
				$mediaEmbed = "<p><a href='" . $item["data"]["url"] . "'><img src='" . $item["data"]["url"] . "'></img></a></p>";
				$itemDescription .= $mediaEmbed;
				break;

			// Preview images
			case !empty($item["data"]["preview"]["images"]) && strpos($item["data"]["preview"]["images"][0]["source"]["url"],"redditmedia") !== false:
				$mediaEmbed = "<p><img src='" . $item["data"]["preview"]["images"][0]["source"]["url"] . "' /></p>";
				$itemDescription .= $mediaEmbed;
			break;

			// Image in URL
			case strpos($itemDataUrl, "jpg") !== false || strpos($itemDataUrl, "jpeg") !== false || strpos($itemDataUrl, "png") !== false || strpos($itemDataUrl, "gif") !== false:
				$itemDescription .= "<p><img src='" . $itemDataUrl . "' /></p>";
			break;

			// Imgur
			case strpos($itemDataUrl, "imgur.com") !== false:
				$itemDescription .= "<p><img src='" . $itemDataUrl . ".jpg' /></p>";
			break;

			// Livememe
			case strpos($itemDataUrl, "livememe") !== false:
				$imageUrl = str_replace(["http://www.livememe.com/", "https://www.livememe.com/"], "https://i.lvme.me/", $itemDataUrl);
				$itemDescription .= "<p><img src='" . $imageUrl . ".jpg' /></p>";
			break;

			// Mercury-parsed lead image
			case MERCURY_URL && strpos($item["data"]["domain"], "self.") == false:
				$mercuryJSON = getFile($itemDataUrl, "mercuryJSON", "cache/mercury/" . filter_var($itemDataUrl, FILTER_SANITIZE_ENCODED) . ".json", 60 * 60 * 24 * 7);
				if(!isset(json_decode($mercuryJSON)->message) || json_decode($mercuryJSON)->message != "Internal server error") {
					$mercuryJSON = json_decode($mercuryJSON);
					if ($mercuryJSON->lead_image_url) {
						$itemDescription .= "<p><img src='" . $mercuryJSON->lead_image_url . "' /></p>";
					}
				}
			break;
		}


		// Feed text content and string replacement
		switch (true) {

			// Selftext
			case isset($item["data"]["selftext_html"]):
				$selftext = $item["data"]["selftext_html"];
				$selftext = str_replace("&lt;", "<", $selftext);
				$selftext = str_replace("&gt;", ">", $selftext);
				$selftext = str_replace("&amp;quot;", '"', $selftext);
				$selftext = str_replace("&amp;#39;", "'", $selftext);
				$itemDescription .= $selftext;
			break;

			// No selftext and domain contains self
			case !isset($item["data"]["selftext_html"]) && strpos($item["data"]["domain"],"self.") !== false:
			break;

			// Mercury-parsed article content
			case MERCURY_URL && strpos($item["data"]["domain"], "self.") == false && strpos($item["data"]["url"], "redd.it") == false:
				$mercuryJSON = getFile($itemDataUrl, "mercuryJSON", "cache/mercury/" . filter_var($itemDataUrl, FILTER_SANITIZE_ENCODED) . ".json", 60 * 60 * 24 * 7);
				if(!isset(json_decode($mercuryJSON)->message) || json_decode($mercuryJSON)->message != "Internal server error") {
					if ($mercuryJSON = json_decode($mercuryJSON)) {
						$itemDescription .= $mercuryJSON->content;
					}
				}
			break;
		}


		// Add article's best comments
		if(isset($_GET["comments"]) && $_GET["comments"] > 0) {
			$commentsURL = "https://www.reddit.com/r/" . $item["data"]["subreddit"] . "/comments/" . $item["data"]["id"] . ".json?depth=1&showmore=0&limit=" . (intval($_GET["comments"]) + 1);
			$commentsJSON = getFile($commentsURL, "redditJSON", "cache/reddit/" . $item["data"]["subreddit"] . "-comments-" . $item["data"]["id"] . $_GET["comments"] . ".json", 60 * 5);
			$commentsJSONParsed = json_decode($commentsJSON, true);
			$comments = $commentsJSONParsed[1]["data"]["children"];
			if($comments[0]["data"]["author"] == "AutoModerator") {
				unset($comments[0]);
				$comments = array_values($comments);
			}
			if(count($comments) > $_GET["comments"]) {
				$commentCount = $_GET["comments"];
			} else {
				$commentCount = count($comments);
			}
			if($commentCount) {
				$itemDescription .= "<p>&nbsp;</p><hr><p>&nbsp;</p>";
				if($commentCount == 1) {
					$itemDescription .= "<p>Best comment</p>";
				} elseif($commentCount > 1) {
					$itemDescription .= "<p>Best comments</p>";
				}
				$itemDescription .= "<ol>";
				for ($i = 0; $i < $commentCount; $i++) {
					$itemDescription .= "<li>" . htmlspecialchars_decode($comments[$i]["data"]["body_html"]) . "<ul><li><a href='https://www.reddit.com" . $comments[$i]["data"]["permalink"] . "'><small>Permalink</small></a> | <a href='https://www.reddit.com/user/" . $comments[$i]["data"]["author"] . "'><small>Author</small></a></li></ul></li>";
				}
				$itemDescription .= "</ol>";
			}
		}


		//fill description node with CDATA content
		$descriptionContents = $xml->createCDATASection($itemDescription);
		$descriptionNode->appendChild($descriptionContents);


		//Published date
		$pubDate = $xml->createElement("pubDate", date("r", $item["data"]["created_utc"]));
		$pubDateNode = $itemNode->appendChild($pubDate);


	}


	// Prevent CPU from spiking
	usleep(0.1 * 1000000);
}


// Cache RSS feed
if(CACHE_RSS_FEEDS) {
	file_put_contents("cache/rss/" . $_SERVER["REQUEST_URI"] . ".xml", $xml->saveXML());
}


// Echo the feed
echo $xml->saveXML();
?>