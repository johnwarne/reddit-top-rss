<?php

// Default subreddit
if (isset($_SERVER["DEFAULT_SUBREDDIT"])) {
  define('DEFAULT_SUBREDDIT', $_SERVER["DEFAULT_SUBREDDIT"]);
} else {
  define('DEFAULT_SUBREDDIT', 'pics');
}

// Mercury Parser URL, e.g. https://mercuryparser.example.com
if (isset($_SERVER["MERCURY_URL"])) {
  define('MERCURY_URL', $_SERVER["MERCURY_URL"]);
} else {
  define('MERCURY_URL', '');
}

// Mercury API key, e.g. YaaFZZf9rUvZJQLXmt&MN9efXKQJxMa1k8smtv09
if (isset($_SERVER["MERCURY_API_KEY"])) {
  define('MERCURY_API_KEY', $_SERVER["MERCURY_API_KEY"]);
} else {
  define('MERCURY_API_KEY', '');
}

// Cache Reddit JSON files
if (isset($_SERVER["CACHE_REDDIT_JSON"])) {
  define('CACHE_REDDIT_JSON', $_SERVER["CACHE_REDDIT_JSON"]);
} else {
  define('CACHE_REDDIT_JSON', 'true');
}

// Cache Mercury Contend
if (isset($_SERVER["CACHE_MERCURY_CONTENT"])) {
  define('CACHE_MERCURY_CONTENT', $_SERVER["CACHE_MERCURY_CONTENT"]);
} else {
  define('CACHE_MERCURY_CONTENT', 'true');
}

// Cache RSS feeds
if (isset($_SERVER["CACHE_RSS_FEEDS"])) {
  define('CACHE_RSS_FEEDS', $_SERVER["CACHE_RSS_FEEDS"]);
} else {
  define('CACHE_RSS_FEEDS', 'true');
}

// Config version
if (isset($_SERVER["CONFIG_VERSION"])) {
  define('CONFIG_VERSION', $_SERVER["CONFIG_VERSION"]);
} else {
  define('CONFIG_VERSION', 1);
}
