<?php
/**
 * @package Commentary
 */

////
// Posts
//


// Register the meta box, possible alternative to oembed

add_action( 'add_meta_boxes', 'commentary_meta_boxes' );

function commentary_meta_boxes() {
	add_meta_box(
			'commentary_meta_box',	// this is HTML id of the box on edit screen
			'Commentary',		// title of the box
			'commentary_callback',	// function to be called to display the checkboxes, see the function below
			'post',			// on which edit screen the box should appear
			'normal',		// part of page where the box should appear
			'default'		// priority of the box
		    );
}

// Display the meta box

function commentary_callback( $post, $metabox ) {
	// nonce field for security check, you can have the same
	// nonce field for all your meta boxes of same plugin
	wp_nonce_field( plugin_basename( __FILE__ ), 'commentary_nonce' );

	$value = get_post_meta( $post->ID, $key = 'commentary_vimeo_url', $single = true );
?>		
	<label>Vimeo URL</label>
	<input type="text" name="commentary_vimeo_url" value="<?=$value?>" />
<?
}

// Save data from meta box

add_action( 'save_post', 'commentary_save_post' );

function commentary_save_post( $post_id) {

    // Check for autosave 

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // Security check

    if ( !wp_verify_nonce( $_POST['commentary_nonce'], plugin_basename( __FILE__ ) ) ) return;

    // Store data

    $data = sanitize_text_field( $_POST['commentary_vimeo_url'] );
    update_post_meta( $post_id, 'commentary_vimeo_url',  $data);
}


///////////
// Comments


// Add to admin comment form

add_action( 'add_meta_boxes_comment', 'commentary_meta_boxes_comment' );

function commentary_meta_boxes_comment()
{
    add_meta_box( 'commentary_comment_meta_box',
	'Video Timestamp',
	'commentary_comment_callback', 'comment', 'normal', 'high' );
}

function commentary_comment_callback( $comment )
{
    $timestamp = get_comment_meta( $comment->comment_ID, 'commentary_timestamp', true );
	wp_nonce_field( plugin_basename( __FILE__ ), 'commentary_comment_nonce' );
    ?>
    <p>
        <label for="commentary_timestamp">Video Timestamp</label>
        <input type="text" name="commentary_timestamp" value="<?php echo esc_attr( $timestamp ); ?>" class="widefat" />
    </p>

<?
}

// Save in admin form

add_action( 'edit_comment', 'commentary_edit_comment' );

function commentary_edit_comment( $comment_id )
{
    if ( !wp_verify_nonce( $_POST['commentary_comment_nonce'], plugin_basename( __FILE__ ) ) ) return;

    if( isset( $_POST['commentary_timestamp'] ) )
        update_comment_meta( $comment_id, 'commentary_timestamp', esc_attr( $_POST['commentary_timestamp'] ) );
}

?>
