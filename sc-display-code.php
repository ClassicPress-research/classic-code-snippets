<?php
/**
 * Plugin Name: Classic Display Code
 * Plugin URI: https://github.com/classicpress-research/classic-code-snippet
 * Description: A small plugin to generate a shortcode to display code on a page.
 * Version: 1.0.0
 * Author: ClassicPress Research Team
 * Author URI: https://github.com/classicpress-research/classic-code-snippet
 * License: GPL2
 * text-domain: ccs-code-snippet
 * 
 * Credits: Simply Computing (https://simplycomputing.com.au)
 * Classic Code Snippets is the new name for SC Display Code (sc-display-code) originally developed by Alan and Jack Coggins at Simply Computing.
 */

// Basic Security.
defined( 'ABSPATH' ) or die;

/**
 * Add display highlighter and line number styling to head section.
 *
 * @return void
 */
function sc_add_display_highlighter() { 
	?>
		<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/styles/default.min.css">
		<script src="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/highlight.min.js"></script>
		<script>hljs.initHighlightingOnLoad();</script>
		<script src="//cdn.jsdelivr.net/npm/highlightjs-line-numbers.js@2.8.0/dist/highlightjs-line-numbers.min.js"></script>
		<script>hljs.initLineNumbersOnLoad();</script>
	<?php
}
add_action( 'wp_head', 'sc_add_display_highlighter' );

/**
 * Create custom post type named "code".
 *
 * @return void
 */
function sc_my_post_type () {

	$labels = array(
		'name'                  => __( 'Code Snippets', 'ccs-code-snippet' ),
		'singular_name'         => __( 'Code Snippet', 'ccs-code-snippet' ),
        'menu_name'             => __( 'Code Snippets', 'ccs-code-snippet' ),
        'name_admin_bar'        => __( 'Code Snippet', 'ccs-code-snippet' ),
        'add_new'               => __( 'Add New', 'ccs-code-snippet' ),
        'add_new_item'          => __( 'Add New Code Snippet', 'ccs-code-snippet' ),
        'new_item'              => __( 'New Code Snippet', 'ccs-code-snippet' ),
        'edit_item'             => __( 'Edit Code Snippet', 'ccs-code-snippet' ),
        'view_item'             => __( 'View Code Snippet', 'ccs-code-snippet' ),
        'all_items'             => __( 'All Code Snippets', 'ccs-code-snippet' ),
        'search_items'          => __( 'Search Code Snippets', 'ccs-code-snippet' ),
        'parent_item_colon'     => __( 'Parent Code Snippets:', 'ccs-code-snippet' ),
        'not_found'             => __( 'No Code Snippets found.', 'ccs-code-snippet' ),
        'not_found_in_trash'    => __( 'No Code Snippets found in Trash.', 'ccs-code-snippet' ),
        'featured_image'        => __( 'Code Snippet Cover Image', 'ccs-code-snippet' ),
        'set_featured_image'    => __( 'Set cover image', 'ccs-code-snippet' ),
        'archives'              => __( 'Code Snippet archives', 'ccs-code-snippet' ),
        'filter_items_list'     => __( 'Filter Code Snippets list', 'ccs-code-snippet' ),
        'items_list_navigation' => __( 'Code Snippets list navigation', 'ccs-code-snippet' ),
        'items_list'            => __( 'Code Snippets list', 'ccs-code-snippet' ),
	);

	register_post_type(
		'sc_code_snippet',
		array(
			'labels'       => $labels,
			'public'       => true,
			'menu_icon'    => 'dashicons-editor-code',
			'show_in_menu' => true,
			'supports'     => 'title',
		)
	);
}
add_action( 'init', 'sc_my_post_type' );

/**
 * Register Code Snippet metabox.
 *
 * @return void
 */
function sc_register_meta_boxes() {
	add_meta_box(
		'hcf-1',
		__( 'Code Snippet', 'ccs-code-snippet' ),
		'sc_display_callback',
		'sc_code_snippet'
	);
}
add_action( 'add_meta_boxes', 'sc_register_meta_boxes' );

/**
 * Metabox callback with HTML.
 *
 * @param mixed $post WP_Post Post object.
 * @return void
 */
function sc_display_callback( $post ) {
	include plugin_dir_path( __FILE__ ) . './form.php';
}

/**
 * Save custom field data.
 *
 * @return void
 */
function save_custom_fields(){
	global $post;
	if ( $post ) {
		update_post_meta( $post->ID, "sc_code_snippet", @$_POST["sc_code_snippet"] );
	}
}
add_action( 'save_post', 'save_custom_fields' );

/**
 * Add shortcode to display html code.
 * Requires passing in the ID of the code snippet when posting in a page or post.
 *
 * @param mixed $atts Attributes from the shortcode. 
 * @return void
 */
function shortcode_function($atts) {

	// Get the value of the post meta with name `_my_post_meta_name`
	$post_id         = $atts['id'];
	$post_meta_value = get_post_meta( $post_id, 'sc_code_snippet', true );
	$post_url        = get_permalink( $post_id );

	if ( is_user_logged_in() ) {
		$codeID = '<a href="/wp-admin/post.php?post=' . $post_id . '&action=edit"> Code ID: ' . $post_id . '</a>';
	} else {
		$codeID = 'Code ID: ' . $post_id;
	}

	// Print the post meta value with buttons and ID info/link
	$myVar = htmlspecialchars($post_meta_value, ENT_QUOTES); 

	return '
	<pre>
		<code class="language-php">' . $myVar  . '</code>
	</pre>
	<button id="copy-button" class="cc-button" onclick="copyToClipboard()">Copy to Clipboard</button>
	<a href="/wp-content/downloads/code/' . $post_id . '.txt" class="cc-button">View Raw</a>
	<span style="float:right;color:#999;"><small>' . $codeID . '</small></span>
	<script>
		var copyButton = document.getElementById("copy-button");
		function copyToClipboard () {
			var clipText = `' . $post_meta_value . '`;
			navigator.clipboard.writeText(clipText).then(function() {
				console.log("Copied to clipboard.");
				copyButton.innerHTML = "Copied";
			}, function() {
				console.log("Copy failed.");
				copyButton.innerHTML = "Copy Failed";
			});
		}
	</script>';
}
add_shortcode('snippet', 'shortcode_function');

/**
 * Write a copy of the code snippet to a text file on server.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function create_code_text_file( $post_id ) {
	if ( get_post_meta( $post_id, 'sc_code_snippet', true ) ) {
		$post_meta_value = get_post_meta( $post_id, 'sc_code_snippet', true );
		$handle          = fopen( "/home/classicc/public_html/wp-content/downloads/code/" . $post_id . ".txt", "w+" );
		
		fwrite( $handle, $post_meta_value );
		fclose( $handle );
	}
}
add_action( 'save_post', 'create_code_text_file' );
