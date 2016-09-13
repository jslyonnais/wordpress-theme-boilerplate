<?php function register_cpt_EXAMPLE() {

   /**
    * Register a custom post type
    *
    * Supplied is a "reasonable" list of defaults
    * @see register_post_type for full list of options for register_post_type
    * @see add_post_type_support for full descriptions of 'supports' options
    * @see get_post_type_capabilities for full list of available fine grained capabilities that are supported
    */
    register_post_type( 'CPT_EXAMPLE', array(
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => '' ),
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'menu_icon' => "",
        'supports' => array( 'title', 'editor',  'thumbnail', 'page-attributes' ),
        'taxonomies' => array(''),
        'capability_type' => 'post',
        'capabilities' => array(),
        'labels' => array(
            'name' => __( 'CPT_EXAMPLES (plural)', 'example' ),
            'singular_name' => __( 'CPT_EXAMPLE (singular)', 'example' ),
            'add_new' => __( 'Add new', 'example' ),
            'add_new_item' => __( 'Add a new CPT_EXAMPLE (singular)', 'example' ),
            'edit_item' => __( 'Edit CPT_EXAMPLE (singular)', 'example' ),
            'new_item' => __( 'New CPT_EXAMPLE (singular)', 'example' ),
            'all_items' => __( 'All CPT_EXAMPLES (plural)', 'example' ),
            'view_item' => __( 'View CPT_EXAMPLE (singular)', 'example' ),
            'search_items' => __( 'Search CPT_EXAMPLES (plural)', 'example' ),
            'not_found' =>  __( 'No CPT_EXAMPLE (singular) found', 'example' ),
            'not_found_in_trash' => __( 'No CPT_EXAMPLE (singular) found in the trash', 'example' ),
            'parent_item_colon' => '',
            'menu_name' => 'CPT_EXAMPLE (plural)'
        )
    ) );
}
add_action( 'init', 'register_cpt_EXAMPLE' );
?>
