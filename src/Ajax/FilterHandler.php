<?php
namespace SmartTable\Ajax;

use SmartTable\Core\ColumnManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FilterHandler {
    
    public function __construct() {
        add_action( 'wp_ajax_smarttable_filter_products', [ $this, 'handle_filter_request' ] );
        add_action( 'wp_ajax_nopriv_smarttable_filter_products', [ $this, 'handle_filter_request' ] );
    }
    
    public function handle_filter_request() {
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'smarttable_filter' ) ) {
            wp_die( 'Security check failed' );
        }
        
        $post_id = absint( $_POST['post_id'] ?? 0 );
        $filters = $_POST['filters'] ?? [];
        $page = absint( $_POST['page'] ?? 1 );
        
        // Debug logging
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'SmartTable Filter Request - Post ID: ' . $post_id );
            error_log( 'SmartTable Filter Request - Filters: ' . print_r( $filters, true ) );
            error_log( 'SmartTable Filter Request - Page: ' . $page );
        }
        
        if ( ! $post_id || get_post_type( $post_id ) !== 'smarttable_product' ) {
            wp_send_json_error( 'Invalid table ID' );
        }
        
        // Get table configuration
        $layout_json = get_post_meta( $post_id, '_smarttable_column_layout', true );
        $layout = json_decode( $layout_json, true );
        
        $columns = [];
        if ( is_array( $layout ) ) {
            foreach ( $layout as $item ) {
                if ( ! is_array( $item ) || empty( $item['type'] ) ) {
                    continue;
                }
                $column_id = sanitize_key( (string) $item['type'] );
                if ( $column_id !== '' ) {
                    $columns[] = $column_id;
                }
            }
        }
        
        // Build query args
        $args = [
            'status' => 'publish',
            'limit' => get_post_meta( $post_id, '_smarttable_per_page', true ) ?: 12,
            'orderby' => sanitize_key( $filters['orderby'] ?? 'date' ),
            'order' => 'DESC',
        ];
        
        // Apply category filter
        if ( ! empty( $filters['category'] ) ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => [ absint( $filters['category'] ) ],
                ]
            ];
        }
        
        // Apply price filter using WP_Query for better price filtering
        $min_price = floatval( $filters['min_price'] ?? 0 );
        $max_price = floatval( $filters['max_price'] ?? 0 );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Price filter - Min: ' . $min_price . ', Max: ' . $max_price );
        }
        
        // Use WP_Query for price filtering
        $per_page = get_post_meta( $post_id, '_smarttable_per_page', true ) ?: 12;
        $query_args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => $args['orderby'],
            'order' => $args['order'],
        ];
        
        // Add search functionality
        if ( ! empty( $filters['search'] ) ) {
            $query_args['s'] = sanitize_text_field( $filters['search'] );
        }
        
        // Add category filter to WP_Query
        if ( ! empty( $filters['category'] ) ) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => [ absint( $filters['category'] ) ],
                ]
            ];
        }
        
        // Add price filter using meta_query
        if ( $min_price > 0 || $max_price > 0 ) {
            $meta_query = [];
            
            if ( $min_price > 0 ) {
                $meta_query[] = [
                    'key' => '_price',
                    'value' => $min_price,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                ];
            }
            
            if ( $max_price > 0 ) {
                $meta_query[] = [
                    'key' => '_price',
                    'value' => $max_price,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                ];
            }
            
            if ( count( $meta_query ) > 1 ) {
                $meta_query['relation'] = 'AND';
            }
            
            $query_args['meta_query'] = $meta_query;
        }
        
        $query = new \WP_Query( $query_args );
        $products = [];
        
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $products[] = wc_get_product( get_the_ID() );
            }
            wp_reset_postdata();
        }
        
        $total_products = $query->found_posts;
        $total_pages = ceil( $total_products / $per_page );
        
        // Get column setup
        $manager = ColumnManager::instance();
        $active_cols = $manager->resolve_active( $columns );
        $header_labels = $manager->get_labels( $active_cols );
        
        // Get design style and display options
        $design_style = get_post_meta( $post_id, '_smarttable_design_style', true ) ?: 'default';
        $show_bulk_cart = get_post_meta( $post_id, '_smarttable_show_bulk_cart', true ) ?: '1';
        
        // Build context for columns
        $context = [
            'post_id' => $post_id,
            'columns' => $columns,
            'design_style' => $design_style
        ];
        
        // Generate table HTML
        ob_start();
        ?>
        <table class="smarttable <?php echo esc_attr( 'smarttable-style-' . $design_style ); ?>">
            <thead>
                <tr>
                    <?php if ( $show_bulk_cart === '1' ) : ?>
                    <th class="smarttable-col smarttable-col-select">
                        <input type="checkbox" class="smarttable-header-select">
                    </th>
                    <?php endif; ?>
                    <?php foreach ( $header_labels as $id => $label ) : ?>
                        <th class="smarttable-col smarttable-col-<?php echo esc_attr( $id ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php if ( ! empty( $products ) ) : ?>
                <?php foreach ( $products as $product ) : ?>
                    <tr class="smarttable-row" data-product-id="<?php echo esc_attr( (string) $product->get_id() ); ?>">
                        <?php if ( $show_bulk_cart === '1' ) : ?>
                        <td class="smarttable-cell smarttable-col-select">
                            <input type="checkbox" class="smarttable-product-select" 
                                   value="<?php echo esc_attr( $product->get_id() ); ?>"
                                   data-price="<?php echo esc_attr( $product->get_price() ); ?>">
                        </td>
                        <?php endif; ?>
                        <?php foreach ( $active_cols as $id => $col ) : ?>
                            <td class="smarttable-cell smarttable-col-<?php echo esc_attr( $id ); ?>">
                                <?php
                                $html = '';
                                if ( is_object( $col ) && method_exists( $col, 'render_cell' ) ) {
                                    $html = (string) $col->render_cell( $product, $context );
                                }
                                echo wp_kses_post( $html );
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?php echo esc_attr( (string) ( count( $active_cols ) + ( $show_bulk_cart === '1' ? 1 : 0 ) ) ); ?>">
                        <?php esc_html_e( 'No products found.', 'smart-product-table' ); ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ( $total_pages > 1 ) : ?>
        <div class="smarttable-pagination" data-post-id="<?php echo esc_attr( $post_id ); ?>">
            <?php if ( $page > 1 ) : ?>
                <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $page - 1 ); ?>">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php 
            $start_page = max( 1, $page - 2 );
            $end_page = min( $total_pages, $page + 2 );
            
            for ( $i = $start_page; $i <= $end_page; $i++ ) :
                if ( $i == $page ) : ?>
                    <span class="smarttable-page-current"><?php echo $i; ?></span>
                <?php else : ?>
                    <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $i ); ?>"><?php echo $i; ?></a>
                <?php endif;
            endfor; ?>
            
            <?php if ( $page < $total_pages ) : ?>
                <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $page + 1 ); ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php
        
        $html = ob_get_clean();
        wp_send_json_success( $html );
    }
}