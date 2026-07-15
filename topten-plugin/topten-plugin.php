<?php
/**
 * Plugin Name: TopTen Plugin
 * Plugin URI: https://github.com/mdhemalakhand/topten-plugin
 * Description: This plugin is used to create a top ten list of the most popular products.
 * Version: 1.1.1
 * Requires at least: 5.0
 * Requires PHP: 8.0
 * Author: Topten
 * Author URI: https://github.com/topten
 * License: GPL2
 * Text Domain: topten-plugin
 */

/**
➤ edit_product
➤➤ read_product
➤➤ delete_product
➤➤ create_products
➤➤ edit_products
➤➤ edit_others_products
➤➤ edit_private_products
➤➤ edit_published_products
➤➤ publish_products
➤➤ read_private_products
➤➤ delete_products
➤➤ 
 */

function topten_register_menu() {
    $labels = [
        'name' => 'Products',
        'singular_name' => 'Product',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Product',
        'edit_item' => 'Edit Product',
        'new_item' => 'New Product',
        'view_item' => 'View Product',
        'view_items' => 'View Products',
        'search_items' => 'Search Products',
        'not_found' => 'No Products found.',
        'not_found_in_trash' => 'No Products found in Trash.',
        'all_items' => 'All Products',
        'archives' => 'Product Archives',
        'attributes' => 'Product Attributes',
        'insert_into_item' => 'Insert into Product',
        'uploaded_to_this_item' => 'Uploaded to this Product',
        'featured_image' => 'Product Image',
        'set_featured_image' => 'Set Product image',
        'remove_featured_image' => 'Remove Product image',
        'use_featured_image' => 'Use as Product image',
        'filter_items_list' => 'Filter Products list',
        'items_list_navigation' => 'Products list navigation',
        'items_list' => 'Products list',
        'item_published' => 'Product published.',
        'item_published_privately' => 'Product published privately.',
        'item_reverted_to_draft' => 'Product reverted to draft.',
        'item_scheduled' => 'Product scheduled.',
        'item_updated' => 'Product updated.'
    ];
    register_post_type(
        'product',
        array(
            'labels' => $labels,
            'public' => true,
            'show_in_rest' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'has_archive' => 'products',
            'exclude_from_search' => false,
            'show_ui' => true,
            'menu_icon'    => 'dashicons-performance',
            'hierarchical' => true,
            'map_meta_cap' => true,
            'supports' => [
                'title',
                'editor',
                'excerpt',
                'comments',
                'page-attributes'
            ],
            'rewrite' => [
                'slug' => 'products',
                'with_front' => false
            ],
            'capabilities' => [
                'edit_post' => 'edit_product',
                'read_post' => 'read_product',
                'delete_post' => 'delete_product',
                'create_posts' => 'create_products',
                'edit_posts' => 'edit_products',
                'edit_others_posts' => 'edit_others_products',
                'edit_private_posts' => 'edit_private_products',
                'edit_published_posts' => 'edit_published_products',
                'publish_posts' => 'publish_products',
                'read_private_posts' => 'read_private_products',
                'read' => 'read',
                'delete_posts' => 'delete_products',
                'delete_private_posts' => 'delete_private_products',
                'delete_published_posts' => 'delete_published_products',
                'delete_others_posts' => 'delete_others_products'
            ]
        )
    );

    flush_rewrite_rules();
}
add_action('init', 'topten_register_menu');

/**
 * Custom capability names are NOT granted automatically.
 * Without edit_products, even Administrator cannot see the Products menu.
 */
function topten_add_product_caps() {
	$caps = array(
		'edit_product',
		'read_product',
		'delete_product',
		'create_products',
		'edit_products',
		'edit_others_products',
		'edit_private_products',
		'edit_published_products',
		'publish_products',
		'read_private_products',
		'delete_products',
		'delete_private_products',
		'delete_published_products',
		'delete_others_products',
	);

	$admin = get_role( 'administrator' );
	if ( ! $admin ) {
		return;
	}

	foreach ( $caps as $cap ) {
		$admin->add_cap( $cap );
	}
}
add_action( 'admin_init', 'topten_add_product_caps' );


