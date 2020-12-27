# Classic Code Snippets.

This plugin, created for [ClassicPress](https://www.classicpress.net/), is intended to be used for displaying code snippets on a page or post.

This is a **ClassicPress Research** plugin to find an alternative to using gist for displaying code snippets on a ClassicPress site.

## How it works.
The plugin uses highlight.js to provide syntax highlighting.

Code snippets are saved or edited in the custom post type `ccs_code_snippets`. The plugin uses a shortcode like [ccs_code_snippet id=19] to display the code snippet on a page or post.

## How to use.
- Download the plugin zip
- Install and activate the plugin via plugins admin.
- Add a code snippet via the Code Snippet post type.

<img src="assets/images/Screenshot-1.png" alt="Adding code snippet into the editor">

- Add the snippet with the post id of the snippet e.g. `[ccs_snippet id="19"]` in any post or page.

<img src="assets/images/Screenshot-2.png" alt="Add shortcode to page or post.">

## Credits.
Classic Code Snippets is a fork of SC Display Code (sc-display-code) originally developed by Alan and Jack Coggins at Simply Computing (https://simplycomputing.com.au).
