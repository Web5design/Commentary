"use strict"

Popcorn.getScript( "http://mustache.github.com/extras/mustache.js");

Popcorn(function() {


	var popcornDiv = document.getElementById('popcorn');

	if (popcornDiv == null) {
		console.log("I know when I'm not needed.");
		return;
	}

	// Feed the video to popcorn

	var videoURL = popcornDiv.getAttribute('data-url');
	var pop = Popcorn.vimeo( "#popcorn", videoURL );

	// Make a div for commentary

	var commentaryDiv = document.createElement('div');
	commentaryDiv.setAttribute("id", "commentary");
	popcornDiv.parentNode.insertBefore(commentaryDiv, popcornDiv.nextSibling);

	// Setup commentary

	pop.commentary({
		start: 0,
		target: "commentary",
		commentsURL: commentaryAjax.commentsURL,
		commentsData: commentaryAjax.commentsData,
		commentPostURL: commentaryAjax.commentPostURL,
		commentPostData: commentaryAjax.commentPostData,
		commentsTemplateURL: commentaryAjax.commentsTemplateURL,
		formTemplateURL: commentaryAjax.formTemplateURL,
	});
});

(function (Popcorn) {  
 Popcorn.plugin( "commentary" , function( options ) {

	var popcornObject = this;

	var commentaryDiv = document.getElementById( options.target );

	var commentData, commentsTemplate = null;
	var commentHTML = "";
	var commentsDiv;

	var formTemplate;
	var formDiv;

	var lastIndex = 0; 

	// Create divs

	var commentsDiv = document.createElement('div');
	commentsDiv.setAttribute("id", "commentary-comments");
	commentaryDiv.insertBefore(commentsDiv,commentaryDiv.firstChild);

	var formDiv = document.createElement('div');
	formDiv.setAttribute("id", "commentary-form");
	commentaryDiv.insertBefore(formDiv,commentsDiv.nextSibling);

	// External requests

	Popcorn.xhr({
		url: options.commentsTemplateURL,
		dataType: 'text',
		success: function(data) {
			commentsTemplate = data;
			dataReady();
		}
	});

	function loadCommentData() {

		Popcorn.xhr({
			type: "POST" ,
			url: options.commentsURL,
			data: options.commentsData,
			dataType: 'json',
			success: function(data) {
				commentData = data;
				dataReady();
			}
		});
	}
	
	loadCommentData();

	function dataReady() {
		if (commentData != null && commentsTemplate != null) {
			commentHTML = Mustache.to_html(commentsTemplate, commentData);
			commentsDiv.innerHTML = commentHTML;
		}	
	}

	Popcorn.xhr({
		url: options.formTemplateURL,
		dataType: 'text',
		success: function(data) {
			formDiv.innerHTML = data;

			document.getElementById("commentary-save").onclick = function() {

				var formAuthor = document.getElementById('commentary-author').value;
				var formComment = document.getElementById('commentary-comment').value;
				var formEmail = document.getElementById('commentary-email').value;

				var formTimestamp = popcornObject.currentTime();

				Popcorn.xhr({
					type: "POST" ,
					url: options.commentPostURL,
					data: "author=" + formAuthor +
						"&comment=" + formComment + 
						"&email=" + formEmail + 
						"&commentary_timestamp=" + formTimestamp + 
						options.commentPostData,
					dataType: 'json',
					success: function(data) {
						loadCommentData();
					}
				});

			};
		}
	});

	this.on('timeupdate', function() {

		// Insanely inefficient

		var commentIndex = 0;
		var comments = commentsDiv.getElementsByTagName("div");

		for(commentIndex=lastIndex; commentIndex < comments.length; commentIndex++) {
			comments[commentIndex].style.background = "";
			var timestamp = comments[commentIndex].getAttribute("data-timestamp");
			if (this.currentTime() <= timestamp) break;
		}

		if (commentIndex > 0) commentIndex -= 1;

		comments[commentIndex].style.background = "#99ccff";

		if (commentIndex == lastIndex) return;

		lastIndex = commentIndex;
		var commentPosition = comments[commentIndex].offsetTop - commentsDiv.offsetTop -
			commentsDiv.clientHeight /2 + comments[commentIndex].clientHeight /2 ;
		commentsDiv.scrollTop = commentPosition;

	});

	return {
		start: function(){
			console.log('go team');
		},
		end: function(){
			console.log('stop team');
		}
	};
});
})(Popcorn);


jQuery('document').ready( function() {

	jQuery('#commentform').submit( function(e) {

		e.preventDefault();

		// Set timestamp field

		jQuery('#commentary_timestamp').val(timestamp);

		// Get form action

		var action = jQuery('#commentform').attr('action');
		jQuery.post(action, jQuery("#commentform").serialize());

	});

});
