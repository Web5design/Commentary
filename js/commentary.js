var timestamp = 0;

var iframe, player;


(function (Popcorn) {  
 Popcorn.plugin( "commentary" , function( options ) {

	var commentData, commentTemplate = null;
	var commentHTML;
	var commentDiv = document.getElementById( options.target );

	console.log(commentDiv);

	Popcorn.getScript( "http://mustache.github.com/extras/mustache.js");

	Popcorn.xhr({
		url: options.template,
		dataType: 'text',
		success: function(data) {
			commentTemplate = data;
			checkReady();
		}
	});

	Popcorn.xhr({
		type: "GET" ,
		url: commentaryAjax.url,
		data: "action=commentary" +
			"&postid=" + commentaryAjax.postid +
			"&nonce=" + commentaryAjax.nonce,
		dataType: 'json',
		success: function(data) {
			commentData = data;
			checkReady();
		}
	});

	this.on('timeupdate', function() {

		// Insanely inefficient

		var commentIndex = 0;
		var comments = commentDiv.getElementsByTagName("div");

		for(commentIndex=0; commentIndex < comments.length; commentIndex++) {
			comments[commentIndex].style.background = "";
			var timestamp = comments[commentIndex].getAttribute("data-timestamp");
			if (this.currentTime() <= timestamp) break;
		}

		if (commentIndex > 0) commentIndex -= 1;

		comments[commentIndex].style.background = "#99ccff";
		var commentPosition = comments[commentIndex].offsetTop - commentDiv.offsetTop;
		commentDiv.scrollTop = commentPosition;

	});

	function checkReady() {
		if (commentData != null && commentTemplate != null) {
			console.log(commentData);
			commentHTML = Mustache.to_html(commentTemplate, commentData);
			commentDiv.innerHTML = commentHTML;
		}	
	}

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

	if (jQuery('#commentary_player').length > 0) {
		iframe = jQuery('#commentary_player')[0];
		player = $f(iframe);

		player.addEvent('ready', function() {
			player.addEvent('playProgress', onPlayProgress);
		});

	}

	if (jQuery("#popcorn").length > 0) {

		var videoURL = jQuery("#popcorn").attr('data-url');

		var commentaryDiv = jQuery('<div/>').attr({'id':'commentary'});
		jQuery('#popcorn').after(commentaryDiv);

		var pop = Popcorn.vimeo( "#popcorn", videoURL );

		pop.commentary({
			start: 0,
			template: commentaryAjax.template,
			target: "commentary"
		});

		pop.on( "timeupdate", function() {
		    timestamp = this.currentTime();
		});


		//var dataDisplay = jQuery('<div/>').attr({'id':'dataDisplay'}).css({'height':100});
		//jQuery('#popcorn').after(dataDisplay);

		//pop.footnote({
		//	start: 2,
		//	end: 5,
		//	target: "dataDisplay",
		//	text: "Somebody should put some comments here..."
		//});

	}

	jQuery('#commentform').submit( function(e) {

		e.preventDefault();

		// Set timestamp field

		jQuery('#commentary_timestamp').val(timestamp);

		// Get form action

		var action = jQuery('#commentform').attr('action');
		jQuery.post(action, jQuery("#commentform").serialize());

	});

});

function fetchComments() {

	var commentData;

	jQuery.ajax({
		type: "post" ,
		url: commentaryAjax.url,
		data: {
			action: "commentary",
			postid: commentaryAjax.postid,
			nonce: commentaryAjax.nonce
		},
		dataType: 'json',
		async: false,
		error: function(error) { 
			console.log(error);
		},
		success: function(data) {
			console.log(data);
		}
	});

}

function onPlayProgress(data, id) {
	timestamp = data.seconds;
}
