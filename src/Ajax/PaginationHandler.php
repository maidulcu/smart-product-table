<?php
namespace SmartTable\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PaginationHandler {

    public function __construct() {
        add_action( 'wp_ajax_smarttable_load_page', [ $this, 'handle_pagination' ] );
        add_action( 'wp_ajax_nopriv_smarttable_load_page', [ $this, 'handle_pagination' ] );
    }

    public function handle_pagination() {
        check_ajax_referer( 'smarttable_nonce', 'nonce' );

        $page = max( 1, (int) ( $_POST['page'] ?? 1 ) );
        $columns = sanitize_text_field( $_POST['columns'] ?? '' );
        $post_id = (int) ( $_POST['post_id'] ?? 0 );
        
        // Get metabox settings if post_id provided
        $metabox_per_page = 0;
        if ( $post_id > 0 ) {
            $metabox_per_page = (int) get_post_meta( $post_id, '_smarttable_products_per_page', true );
        }
        
        $admin_posts_per_page = class_exists( '\SmartTable\Admin\Settings' ) ? \SmartTable\Admin\Settings::get_posts_per_page() : 10;
        $limit = max( 1, (int) ( $_POST['limit'] ?? 0 ) );
        
        // Priority: POST limit > metabox setting > admin setting
        if ( $limit <= 0 ) {
            $limit = $metabox_per_page > 0 ? $metabox_per_page : $admin_posts_per_page;
        }
        
        $category = sanitize_text_field( $_POST['category'] ?? '' );
        $tag = sanitize_text_field( $_POST['tag'] ?? '' );
        $filter_categories = sanitize_text_field( $_POST['filter_categories'] ?? '' );
        $filter_tags = sanitize_text_field( $_POST['filter_tags'] ?? '' );
        $filter_category = sanitize_text_field( $_POST['filter_category'] ?? '' );
        $min_price = floatval( $_POST['min_price'] ?? 0 );
        $max_price = floatval( $_POST['max_price'] ?? 0 );
        $orderby = sanitize_key( $_POST['orderby'] ?? 'date' );
        $order = sanitize_text_field( $_POST['order'] ?? 'DESC' );

        $context = [
            'post_id' => $post_id,
            'columns' => $columns,
            'limit' => $limit,
            'category' => $category ? explode( ',', $category ) : [],
            'tag' => $tag ? explode( ',', $tag ) : [],
            'filter_categories' => $filter_categories ? explode( ',', $filter_categories ) : [],
            'filter_tags' => $filter_tags ? explode( ',', $filter_tags ) : [],
            'orderby' => $orderby,
            'order' => $order,
            'enable_pagination' => '1',
            'products_per_page' => $limit,
            'paged' => $page,
            'url_filter_category' => $filter_category,
            'url_min_price' => $min_price,
            'url_max_price' => $max_price,
        ];

        ob_start();
        include SMARTTABLE_PLUGIN_DIR . 'templates/table.php';
        $html = ob_get_clean();

        wp_send_json_success( [ 'html' => $html ] );
    }
}