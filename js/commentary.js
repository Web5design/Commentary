var timestamp = 0;

var iframe, player;

jQuery('document').ready( function() {

	iframe = jQuery('#commentary_player')[0];
	player = $f(iframe);

	player.addEvent('ready', function() {
	console.log('yeah');
		player.addEvent('playProgress', onPlayProgress);
	});

	jQuery('#commentform').submit( function() {
		jQuery('#commentary_timestamp').val(timestamp);
	});
});


function onPlayProgress(data, id) {
	timestamp = data.seconds;
}