function topten_register_metadata() {
    $args = array(
        'single' => true,
        'type' => 'string',
        'show_in_rest' => true,
        'sanitize_callback' => function($value) {
            return wp_strip_all_tags( $value );
        }
    );
    register_post_meta( 'product', 'topten_product_additional_info', $args );
    register_post_meta( 'product', 'topten_product_rating_text', $args );
}
add_action('init', 'topten_register_metadata');


function topten_manage_metadata($post_id) {
    add_post_meta( $post_id, 'topten_product_additional_info', 'This product is red color', true );
    update_post_meta( $post_id, 'topten_product_additional_info', 'This product is yellow color' ); // existing info update.
    update_post_meta( $post_id, 'topten_product_rating_text', 'This product is highly recommended.' ); // new info add.

    $additional_info = get_post_meta( $post_id, 'topten_product_additional_info', true );
    error_log($additional_info);

    delete_post_meta( $post_id, 'topten_product_rating_text' );
}
add_action('save_post_product', 'topten_manage_metadata');


function display_topten_product_info_box($post) {
    $topten_my_product_info = get_post_meta( $post->ID, 'topten_my_product_info', true );
    
    wp_nonce_field( basename(__FILE__), 'topten_my_product_info', true );

    echo '<textarea name="product_informations">'.esc_html($topten_my_product_info).'</textarea>';
}
function topten_add_meta_box() {
    add_meta_box(
        'topten_product_info_box',
        "Topten - Product Informations",
        'display_topten_product_info_box',
        'product',
        'advanced'
    );
}
add_action('add_meta_boxes_product', 'topten_add_meta_box');


function save_topten_meta_informations($post_id) {
    if( !isset($_POST['topten_my_product_info']) || !wp_verify_nonce($_POST['topten_my_product_info'], basename(__FILE__)) ) {
        return;
    }

    if( !current_user_can( 'edit_product', $post_id ) ) {
        return;
    }

    if(
        wp_doing_ajax() ||
        wp_is_post_autosave($post_id) ||
        wp_is_post_revision($post_id)
    ) {
        return;
    }

    $old_info = get_post_meta( $post_id, 'topten_my_product_info', true );
    $new_info = isset($_POST['product_informations']) ? sanitize_text_field( $_POST['product_informations'] ) : '';

    if( !$new_info && $old_info ) {
        delete_post_meta( $post_id, 'topten_my_product_info' );
    } else {
        update_post_meta( $post_id, 'topten_my_product_info', $new_info );
    }

}
add_action('save_post_product', 'save_topten_meta_informations');


/**
 * ============
 * Taxonomy
 * ============
 */
