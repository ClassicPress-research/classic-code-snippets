<?php
/**
 * Plugin Name: Classic Code Snippets
 * Plugin URI: https://github.com/classicpress-research/classic-code-snippets
 * Description: Use a shortcode like [ccs_snippet id=19] to display code snippet on a page or post.
 * Version: 1.0.0
 * Author: ClassicPress Research Team
 * Author URI: https://github.com/classicpress-research/classic-code-snippets
 * License: GPL2
 * text-domain: ccs-code-snippets
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
function ccs_enqueue_scripts() {
	global $post;
	if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ccs_snippet') ) {
		wp_enqueue_style( 'highlight-style', '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/styles/default.min.css' );
		wp_enqueue_script( 'highlight-script', '//cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.1.2/build/highlight.min.js', array(), '10.1.2', true );
		wp_enqueue_script( 'highlight-num-script', 'https://cdnjs.cloudflare.com/ajax/libs/highlightjs-line-numbers.js/2.8.0/highlightjs-line-numbers.min.js', array(), '2.8.0', true );
		wp_enqueue_script( 'highlight-init', plugin_dir_url( __FILE__ ) . 'assets/js/highlight-init.js', array( 'highlight-script', 'highlight-num-script' ), '1.0.0', true );
	}
}

add_action( 'wp_enqueue_scripts', 'ccs_enqueue_scripts' );

/**
 * Create custom post type named "code".
 *
 * @return void
 */
function ccs_create_custom_post_type () {

	$labels = array(
		'name'                  => __( 'Code Snippets', 'ccs-code-snippets' ),
		'singular_name'         => __( 'Code Snippet', 'ccs-code-snippets' ),
		'menu_name'             => __( 'Code Snippets', 'ccs-code-snippets' ),
		'name_admin_bar'        => __( 'Code Snippet', 'ccs-code-snippets' ),
		'add_new'               => __( 'Add New', 'ccs-code-snippets' ),
		'add_new_item'          => __( 'Add New Code Snippet', 'ccs-code-snippets' ),
		'new_item'              => __( 'New Code Snippet', 'ccs-code-snippets' ),
		'edit_item'             => __( 'Edit Code Snippet', 'ccs-code-snippets' ),
		'view_item'             => __( 'View Code Snippet', 'ccs-code-snippets' ),
		'all_items'             => __( 'All Code Snippets', 'ccs-code-snippets' ),
		'search_items'          => __( 'Search Code Snippets', 'ccs-code-snippets' ),
		'parent_item_colon'     => __( 'Parent Code Snippets:', 'ccs-code-snippets' ),
		'not_found'             => __( 'No Code Snippets found.', 'ccs-code-snippets' ),
		'not_found_in_trash'    => __( 'No Code Snippets found in Trash.', 'ccs-code-snippets' ),
		'featured_image'        => __( 'Code Snippet Cover Image', 'ccs-code-snippets' ),
		'set_featured_image'    => __( 'Set cover image', 'ccs-code-snippets' ),
		'archives'              => __( 'Code Snippet archives', 'ccs-code-snippets' ),
		'filter_items_list'     => __( 'Filter Code Snippets list', 'ccs-code-snippets' ),
		'items_list_navigation' => __( 'Code Snippets list navigation', 'ccs-code-snippets' ),
		'items_list'            => __( 'Code Snippets list', 'ccs-code-snippets' ),
	);

	register_post_type(
		'ccs_code_snippet',
		array(
			'labels'       => $labels,
			'public'       => true,
			'menu_icon'    => 'dashicons-editor-code',
			'show_in_menu' => true,
			'supports'     => array( 'title' ),
		)
	);
}
add_action( 'init', 'ccs_create_custom_post_type' );

/**
 * Register Code Snippet metabox.
 *
 * @return void
 */
function ccs_register_meta_boxes() {
	add_meta_box(
		'ccs-code-snippets-metabox',
		__( 'Code Snippet Details', 'ccs-code-snippets' ),
		'ccs_display_callback',
		'ccs_code_snippet'
	);
}
add_action( 'add_meta_boxes', 'ccs_register_meta_boxes' );

/**
 * Metabox callback with HTML.
 *
 * @param mixed $post WP_Post Post object.
 * @return void
 */
