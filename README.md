# Reddit Top RSS

Reddit Top RSS is a set of scripts for [Reddit's API](https://www.reddit.com/dev/api/) that generates RSS feeds for specified subreddits with score thresholds. To preview your outputted feed items there is a front end that utilizes the Bootstrap v4 framework.

## Preview

![Reddit Top RSS screenshot](dist/img/preview.gif?raw=true)

## Motivation

I prefer to interact with Reddit in a low-volume way, so I let Reddit Top RSS surface the most popular posts per subreddit in my [RSS reader of choice](https://reederapp.com/). I usually use the `averagePostsPerDay` filter so I can expect a certain amount of posts in my feeds per day.

## Installation and usage

### Reddit app setup

**Due to Reddit's 2023 updated API policies it is now required to first set up an app in your Reddit account that Reddit Top RSS will authenticate through.**

1. First, log into your Reddit account.
1. Navigate to the Reddit app preferences page: [https://www.reddit.com/prefs/apps](https://www.reddit.com/prefs/apps)
1. Click the `create app` or `create another app` button, depending on whether you’ve already created an app before.
1. Choose any name for the app. I've chosen `Top RSS`. Reddit will not allow you to use `Reddit` in the name.
1. Set the type of app to `web app`.
1. You can leave `description` and `about url` fields blank.
1. Enter in any valid URI in the `redirect uri` field. I've used `http://reddit-top-rss.test`.
1. Click the `create app` button when you’re done.
1. Your app’s client ID and client secret will be displayed. You'll need to add these to either your `config.php` or Docker environment variables.

### Manual

To install this repository manually:

1. Clone this repository somewhere with PHP >= 5.6 installed.
1. Copy `config-default.php` to `config.php`.
1. Enter your Reddit user, app ID, and secret into lines 49, 56, and 63, respectively.
1. Open `index.php` in a browser to view the front end.
1. Enter your parameters into the fields to get a preview of the posts that the filters will output. Click the `RSS` button at top to open a new tab with the rendered RSS XML output of the specified filters. This is the URL you subscribe to in your RSS aggregator.

### Docker

A docker image is available at [https://hub.docker.com/r/johnny5w/reddit-top-rss](https://hub.docker.com/r/johnny5w/reddit-top-rss).

#### Command line

```docker
docker run -p 80:8080 -e REDDIT_USER=your_reddit_user -e REDDIT_CLIENT_ID=your_app_id -e REDDIT_CLIENT_SECRET=your_app_secret johnny5w/reddit-top-rss:latest
```

#### docker-compose

```yaml

version: '3'
services:
  reddit-top-rss:
    image: johnny5w/reddit-top-rss
    container_name: reddit-top-rss
    restart: unless-stopped
    ports:
      - 80:8080
    environment:
      - REDDIT_USER=your_reddit_user
      - REDDIT_CLIENT_ID=your_app_id
      - REDDIT_CLIENT_SECRET=your_app_secret
      - DEFAULT_SUBREDDIT=news
```

#### Docker environment variables

The following required environment variables must be set, or you will not be authorized with Reddit's API:

| Parameter             | Function                                                                       |
| --------------------- | ------------------------------------------------------------------------------ |
| REDDIT_USER           | Your Reddit user account with which you've created an app                      |
| REDDIT_CLIENT_ID      | The client ID of the app you've created                                        |
| REDDIT_CLIENT_SECRET  | The secret for the app you've created                                          |

The following optional environment variables can be used to override the application defaults:

| Parameter             | Function                                                                       |
| --------------------- | ------------------------------------------------------------------------------ |
| DEFAULT_SUBREDDIT     | This sets the initial subreddit on the first page load.<br/>Default: `pics`    |
| MERCURY_URL           | URL of your Mercury parser instance. <a href="#mercury-parser">See below</a>.  |
| MERCURY_API_KEY       | API key for your Mercury parser instance.                                      |
| CACHE_REDDIT_JSON     | Whether to cache the JSON responses from Reddit.<br/>Default: `true`           |
| CACHE_MERCURY_CONTENT | Whether to cache the responses from your Mercury instance.<br/>Default: `true` |
| CACHE_RSS_FEEDS       | Whether to cache the outputted XML from Reddit Top RSS.<br/>Default: `true`    |

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

Reddit Top RSS comes with a default configuration. If you'd like to turn caching off, set a different default subreddit, or use a self-hosted [Mercury parser](#mercury-parser), just enter your desired values in `config.php`.

## Mercury parser<a name="mercury-parser"></a>

If you'd like to include parsed article content in your outputted feed items, set a self-hosted [Mercury parser](https://github.com/postlight/mercury-parser) URL and optional API key in `config.php`. An easy to install, Dockerized version of the Mercury parser can be found here: [https://www.github.com/HenryQW/mercury-parser-api](https://www.github.com/HenryQW/mercury-parser-api).

## Caching

By default Reddit Top RSS will cache Reddit JSON files, rendered RSS XML files, and Mercury parsed content to speed up the application. To clear the cache, click the `Clear cached results` link in the footer. To disable caching for any of the above items, set the appropriate values to `false` in `config.php`.

## License

This project is released under the [MIT License].

[MIT License]: http://www.opensource.org/licenses/MIT

## Buy me a coffee

[![BuyMeCoffee][buymecoffeebadge]][buymecoffee]

---

[buymecoffee]: https://www.buymeacoffee.com/johnwarne
[buymecoffeebadge]: https://img.shields.io/badge/buy%20me%20a%20coffee-donate-yellow.svg?style=for-the-badge
