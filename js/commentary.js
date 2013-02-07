var timestamp = 0;

var iframe, player;

jQuery('document').ready( function() {

	if (jQuery('#commentary_player').length > 0) {
		iframe = jQuery('#commentary_player')[0];
		player = $f(iframe);

		player.addEvent('ready', function() {
		console.log('yeah');
			player.addEvent('playProgress', onPlayProgress);
		});

	}

	if (jQuery("#popcorn").length > 0) {

		var videoURL = jQuery("#popcorn").attr('data-url');

		var pop = Popcorn.vimeo( "#popcorn", videoURL );

		pop.on( "timeupdate", function() {
		    timestamp = this.currentTime();
		});

		var dataDisplay = jQuery('<div/>').attr({'id':'dataDisplay'}).css({'height':100});
		jQuery('#popcorn').after(dataDisplay);

		pop.footnote({
			start: 2,
			end: 5,
			target: "dataDisplay",
			text: "Somebody should put some comments here..."
		});

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


function onPlayProgress(data, id) {
	timestamp = data.seconds;
}
