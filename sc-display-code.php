<?php
/**
 * Plugin Name: SC Display Code
 * Plugin URI: https://simplycomputing.com.au
 * Description: A small plugin to generate a shortcode to display code on a page.
 * Version: 1.0.0
 * Author: Simply Computing
 * Author URI: https://simplycomputing.com.au
 * License: GPL2
 * text-domain: sc-display-code
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
		'name'                  => _x( 'Code Snippets', 'Post type general name', 'sc-display-code' ),
        'singular_name'         => _x( 'Code Snippet', 'Post type singular name', 'sc-display-code' ),
        'menu_name'             => _x( 'Code Snippets', 'Admin Menu text', 'sc-display-code' ),
        'name_admin_bar'        => _x( 'Code Snippet', 'Add New on Toolbar', 'sc-display-code' ),
        'add_new'               => __( 'Add New', 'sc-display-code' ),
        'add_new_item'          => __( 'Add New Code Snippet', 'sc-display-code' ),
        'new_item'              => __( 'New Code Snippet', 'sc-display-code' ),
        'edit_item'             => __( 'Edit Code Snippet', 'sc-display-code' ),
        'view_item'             => __( 'View Code Snippet', 'sc-display-code' ),
        'all_items'             => __( 'All Code Snippets', 'sc-display-code' ),
        'search_items'          => __( 'Search Code Snippets', 'sc-display-code' ),
        'parent_item_colon'     => __( 'Parent Code Snippets:', 'sc-display-code' ),
        'not_found'             => __( 'No Code Snippets found.', 'sc-display-code' ),
        'not_found_in_trash'    => __( 'No Code Snippets found in Trash.', 'sc-display-code' ),
        'featured_image'        => _x( 'Code Snippet Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'sc-display-code' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'sc-display-code' ),
        'archives'              => _x( 'Code Snippet archives', 'The post type archive label used in nav menus. Default "Post Archives".', 'sc-display-code' ),
        'filter_items_list'     => _x( 'Filter Code Snippets list', 'Screen reader text for the filter links heading on the post type listing screen. Default "Filter posts list"/"Filter pages list".', 'sc-display-code' ),
        'items_list_navigation' => _x( 'Code Snippets list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default "Posts list navigation"/"Pages list navigation".', 'sc-display-code' ),
        'items_list'            => _x( 'Code Snippets list', 'Screen reader text for the items list heading on the post type listing screen. Default "Posts list"/"Pages list".', 'sc-display-code' ),
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
		__( 'Code Snippet', 'sc-display-code' ),
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
	<pre><code class="language-php">' . $myVar  . '</code></pre>
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
