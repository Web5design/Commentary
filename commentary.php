<?php
/**
 * @package Commentary
 */

/*
Plugin Name: Commentary
Plugin URI: http://github.com/scottgarner/Commentary
Description: Video annotation support
Version: 0.1
Author: Scott Garner
Author URI: http://scott.j38.net/
License: MIT
*/


include_once dirname( __FILE__ ) . '/commentary-admin.php';
include_once dirname( __FILE__ ) . '/commentary-ajax.php';

// Javascript and Style 

add_action( 'wp_enqueue_scripts', 'commentary_scripts' );

function commentary_scripts()
{
	global $wp_query;

	//wp_enqueue_script( 'froogaloop', 'http://a.vimeocdn.com/js/froogaloop2.min.js');
	wp_enqueue_script('popcorn', plugins_url('/js/popcorn.js', __FILE__ ));
	wp_enqueue_script( 'commentary', plugins_url( '/js/commentary.js', __FILE__ ), array('jquery') );

	// Pass variables

	wp_localize_script('commentary', 'commentaryAjax', array(
		'url' => admin_url( 'admin-ajax.php' ),
		'template' => plugins_url( '/templates/commentary.mustache', __FILE__ ),
		'postid' =>  $wp_query->post->ID,
		'nonce' => wp_create_nonce( 'commentary-nonce' )
	));

	// Styles

	wp_register_style( 'commentary-css', plugins_url( '/css/commentary.css', __FILE__ ), array(),
		'20130214', 'all' );
	wp_enqueue_style( 'commentary-css' );
}




////
// Post Section
//

add_filter('the_content', 'commentary_content');

function commentary_content($content) {
	global $post;

	$commentary_vimeo_url = get_post_meta( $post->ID, $key = 'commentary_vimeo_url', $single = true );
	$commentary = <<<VIDEOLOG

	<div id="popcorn" data-url="$commentary_vimeo_url"></div>

VIDEOLOG;

	return $commentary.$content;
}


// Add timestamp field to comment form

add_action( 'comment_form_logged_in_after', 'commentary_comment_fields' );
add_action( 'comment_form_after_fields', 'commentary_comment_fields' );

function commentary_comment_fields() {
?>
	<input type="hidden" id="commentary_timestamp" name="commentary_timestamp" value = "0"/>
<?
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


/*

Using a meta field instead.

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

*/

?>