function topten_register_taxonomy() {
    $args = array(
        'public' => true,
        'show_ui' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_admin_column' => true,
        'labels' => [
            'name' => 'Products Categories',
            'singular_name' => 'Product Category',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Product Category',
            'edit_item' => 'Edit Product Category',
            'new_item' => 'New Product Category',
            'view_item' => 'View Product Category',
            'view_items' => 'View Products',
            'search_items' => 'Search Products',
            'not_found' => 'No Products found.',
            'not_found_in_trash' => 'No Products found in Trash.',
            'all_items' => 'All Products',
            'archives' => 'Product Category Archives',
            'attributes' => 'Product Category Attributes',
            'insert_into_item' => 'Insert into Product Category',
            'uploaded_to_this_item' => 'Uploaded to this Product Category',
            'featured_image' => 'Product Category Image',
            'set_featured_image' => 'Set Product Category image',
            'remove_featured_image' => 'Remove Product Category image',
            'use_featured_image' => 'Use as Product Category image',
            'filter_items_list' => 'Filter Products list',
            'items_list_navigation' => 'Products list navigation',
            'items_list' => 'Products list',
            'item_published' => 'Product Category published.',
            'item_published_privately' => 'Product Category published privately.',
            'item_reverted_to_draft' => 'Product Category reverted to draft.',
            'item_scheduled' => 'Product Category scheduled.',
            'item_updated' => 'Product Category updated.'
        ]
    );
    $args_tag = array(
        'public' => true,
        'show_ui' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_admin_column' => true,
        'labels' => [
            'name' => 'Products Tags',
            'singular_name' => 'Product Tag',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Product Tag',
            'edit_item' => 'Edit Product Tag',
            'new_item' => 'New Product Tag',
            'view_item' => 'View Product Tag',
            'view_items' => 'View Products',
            'search_items' => 'Search Products',
            'not_found' => 'No Products found.',
            'not_found_in_trash' => 'No Products found in Trash.',
            'all_items' => 'All Products',
            'archives' => 'Product Tag Archives',
            'attributes' => 'Product Tag Attributes',
            'insert_into_item' => 'Insert into Product Tag',
            'uploaded_to_this_item' => 'Uploaded to this Product Tag',
            'featured_image' => 'Product Tag Image',
            'set_featured_image' => 'Set Product Tag image',
            'remove_featured_image' => 'Remove Product Tag image',
            'use_featured_image' => 'Use as Product Tag image',
            'filter_items_list' => 'Filter Products list',
            'items_list_navigation' => 'Products list navigation',
            'items_list' => 'Products list',
            'item_published' => 'Product Tag published.',
            'item_published_privately' => 'Product Tag published privately.',
            'item_reverted_to_draft' => 'Product Tag reverted to draft.',
            'item_scheduled' => 'Product Tag scheduled.',
            'item_updated' => 'Product Tag updated.'
        ]
    );

    register_taxonomy( 'product_category', 'product', $args );
    register_taxonomy( 'product_tag_archive', 'product', $args_tag );
}
add_action('init', 'topten_register_taxonomy');

function topten_assign_taxonomy_to_cpt() {
    register_taxonomy_for_object_type( 'product_category', 'post' );
    register_taxonomy_for_object_type( 'product_category', 'page' );
}
add_action('init', 'topten_assign_taxonomy_to_cpt');

// display taxonomy.
function topten_display_taxonomy() {
    $tax_info = get_taxonomy( 'product_category' );
    // error_log(print_r($tax_info, true));
    // error_log('taxonomy name: '. $tax_info->name);
    // error_log('taxonomy label single name: '. $tax_info->labels->singular_name);

    $product_categories = get_the_terms(
        115,
        'product_category'
    );
    if( !empty($product_categories) ) {
        foreach($product_categories as $product_cat) {
            $taxonomy_name = $product_cat->name;
            error_log('tax name: '. $taxonomy_name);
        }
    }
}
add_action('init', 'topten_display_taxonomy');


function topten_display_product_categories_in_footer() {
    $taxonomy = 'product_category';

    the_terms(
        115,
        $taxonomy,
        'Product Category: ',
        ', ',
        'End'
    );
}
add_action('wp_footer', 'topten_display_product_categories_in_footer');

function topten_condition_based_taxonomy() {
    $taxonomy = 'product_category';
    if( taxonomy_exists( $taxonomy ) ) {
        register_taxonomy_for_object_type( 'product_category', 'page' );
    } else {
        // register_taxonomy( 'product_category', 'product', $args );
    }

    if(is_tax('product_category', 'automotive')) {
        echo '<h2>This is automative taxonomy archive</h2>';
    } elseif( is_tax('product_category') ) {
        echo '<h2>This is taxonomy archive</h2>';
    } elseif( is_tax() ) {
        echo '<h2>This is other taxonomy archive</h2>';
    }
}
add_action('wp_footer', 'topten_condition_based_taxonomy');