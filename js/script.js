// Clear form values if one is already set and show filled field
function showActiveField() {
	$(".filter-group input").each(function(){
		if($(this).val().length) {
			$("select[name=filterType]").val($(this).attr("name"));
			$(this).closest(".filter-group").removeClass("d-none");
			$(this).focus();
		}
	});
	if (!$(".filter-group input").filter(function() { return $(this).val(); }).length > 0) {
		$("#score-group").removeClass("d-none");
		$("input[name=subreddit]").focus();
	}
}


// Submit the form
function formSubmit() {
	$("form").submit(function(e) {
		e.preventDefault();
		$(this).find("button[type=submit]").attr("disabled", "disabled").text("Fetching posts…");
		$("#post-list").addClass("processing");
		$("#post-list").prepend("<div class='lds-ellipsis'><div></div><div></div><div></div><div></div></div>");
		// Plug subreddit name from URL parameter into subreddit field
		// If subreddit URL parameter doesn't exist, set field to defined default subreddit
		function getUrlVars() {
			var vars = {};
			var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
				vars[key] = value;
			});
			return vars;
		}
		if(!$("input[name=subreddit]").val().length && getUrlVars()["subreddit"]) {
			$("input[name=subreddit]").val(getUrlVars()["subreddit"]);
		} else if(!$("input[name=subreddit]").val().length) {
			$("input[name=subreddit]").val($("#post-list").attr("data-default-subreddit"));
		}
		// If no filters are set, enable the score filter
		if (!$(".filter-group input").filter(function() { return $(this).val(); }).length > 0) {
			$("input[name=score]").removeAttr("disabled");
		}
		// Push form values to history
		var uri = window.location.toString();
		if (uri.indexOf("?") > 0) {
			var uri = uri.substring(0, uri.indexOf("?"));
		}
		var uri = uri + "?subreddit=" + $("input[name=subreddit]").val();
		$(".filter-group input").each(function(){
			if($(this).val().length) {
				uri = uri + "&" + $(this).attr("name") + "=" +	$(this).val();
			}
		});
		window.history.pushState({}, document.title, uri);
		// Set jumbotron title to value from subreddit field
		$("h1.page-title span.subreddit").text($("input[name=subreddit]").val());
		// Set RSS badge URL
		$("a.rss-badge").attr("href", uri + "&view=rss").tooltip("hide").attr("data-original-title", uri + "&view=rss");
		// Return filtered posts on submit
		$.ajax( {
			url: "postlist.php",
			data: {
				subreddit : $("input[name=subreddit]").val(),
				score : $("input[name=score]").val(),
				threshold : $("input[name=threshold]").val(),
				averagePostsPerDay : $("input[name=averagePostsPerDay]").val(),
			},
			success: function (result) {
				$("#post-list").html(result);
				$("form button[type=submit]").removeAttr("disabled").text("Submit");
				$("#post-list").removeClass("processing");
				$("#post-list .lds-ellipsis").remove();
				$(".clear-cache span").text($(".cache-size").attr("data-cache-size"));
				$("a.media img").each(function(){
					$(this).attr("src", $(this).attr("data-src"));
				});
			},
		});
	});
}


// Select filter type
$("select[name=filterType]").on("change", function() {
	$(".filter-group").addClass("d-none").find("input").val("").removeAttr("disabled");
	$("#" + this.value + "-group").removeClass("d-none").find("input").focus();
});


// Clear cached files
$(".clear-cache button").on("click", function() {
	$.ajax( {
		url: "cache-clear.php",
		success: function () {
			$(".clear-cache button span").text("0 B");
			$("form").submit();
			$("html, body").animate({ scrollTop: 0 }, 400);
		},
	});
});


$(document).ready(function() {
	$("[data-toggle='tooltip']").tooltip();
	showActiveField();
	formSubmit();
	$("form").submit();
});
