# Reddit Top RSS

Reddit Top RSS is a set of scripts for [Reddit's API](https://www.reddit.com/dev/api/) that generates RSS feeds for specified subreddits with score thresholds. To preview your outputted feed items there is a front end that utilizes the Bootstrap v4 framework.

## Preview

![Reddit Top RSS screenshot](dist/img/preview.gif?raw=true)

## Installation and usage

To install, clone this repository somewhere with PHP >= 5.6 installed, and open `index.php` in a browser to view the front end. Enter your parameters into the fields to get a preview of the posts that the filters will output. Click the `RSS` button at top to open a new tab with the rendered RSS XML output of the specified filters. This is the URL you subscribe to in your RSS aggregator.

## Supported URL parameters

There are five URL paramenters supported:

### subreddit
The exact string of the subreddit as it appears in the Reddit URL. Only one subreddit may be chosen.

### score
Items below the desired score will be filtered out.

### threshold
This parameter will get the average score for the past month's hot posts and will filter out items that fall below this percentage. This is helpful for volatile subreddits — and subreddits in general — since more people are using the service and causing posts to be scored higher and higher. Since this is a percentage, the number of items in the outputted feed should be more consistent than when using the `score` parameter.

### averagePostsPerDay

Reddit Top RSS will attempt to output an average number of posts per day by looking at a subreddit's recent history to determine the score below which posts will be filtered out. This is the filter I find most useful.

### view

Accepted values are `html` and `rss`:

- `html` shows the front end preview.
- `rss` shows the rendered RSS XML feed. Use this for the URL to subscribe to in your RSS aggregator.
- If the `view` parameter is left blank or omitted, the front end is shown.

## URI examples

- `https://www.example.com?subreddit=funny&threshold=10000`
- `https://www.example.com?subreddit=worldnews&score=1000&view=rss`
- `https://www.example.com?subreddit=coolgithubprojects&averagePostsPerDay=3`

## Configuration

Reddit Top RSS comes with a default configuration. If you'd like to turn caching off, set a different default subreddit, or use a self-hosted [Mercury parser](#mercury-parser), just copy `config-default.php` to `config.php` and enter your changed values.

## Mercury parser<a name="mercury-parser"></a>

If you'd like to include parsed article content in your outputted feed items, set a self-hosted [Mercury parser](https://github.com/postlight/mercury-parser) URL and optional API key in `config.php`. An easy to install, Dockerized version of the Mercury parser can be found here: [https://www.github.com/HenryQW/mercury-parser-api](https://www.github.com/HenryQW/mercury-parser-api).

## Caching

By default Reddit Top RSS will cache Reddit JSON files, rendered RSS XML files, and Mercury parsed content to speed up the application. To clear the cache, click the `Clear cached results` link in the footer. To disable caching for any of the above items, set the appropriate values to `false` in `config.php`.

## License

This project is released under the [MIT License].

[MIT License]: http://www.opensource.org/licenses/MIT