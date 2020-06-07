function debounce(o,n){var i=null;return function(){clearTimeout(i);var t=arguments,e=this;i=setTimeout(function(){o.apply(e,t)},n)}}function myFunction(t){console.log(t),t.target.preventDefault();var e=document.getElementById("myInput");e.select(),e.setSelectionRange(0,99999),document.execCommand("copy"),document.getElementById("myTooltip").innerHTML="Copied: "+e.value}function outFunc(){document.getElementById("myTooltip").innerHTML="Copy to clipboard"}var app=new Vue({el:"#app",data:{subreddit:subreddit,filterType:filterType,score:score,computedScore:score,percentage:percentage,postsPerDay:postsPerDay,cacheSize:"0 B",includeComments:includeComments,includedComments:comments,generatedRssUrl:null,posts:[{url:null,title:null,score:0,scoreRaw:0,dateTime:null,dateTime2822:null,imgSrc:null}]},methods:{myFunction:function(){var t=document.getElementById("myInput");console.log(t.value),t.select(),t.setSelectionRange(0,99999),document.execCommand("copy"),document.getElementById("myTooltip").innerHTML="Copied: "+t.value},outFunc:function(t){document.getElementById("myTooltip").innerHTML="Copy to clipboard"},submitFormOnChange:function(t){debounce(function(t){this.formSubmit(t)},250)},clearCache:function(){window.axios.post("cache-clear.php").then(function(t){this.cacheSize="0 B"}.bind(this)).catch(function(t){console.log(t)}),this.formSubmit()},buildURL:function(){var t=(t=window.location.port)?":"+t:"";url=window.location.href,url=window.location.protocol+"//"+window.location.hostname+t+window.location.pathname;var e=new URLSearchParams;e.set("subreddit",this.subreddit),"score"===this.filterType?e.set("score",this.score):"percentage"===this.filterType?e.set("threshold",this.percentage):"postsPerDay"===this.filterType&&e.set("averagePostsPerDay",this.postsPerDay),this.includeComments&&e.set("comments",this.includedComments),url=url+"?"+e,this.generatedRssUrl=url+"&view=rss",window.history.pushState({},window.title,"?"+e)},formSubmit:function(t){document.getElementById("post-list").classList.add("loading"),document.getElementById("submit").classList.add("opacity-50","cursor-not-allowed"),document.getElementById("submit").innerHTML="Loading posts…",document.getElementById("post-list").classList.remove("notices","warning","info"),document.querySelectorAll("#post-list .notice").forEach(function(t){t.remove()}),this.buildURL(),window.axios.post("postlist.php",{subreddit:this.subreddit,filterType:this.filterType,score:this.score,threshold:this.percentage,averagePostsPerDay:this.postsPerDay}).then(function(t){document.getElementById("post-list").classList.remove("loading"),document.getElementById("submit").classList.remove("opacity-50","cursor-not-allowed"),document.getElementById("submit").innerHTML="Submit",this.posts=t.data.postList,t.data.subredditValid?t.data.postList.length?(this.cacheSize=t.data.cacheSize,this.computedScore=t.data.thresholdScore):(document.getElementById("post-list").classList.add("notices","info"),document.getElementById("post-list").innerHTML='<div class="notice">No hot posts in <strong>/r/'+this.subreddit+"</strong> match the filters</div>"):(console.log("subreddit is not valid"),document.getElementById("post-list").classList.add("notices","warning"),document.getElementById("post-list").innerHTML='<div class="notice"><strong>/r/'+this.subreddit+"</strong> is not a valid subreddit</div>")}.bind(this)).catch(function(t){console.log(t),document.getElementById("submit").classList.remove("opacity-50","cursor-not-allowed"),document.getElementById("submit").innerHTML="Submit"})}},watch:{subreddit:debounce(function(){this.formSubmit()},500),filterType:debounce(function(){this.formSubmit()},0),score:debounce(function(){this.formSubmit()},500),percentage:debounce(function(){this.formSubmit()},500),postsPerDay:debounce(function(){this.formSubmit()},500),includeComments:debounce(function(){this.buildURL()},0),includedComments:debounce(function(){this.buildURL()},500)},mounted:function(){this.formSubmit()}});