<?php
// Generated with CPT UI
function cptui_register_my_cpts_newsletter() {

	/**
	 * Post Type: Newsletters.
	 */

	$labels = array(
		'name' => __( 'Newsletters', 'sib-integration' ),
		'singular_name' => __( 'Newsletter', 'sib-integration' ),
	);

	$args = array(
		'label' => __( 'Newsletters', 'sib-integration' ),
		'labels' => $labels,
		'description' => '',
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_rest' => false,
		'rest_base' => '',
		'has_archive' => false,
		'show_in_menu' => true,
		'menu_icon' => 'dashicons-email-alt',
		'exclude_from_search' => true,
		'capability_type' => 'post',
		'map_meta_cap' => true,
		'hierarchical' => false,
		'rewrite' => array( 'slug' => 'sib_newsletter', 'with_front' => true ),
		'query_var' => true,
		'supports' => array( 'title', 'editor', 'thumbnail' ),
		'taxonomies' => array( 'sib_newsletter-category' ),
	);

	register_post_type( 'sib_newsletter', $args );
}

add_action( 'init', 'cptui_register_my_cpts_newsletter' );
