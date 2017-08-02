<?php
// Generated with CPT UI
function cptui_register_my_taxes_newsletter_category() {

	/**
	 * Taxonomy: Newsletter Category.
	 */

	$labels = array(
		'name' => __( 'Newsletter Category', 'sib-integration' ),
		'singular_name' => __( 'Newsletter Category', 'sib-integration' ),
	);

	$args = array(
		'label' => __( 'Newsletter Categories', 'sib-integration' ),
		'labels' => $labels,
		'public' => true,
		'hierarchical' => false,
		'label' => 'Newsletter Category',
		'show_ui' => true,
		'show_in_menu' => false,
		'show_in_nav_menus' => false,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'sib_newsletter-category', 'with_front' => true, ),
		'show_admin_column' => false,
		'show_in_rest' => false,
		'rest_base' => '',
		'show_in_quick_edit' => false,
	);
	register_taxonomy( 'sib_newsletter-category', array( 'newsletter' ), $args );
}

add_action( 'init', 'cptui_register_my_taxes_newsletter_category' );
