# sc-display-code
Simply Computing plugin for displaying code snippets on a page or post

https://simplycomputing.com.au

mail@simplycomputing.work

Written by Alan and Jack Coggins

18 Sept 2020

This is a proof-of-concept plugin to find a workable alternative to using gist for displaying code snippets on a ClassicPress site.

The plugin uses highlight.js to provide syntax highlighting.

Code snippets saved or edited in the custom post type (post meta value sc_code_snippet) are also saved to a text file in wp-content/downloads/code with naming format: $post_id.txt

The raw view displays this file.
