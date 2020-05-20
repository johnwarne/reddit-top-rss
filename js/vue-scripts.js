// Debounce function to prevent too frequent form submissions
function debounce (fn, delay) {
  var timeoutID = null
  return function () {
    clearTimeout(timeoutID)
    var args = arguments
    var that = this
    timeoutID = setTimeout(function () {
      fn.apply(that, args)
    }, delay)
  }
}


// Copy href to clipboard
function myFunction(event) {
	console.log(event);
	event.target.preventDefault();
  var copyText = document.getElementById("myInput");
  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand("copy");

  var tooltip = document.getElementById("myTooltip");
  tooltip.innerHTML = "Copied: " + copyText.value;
}

function outFunc() {
  var tooltip = document.getElementById("myTooltip");
  tooltip.innerHTML = "Copy to clipboard";
}


var app = new Vue({
	el: '#app',
	data: {
		subreddit: subreddit,
		filterType: filterType,
		score: score,
		computedScore: score,
		percentage: percentage,
		postsPerDay: postsPerDay,
		cacheSize: '0 B',
		includeComments: includeComments,
		includedComments: comments,
		generatedRssUrl: null,
		posts: [
			{
				url: null,
				title: null,
				score: 0,
				scoreRaw: 0,
				dateTime: null,
				dateTime2822: null,
				imgSrc: null
			}
		]
	},
	methods: {
		myFunction: function() {
			var copyText = document.getElementById("myInput");
			console.log(copyText.value);
			copyText.select();
			copyText.setSelectionRange(0, 99999);
			document.execCommand("copy");

			var tooltip = document.getElementById("myTooltip");
			tooltip.innerHTML = "Copied: " + copyText.value;
		},
		outFunc: function(event) {
			var tooltip = document.getElementById("myTooltip");
			tooltip.innerHTML = "Copy to clipboard";
		},
		submitFormOnChange: function(event) {
			debounce(function(event) {
				this.formSubmit(event);
			}, 250)
		},
		clearCache: function() {
			window.axios.post('cache-clear.php')
			.then(function (response) {
				this.cacheSize = '0 B';
			}.bind(this))
			.catch(function (error) {
				console.log(error);
			});
			this.formSubmit();
		},
		buildURL: function() {
			var port = window.location.port;
			if(port) {
				port = ':' + port;
			} else {
				port = '';
			}
			url = window.location.href;
			url = window.location.protocol + '//' + window.location.hostname + port + window.location.pathname;
			var searchParams = new URLSearchParams();
			searchParams.set('subreddit', this.subreddit);
			if(this.filterType === 'score') {
				searchParams.set('score', this.score);
			} else if(this.filterType === 'percentage') {
				searchParams.set('threshold', this.percentage);
			} else if(this.filterType === 'postsPerDay') {
				searchParams.set('averagePostsPerDay', this.postsPerDay);
			}
			if(this.includeComments) {
				searchParams.set('comments', this.includedComments);
			}
			url = url + '?' + searchParams;
			this.generatedRssUrl = url + '&view=rss';
			window.history.pushState({}, window.title, '?' + searchParams);
		},
		formSubmit: function(event) {
			document.getElementById("post-list").classList.add("loading");
			document.getElementById("submit").classList.add("opacity-50", "cursor-not-allowed");
			document.getElementById("submit").innerHTML = "Loading posts…";
			document.getElementById("post-list").classList.remove('notices', 'warning', 'info');
			document.querySelectorAll('#post-list .notice').forEach(function(a){
				a.remove()
			});
			this.buildURL();
			window.axios.post('postlist.php', {
				subreddit : this.subreddit,
				filterType : this.filterType,
				score : this.score,
				threshold : this.percentage,
				averagePostsPerDay : this.postsPerDay,
			})
			.then(function (response) {
				document.getElementById("post-list").classList.remove("loading");
				document.getElementById("submit").classList.remove("opacity-50", "cursor-not-allowed");
				document.getElementById("submit").innerHTML = "Submit";
				this.posts = response.data.postList;
				if(!response.data.subredditValid) {
					console.log('subreddit is not valid');
					document.getElementById("post-list").classList.add('notices', 'warning');
					document.getElementById("post-list").innerHTML = '<div class="notice"><strong>/r/' + this.subreddit + '</strong> is not a valid subreddit</div>';
				} else if(!response.data.postList.length) {
					document.getElementById("post-list").classList.add('notices', 'info');
					document.getElementById("post-list").innerHTML = '<div class="notice">No hot posts in <strong>/r/' + this.subreddit + '</strong> match the filters</div>';
				} else {
					this.cacheSize = response.data.cacheSize;
					this.computedScore = response.data.thresholdScore;
				}
			}.bind(this))
			.catch(function (error) {
				console.log(error);
				document.getElementById("submit").classList.remove("opacity-50", "cursor-not-allowed");
				document.getElementById("submit").innerHTML = "Submit";
			});
		}
	},
	watch: {
		subreddit: debounce(function() {
			this.formSubmit();
		}, 500),
		filterType: debounce(function() {
			this.formSubmit();
		}, 0),
		score: debounce(function() {
			this.formSubmit();
		}, 500),
		percentage: debounce(function() {
			this.formSubmit();
		}, 500),
		postsPerDay: debounce(function() {
			this.formSubmit();
		}, 500),
		includeComments: debounce(function() {
			this.buildURL();
		}, 0),
		includedComments: debounce(function() {
			this.buildURL();
		}, 500),
	},
	mounted: function () {
		this.formSubmit();
	}
});