<?php
/**
 * @package Commentary
 */
/*

/*
Plugin Name: Commentary
Plugin URI: http://github.com/scottgarner/Commentary
Description: Video annotation support
Version: 0.1
Author: Scott Garner
Author URI: http://scott.j38.net/
License: MIT
*/


////
// Admin Section
//

/* Tests for adding a field instead of using oembed

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

*/

////
// Post Section
//

/* Add data from admit field, oembed seems better

add_filter('the_content', 'commentary_content');

function commentary_content($content) {
	global $post;

	$commentary_vimeo_url = get_post_meta( $post->ID, $key = 'commentary_vimeo_url', $single = true );
	$commentary = <<<VIDEOLOG

	<div>$commentary_vimeo_url</div>

VIDEOLOG;

	return $commentary.$content;
}

*/

// Add timestamp field to comment form

add_action( 'comment_form_logged_in_after', 'commentary_comment_fields' );
add_action( 'comment_form_after_fields', 'commentary_comment_fields' );

function commentary_comment_fields() {
?>
	<input type="hidden" id="commentary_timestamp" name="commentary_timestamp" value = "0"/>
<?
}

// Save comment data

add_action( 'comment_post', 'commentary_comment_post', 10, 1 );

function commentary_comment_post( $comment_id ) {
    if( isset( $_POST['commentary_timestamp'] ) )
        update_comment_meta( $comment_id, 'commentary_timestamp', esc_attr( $_POST['commentary_timestamp'] ) );
}

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

// Add to comment display

add_filter( 'comment_text', 'commentary_comment_text', 99, 2 );

function commentary_comment_text( $text, $comment )
{

    $timestamp = get_comment_meta( $comment->comment_ID, 'commentary_timestamp', true );
    if( trim($timestamp) != "" )
    {
        $timestamp = '<h3>Timestamp: ' . esc_attr( $timestamp ) . '</h3>';
        $text = $timestamp . $text;
    }
    return $text;
}

// Comments javascript

add_action( 'wp_enqueue_scripts', 'commentary_scripts' );

function commentary_scripts()
{
	wp_enqueue_script( 'froogaloop', 'http://a.vimeocdn.com/js/froogaloop2.min.js');
	wp_enqueue_script( 'commentary', plugins_url( '/js/commentary.js', __FILE__ ), array('jquery') );
}

////
// Oembed routines
//

// Change oembed url

add_filter( 'oembed_fetch_url', 'commentary_fetch_url', 10, 3);

function commentary_fetch_url(  $provider, $url, $args ) {

	if ( 'vimeo.com' == parse_url( $url, PHP_URL_HOST ) ) {
		$provider = add_query_arg( 'api', urlencode( 1 ), $provider );
		$provider = add_query_arg( 'player_id', urlencode( "commentary_player" ), $provider );
	}

	return $provider;
}

// Alter oembed results

add_filter('oembed_result','commentary_oembed_result',10,3);

function commentary_oembed_result($html, $url, $args) {
	if ( 'vimeo.com' == parse_url( $url, PHP_URL_HOST ) ) {
                $html = str_replace('iframe','iframe id="commentary_player"',$html);
        }
        return $html;
}

?>