function ccs_display_callback( $post ) {
	$ccs_code_snippet = get_post_meta( get_the_ID(), 'ccs_code_snippet', true );
	?>
	<p class="ccs-meta-options">
		<label for="ccs_shortcode"><?php esc_attr_e( 'Insert snippet in post/page using', 'ccs-code-snippets'); ?></label>
		<input id="ccs_shortcode" type="text" name="ccs_shortcode" value="<?php echo '[ccs_snippet id=' . get_the_ID() . ']'; ?>" />
	</p>
	<p class="ccs-meta-options">
		<label for="ccs_code_snippet screen-reader-text"><?php echo esc_attr( 'Code Snippet', 'ccs-code-snippets'); ?></label><br>
		<textarea id="ccs_code_snippet" rows="20" class="widefat" name="ccs_code_snippet"><?php echo esc_attr( $ccs_code_snippet ); ?></textarea>
	</p>
	<?php
}

/**
 * Save custom field data.
 *
 * @return void
 */
function ccs_save_custom_fields( $post_id ){

	// Escape on autosave of the post.
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	
	// Update the code snippet.
	if ( array_key_exists('ccs_code_snippet', $_POST ) ) {
		update_post_meta( $post_id, 'ccs_code_snippet', $_POST['ccs_code_snippet'] );
	}

}
add_action( 'save_post', 'ccs_save_custom_fields', 10, 1 );

/**
 * Add shortcode to display html code.
 * Requires passing in the ID of the code snippet when posting in a page or post.
 *
 * @param mixed $atts Attributes from the shortcode. 
 * @return void
 */
function ccs_declare_shortcode($atts) {

	// Get the value of the post meta with name `_my_post_meta_name`
	$post_id         = $atts['id'];
	$post_meta_value = get_post_meta( $post_id, 'ccs_code_snippet', true );
	$post_url        = get_permalink( $post_id );
	$post_link       = get_post_permalink( $post_id );

	if ( is_user_logged_in() ) {
		$codeID = '<a href="/wp-admin/post.php?post=' . $post_id . '&action=edit"> Code ID: ' . $post_id . '</a>';
	} else {
		$codeID = 'Code ID: ' . $post_id;
	}

	// Print the post meta value with buttons and ID info/link
	$myVar = htmlspecialchars( $post_meta_value, ENT_QUOTES ); 

	return '
	<pre>
		<code class="language-php">' . $myVar  . '</code>
	</pre>
	<div>
		<button id="copy-button" class="cc-button" onclick="copyToClipboard()">Copy to Clipboard</button>
		<a href="' .$post_link . '?raw=true " class="cc-button">View Raw</a>
		<span style="float:right;color:#999;"><small>' . $codeID . '</small></span>
	</div>
	<script>
		var copyButton = document.getElementById("copy-button");
		function copyToClipboard () {
			var clipText = `' . $post_meta_value . '`;
			navigator.clipboard.writeText(clipText).then(function() {
				copyButton.innerHTML = "Copied";
			}, function() {
				copyButton.innerHTML = "Copy Failed";
			});
		}
	</script>';

}

add_shortcode( 'ccs_snippet', 'ccs_declare_shortcode' );

/**
 * Hook to the template_redirect
 * Checks if $_GET['raw'] is set, ccs_code_snippet post type
 * Outputs the raw code snippet with Content-Type: text/plain and then exits.
 *
 * @return void
 */
function ccs_code_snippet_raw_code() {

	$post_id = get_queried_object_id();

	if ( isset( $_GET['raw'] ) && get_post_type() == 'ccs_code_snippet' ) {
		header( "Content-Type: text/plain", true, 200 );
		$ccs_code_snippet = get_post_meta( $post_id, 'ccs_code_snippet', true );
		echo $ccs_code_snippet;
		exit();
	}

}

add_action( 'template_redirect', 'ccs_code_snippet_raw_code' );

// Add simple copy-paste code for the post/pages in columns.
add_filter( 'manage_posts_columns', 'ccs_columns_id', 5 );
add_action( 'manage_posts_custom_column', 'ccs_custom_id_columns', 5, 2 );

/**
 * Add shortcode display column
 *
 * @param array $defaults Default data from function.
 * @return $defaults.
 */
function ccs_columns_id( $defaults ){
	$defaults['ccs_post_id'] = __( 'Shortcode', 'ccs-code-snippets' );
	return $defaults;
}

/**
 * Populate custom column with post/page shortcode.
 *
 * @param array $column_name Column Names.
 * @param int $post_id Post ID.
 * @return void
 */
function ccs_custom_id_columns( $column_name, $post_id ){
	if( $column_name === 'ccs_post_id' ){
		echo '[ccs_snippet id=' . $post_id . ']';
	}
}

/**
 * Rewrite the permalinks rules to avoid the 404 error.
 *
 * @return void
 */
function ccs_flush_rewrite_rules() {
	ccs_create_custom_post_type();
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'ccs_flush_rewrite_rules' );
