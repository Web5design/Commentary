<?php
/**
 * @package Commentary
 */

// Return comment data
//

add_action( 'wp_ajax_nopriv_commentary', 'json_comments' );
add_action( 'wp_ajax_commentary', 'json_comments' );
 
function json_comments() {
	// get the submitted parameters
	$postID = $_REQUEST['postid'];

	$comments = get_comments('post_id='.$postID);

	foreach($comments as $comment) {
		$comment->timestamp = get_comment_meta($comment->comment_ID, 'commentary_timestamp', true);
	}

	usort($comments, "comment_sort");	

	header( "Content-Type: application/json" );
	echo json_encode(array("comments" => $comments));
 
	// IMPORTANT: don't forget to "exit"
	exit;
}

function comment_sort($a, $b) {
    if ($a->timestamp == $b->timestamp) {
        return 0;
    }
    return ($a->timestamp < $b->timestamp) ? -1 : 1;	
}

// Save timestamp field

add_action( 'comment_post', 'commentary_comment_post', 10, 1 );

function commentary_comment_post( $comment_id ) {
    if( isset( $_POST['commentary_timestamp'] ) )
        update_comment_meta( $comment_id, 'commentary_timestamp', esc_attr( $_POST['commentary_timestamp'] ) );
}

?>
