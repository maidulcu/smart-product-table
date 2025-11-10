<?php
namespace SmartTable\CPT;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Post_Type_Product {
    protected $post_type = 'smarttable_product';

    public function __construct() {
        // Hook CPT registration directly on init
        add_action( 'init', [ $this, 'register_post_type' ] );
        // Add custom columns to admin list table
        add_filter( 'manage_smarttable_product_posts_columns', [ $this, 'add_custom_columns' ] );
        add_action( 'manage_smarttable_product_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
    }

    public function register_post_type() {
        $labels = [
            'name'                  => _x( 'Product Table Designs', 'post type general name', 'smart-product-table' ),
            'singular_name'         => _x( 'Product Table Design', 'post type singular name', 'smart-product-table' ),
            'menu_name'             => __( 'Product Tables', 'smart-product-table' ),
            'name_admin_bar'        => __( 'Product Table', 'smart-product-table' ),
            'add_new'               => __( 'Add New Table', 'smart-product-table' ),
            'add_new_item'          => __( 'Add New Product Table Design', 'smart-product-table' ),
            'edit_item'             => __( 'Edit Product Table Design', 'smart-product-table' ),
            'new_item'              => __( 'New Product Table Design', 'smart-product-table' ),
            'view_item'             => __( 'View Product Table Design', 'smart-product-table' ),
            'search_items'          => __( 'Search Product Table Designs', 'smart-product-table' ),
            'not_found'             => __( 'No product table designs found.', 'smart-product-table' ),
            'not_found_in_trash'    => __( 'No product table designs found in Trash.', 'smart-product-table' ),
        ];

        $args = [
            'labels'        => $labels,
            'public'        => false,
            'show_ui'       => true,
            'has_archive'   => false,
            'supports'      => array( 'title', 'editor', 'revisions' ),
            'menu_icon'     => 'dashicons-products',
            'menu_position' => 30,
            'rewrite'       => [ 'slug' => 'smart-product', 'with_front' => false ],
            'show_in_rest'  => true,
        ];

        register_post_type( $this->post_type, $args );
    }


    /**
     * Add custom columns to the smarttable_product post type admin table.
     *
     * @param array $columns
     * @return array
     */
    public function add_custom_columns( $columns ) {
        $columns['layout'] = __( 'Layout', 'smart-product-table' );
        $columns['shortcode'] = __( 'Shortcode', 'smart-product-table' );
        return $columns;
    }

    /**
     * Render content for custom columns in the smarttable_product post type admin table.
     *
     * @param string $column
     * @param int $post_id
     */
    public function render_custom_columns( $column, $post_id ) {
        if ( 'layout' === $column ) {
            echo esc_html( get_post_meta( $post_id, '_smarttable_layout_type', true ) );
        }
        if ( 'shortcode' === $column ) {
            echo esc_html( '[smart_product_table id="' . absint( $post_id ) . '"]' );
        }
    }
    
}
