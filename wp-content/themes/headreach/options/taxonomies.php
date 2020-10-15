<?php

# Custom hierarchical taxonomy (like categories)
/*register_taxonomy(
	'custom_taxonomy', # Taxonomy name
	array('post_type'), # Post Types
	array( # Arguments
		'labels'            => array(
			'name'              => __('Custom Taxonomies', 'crb'),
			'singular_name'     => __('Custom Taxonomy', 'crb'),
			'search_items'      => __('Search Custom Taxonomies', 'crb'),
			'all_items'         => __('All Custom Taxonomies', 'crb'),
			'parent_item'       => __('Parent Custom Taxonomy', 'crb'),
			'parent_item_colon' => __('Parent Custom Taxonomy:', 'crb'),
			'edit_item'         => __('Edit Custom Taxonomy', 'crb'),
			'update_item'       => __('Update Custom Taxonomy', 'crb'),
			'add_new_item'      => __('Add New Custom Taxonomy', 'crb'),
			'new_item_name'     => __('New Custom Taxonomy Name', 'crb'),
			'menu_name'         => __('Custom Taxonomies', 'crb'),
		),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'custom-taxonomy' ),
	)
);

# Custom non-hierarchical taxonomy (like tags)
register_taxonomy(
	'custom_taxonomy', # Taxonomy name
	array('post_type'), # Post Types
	array( # Arguments
		'labels'            => array(
			'name'                       => __('Custom Taxonomies', 'crb'),
			'singular_name'              => __('Custom Taxonomy', 'crb'),
			'search_items'               => __('Search Custom Taxonomies', 'crb'),
			'popular_items'              => __('Popular Custom Taxonomies', 'crb'),
			'all_items'                  => __('All Custom Taxonomies', 'crb'),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __('Edit Custom Taxonomy', 'crb'),
			'update_item'                => __('Update Custom Taxonomy', 'crb'),
			'add_new_item'               => __('Add New Custom Taxonomy', 'crb'),
			'new_item_name'              => __('New Custom Taxonomy Name', 'crb'),
			'separate_items_with_commas' => __('Separate custom taxonomy with commas', 'crb'),
			'add_or_remove_items'        => __('Add or remove custom taxonomy', 'crb'),
			'choose_from_most_used'      => __('Choose from the most used custom taxonomy', 'crb'),
			'not_found'                  => __('No custom taxonomy found.', 'crb'),
			'menu_name'                  => __('Custom Taxonomies', 'crb'),
		),
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'custom-taxonomy' ),
	)
);
*/