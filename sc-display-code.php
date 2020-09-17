<?php
/**
 * Plugin Name: SC Display Code
 * Plugin URI: https://simplycomputing.com.au
 * Description: A small plugin to generate a shortcode to display code on a page.
 * Version: 1.0.0
 * Author: Simply Computing
 * Author URI: https://simplycomputing.com.au
 * License: GPL2
 */


/**
 * Add display highlighter and line number styling to head section
 */

	add_action( 'wp_head', 'sc_add_display_highlighter' );

	function sc_add_display_highlighter() { ?>

		<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/styles/default.min.css">
		<script src="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/highlight.min.js"></script>
		<script>hljs.initHighlightingOnLoad();</script>
		<script src="//cdn.jsdelivr.net/npm/highlightjs-line-numbers.js@2.8.0/dist/highlightjs-line-numbers.min.js"></script>
		<script>hljs.initLineNumbersOnLoad();</script>
		<?php
	}


/**
 * Create custom post type.
 */

add_action('init','sc_my_post_type');

function sc_my_post_type () {
	register_post_type('code',
		array(
			'labels' => array('name' => 'Code'),
				'public' => true,
				'menu_icon' => 'dashicons-editor-code',
				'show_in_menu' => true,
				'supports' => 'title',
			)
		);
}


/**
 * Register meta boxes.
 */

function sc_register_meta_boxes() {
	add_meta_box( 'hcf-1', __( 'Code Snippet', 'hcf' ), 'sc_display_callback', 'code' );
}
add_action( 'add_meta_boxes', 'sc_register_meta_boxes' );


/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */

function sc_display_callback( $post ) {
	include plugin_dir_path( __FILE__ ) . './form.php';
}


/**
 * Save custom field data
 */

function save_custom_fields(){
  global $post;
 
  if ( $post )
  {
    update_post_meta($post->ID, "sc_code_snippet", @$_POST["sc_code_snippet"]);
  }
}

add_action( 'save_post', 'save_custom_fields' );


/**
 * Add shortcode to display html code 
 */

function shortcode_function($atts) {

	// Get the value of the post meta with name `_my_post_meta_name`
	$post_id = $atts['id'];
	$post_meta_value = get_post_meta( $post_id, 'sc_code_snippet', true );
	$post_url = get_permalink( $post_id );
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
 * Write a copy of the code snippet to a text file 
 */

function create_code_text_file( $post_id ) {

	if ( get_post_meta($post_id, 'sc_code_snippet', true)) {

	$post_meta_value = get_post_meta( $post_id, 'sc_code_snippet', true );
	$handle = fopen("/home/classicc/public_html/wp-content/downloads/code/" . $post_id . ".txt", "w+");
	fwrite($handle, $post_meta_value);
	fclose($handle);

 }
}

add_action( 'save_post', 'create_code_text_file' );