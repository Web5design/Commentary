<?php
/**
 * @package VideoLog
 */
/*
<?php
/*
Plugin Name: VideoLog
Plugin URI: http://scott.j38.net/
Description: A brief description of the Plugin.
Version: 0.1
Author: Scott Garner
Author URI: http://scott.j38.net
License: Free like an eagle
*/


////
// Admin Section
//

// Register the meta box

add_action( 'add_meta_boxes', 'videolog_meta_boxes' );

function videolog_meta_boxes() {
	add_meta_box(
			'videolog_meta_box',	// this is HTML id of the box on edit screen
			'VideoLog',		// title of the box
			'videolog_callback',	// function to be called to display the checkboxes, see the function below
			'post',			// on which edit screen the box should appear
			'normal',		// part of page where the box should appear
			'default'		// priority of the box
		    );
}

// Display the meta box
function videolog_callback( $post, $metabox ) {
	// nonce field for security check, you can have the same
	// nonce field for all your meta boxes of same plugin
	wp_nonce_field( plugin_basename( __FILE__ ), 'videolog_nonce' );

	$value = get_post_meta( $post->ID, $key = 'videolog_vimeo_url', $single = true );
?>		
	<label>Vimeo URL</label>
	<input type="text" name="videolog_vimeo_url" value="<?=$value?>" />
<?
}

// Save data from meta box

add_action( 'save_post', 'videolog_save_post' );

function videolog_save_post( $post_id) {

    // Check for autosave 

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // Security check

    if ( !wp_verify_nonce( $_POST['videolog_nonce'], plugin_basename( __FILE__ ) ) ) return;

    // Store data

    $data = sanitize_text_field( $_POST['videolog_vimeo_url'] );
    update_post_meta( $post_id, 'videolog_vimeo_url',  $data);
}


////
// Post Section
//

add_filter('the_content', 'videolog_content');

function videolog_content($content) {
	global $post;

	$videolog_vimeo_url = get_post_meta( $post->ID, $key = 'videolog_vimeo_url', $single = true );
	$videolog = <<<VIDEOLOG

	<div>$videolog_vimeo_url</div>

VIDEOLOG;

	return $videolog.'\n'.$content;
}

// Add to web comment form

add_action( 'comment_form_logged_in_after', 'videolog_comment_fields' );
add_action( 'comment_form_after_fields', 'videolog_comment_fields' );
function videolog_comment_fields() {
?>
	<input type="hidden" id="videolog_timestamp" name="videolog_timestamp" value = "0"/>
<?
}

add_action( 'comment_post', 'videolog_comment_post', 10, 1 );
function videolog_comment_post( $comment_id ) {
    if( isset( $_POST['videolog_timestamp'] ) )
        update_comment_meta( $comment_id, 'videolog_timestamp', esc_attr( $_POST['videolog_timestamp'] ) );
}

// Add to admin comment form

add_action( 'add_meta_boxes_comment', 'videolog_meta_boxes_comment' );
function videolog_meta_boxes_comment()
{
    add_meta_box( 'videolog_comment_meta_box',
	'Video Timestamp',
	'videolog_comment_callback', 'comment', 'normal', 'high' );
}

function videolog_comment_callback( $comment )
{
    $timestamp = get_comment_meta( $comment->comment_ID, 'videolog_timestamp', true );
	wp_nonce_field( plugin_basename( __FILE__ ), 'videolog_comment_nonce' );
    ?>
    <p>
        <label for="videolog_timestamp">Video Timestamp</label>
        <input type="text" name="videolog_timestamp" value="<?php echo esc_attr( $timestamp ); ?>" class="widefat" />
    </p>

<?
}

add_action( 'edit_comment', 'videolog_edit_comment' );
function videolog_edit_comment( $comment_id )
{
    if ( !wp_verify_nonce( $_POST['videolog_comment_nonce'], plugin_basename( __FILE__ ) ) ) return;

    if( isset( $_POST['videolog_timestamp'] ) )
        update_comment_meta( $comment_id, 'videolog_timestamp', esc_attr( $_POST['videolog_timestamp'] ) );
}

// Add to comment display

add_filter( 'comment_text', 'videolog_comment_text', 99, 2 );
function videolog_comment_text( $text, $comment )
{
    if( $timestamp = get_comment_meta( $comment->comment_ID, 'videolog_timestamp', true ) )
    {
        $timestamp = '<h3>' . esc_attr( $timestamp ) . '</h3>';
        $text = $timestamp . $text;
    }
    return $text;
}

// Comments javascript

function videolog_scripts()
{
	wp_enqueue_script( 'froogaloop', 'http://a.vimeocdn.com/js/froogaloop2.min.js');
	wp_enqueue_script( 'videolog', plugins_url( '/js/videolog.js', __FILE__ ), array('jquery') );
}
add_action( 'wp_enqueue_scripts', 'videolog_scripts' );


?>

