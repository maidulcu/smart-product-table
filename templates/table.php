<?php
/**
 * Smart Product Table – Frontend Template
 */

use SmartTable\Core\ColumnManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wc_get_products' ) ) {
    echo '<div class="smarttable-notice">WooCommerce is required for Smart Product Table.</div>';
    return;
}

// Context setup
$context = isset( $context ) && is_array( $context ) ? $context : [];
$requested_columns = isset( $context['columns'] ) ? $context['columns'] : 'image,title,category,stock_status,price,add_to_cart';
$limit = isset( $context['limit'] ) ? (int) $context['limit'] : 12;
$orderby = isset( $context['orderby'] ) ? $context['orderby'] : 'date';
$order = isset( $context['order'] ) ? strtoupper( (string) $context['order'] ) : 'DESC';

// Column setup
$manager = ColumnManager::instance();
$active_cols = $manager->resolve_active( $requested_columns );
$header_labels = $manager->get_labels( $active_cols );

if ( empty( $active_cols ) ) {
    echo '<div class="smarttable-notice">No columns are available to render.</div>';
    return;
}

// Get per page setting from admin
$per_page = isset( $context['post_id'] ) ? get_post_meta( $context['post_id'], '_smarttable_per_page', true ) : 12;
$per_page = $per_page ?: 12;

// Query setup
$args = [
    'status' => 'publish',
    'limit' => $per_page,
    'orderby' => $orderby,
    'order' => $order === 'ASC' ? 'ASC' : 'DESC',
];

// Apply new filters
$categories = isset( $context['categories'] ) ? $context['categories'] : [];
$tags = isset( $context['tags'] ) ? $context['tags'] : [];
$min_price = isset( $context['min_price'] ) ? floatval( $context['min_price'] ) : 0;
$max_price = isset( $context['max_price'] ) ? floatval( $context['max_price'] ) : 0;

if ( ! empty( $categories ) ) {
    $args['category'] = $categories;
}

if ( ! empty( $tags ) ) {
    $args['tag'] = $tags;
}

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
    
    $args['meta_query'] = $meta_query;
}

$products = wc_get_products( $args );

// Design style
$design_style = 'default';
if ( isset( $context['post_id'] ) && $context['post_id'] > 0 ) {
    $saved_style = get_post_meta( $context['post_id'], '_smarttable_design_style', true );
    if ( ! empty( $saved_style ) ) {
        $design_style = $saved_style;
    }
}
if ( isset( $context['design_style'] ) && ! empty( $context['design_style'] ) ) {
    $design_style = $context['design_style'];
}

?>
<div class="smarttable-wrapper" data-post-id="<?php echo esc_attr( $context['post_id'] ?? 0 ); ?>">
    
    <!-- Search Box -->
    <?php 
    $show_search = isset( $context['post_id'] ) ? get_post_meta( $context['post_id'], '_smarttable_show_search', true ) : '0';
    if ( $show_search === '1' ) :
    ?>
    <div class="smarttable-search-box">
        <div class="search-container">
            <input type="text" class="smarttable-search" placeholder="<?php esc_attr_e( 'Search products...', 'smart-product-table' ); ?>">
            <button type="button" class="smarttable-search-btn">
                <i class="dashicons dashicons-search"></i>
                <?php esc_html_e( 'Search', 'smart-product-table' ); ?>
            </button>
            <button type="button" class="smarttable-clear-search">
                <i class="dashicons dashicons-no-alt"></i>
                <?php esc_html_e( 'Clear', 'smart-product-table' ); ?>
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Frontend Filters -->
    <?php 
    $show_filters = isset( $context['post_id'] ) ? get_post_meta( $context['post_id'], '_smarttable_show_filters', true ) : '1';
    if ( $show_filters === '1' ) :
    ?>
    <div class="smarttable-frontend-filters">
        <div class="filter-row">
            
            <div class="filter-item">
                <label><?php esc_html_e( 'Category', 'smart-product-table' ); ?></label>
                <select class="smarttable-filter" data-filter="category">
                    <option value=""><?php esc_html_e( 'All Categories', 'smart-product-table' ); ?></option>
                    <?php
                    $all_categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
                    foreach ( $all_categories as $cat ) {
                        printf(
                            '<option value="%s">%s (%d)</option>',
                            esc_attr( $cat->term_id ),
                            esc_html( $cat->name ),
                            $cat->count
                        );
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-item">
                <label><?php esc_html_e( 'Price Range', 'smart-product-table' ); ?></label>
                <div class="price-filter">
                    <input type="number" class="smarttable-filter" data-filter="min_price" placeholder="Min" min="0" step="0.01">
                    <span>-</span>
                    <input type="number" class="smarttable-filter" data-filter="max_price" placeholder="Max" min="0" step="0.01">
                </div>
            </div>
            
            <div class="filter-item">
                <label><?php esc_html_e( 'Sort By', 'smart-product-table' ); ?></label>
                <select class="smarttable-filter" data-filter="orderby">
                    <option value="date"><?php esc_html_e( 'Date Added', 'smart-product-table' ); ?></option>
                    <option value="title"><?php esc_html_e( 'Name', 'smart-product-table' ); ?></option>
                    <option value="price"><?php esc_html_e( 'Price', 'smart-product-table' ); ?></option>
                    <option value="popularity"><?php esc_html_e( 'Popularity', 'smart-product-table' ); ?></option>
                </select>
            </div>
            
            <div class="filter-item">
                <button type="button" class="smarttable-apply-filters"><?php esc_html_e( 'Apply Filters', 'smart-product-table' ); ?></button>
                <button type="button" class="smarttable-reset-filters"><?php esc_html_e( 'Reset', 'smart-product-table' ); ?></button>
            </div>
            
        </div>
    </div>
    <?php endif; ?>

    <!-- Bulk Cart Controls -->
    <?php 
    $show_bulk_cart = isset( $context['post_id'] ) ? get_post_meta( $context['post_id'], '_smarttable_show_bulk_cart', true ) : '1';
    if ( $show_bulk_cart === '1' ) :
    ?>
    <div class="smarttable-bulk-controls">
        <div class="bulk-actions">
            <label class="select-all-container">
                <input type="checkbox" class="smarttable-select-all">
                <span><?php esc_html_e( 'Select All', 'smart-product-table' ); ?></span>
            </label>
            <button type="button" class="smarttable-bulk-cart" disabled>
                <i class="dashicons dashicons-cart"></i>
                <?php esc_html_e( 'Add Selected to Cart', 'smart-product-table' ); ?>
                <span class="selected-count">(0)</span>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Product Table -->
    <div class="smarttable-results">
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
        
        <?php
        // Initial pagination using admin setting
        $total_query = new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        $total_products = $total_query->found_posts;
        $total_pages = ceil( $total_products / $per_page );
        $current_page = 1;
        
        // Check if pagination is enabled
        $pagination_enabled = isset( $context['post_id'] ) ? get_post_meta( $context['post_id'], '_smarttable_enable_pagination', true ) : '1';
        
        if ( $total_pages > 1 && $pagination_enabled === '1' ) :
        ?>
        <div class="smarttable-pagination" data-post-id="<?php echo esc_attr( $context['post_id'] ?? 0 ); ?>">
            <?php if ( $current_page > 1 ) : ?>
                <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $current_page - 1 ); ?>">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php 
            $start_page = max( 1, $current_page - 2 );
            $end_page = min( $total_pages, $current_page + 2 );
            
            for ( $i = $start_page; $i <= $end_page; $i++ ) :
                if ( $i == $current_page ) : ?>
                    <span class="smarttable-page-current"><?php echo $i; ?></span>
                <?php else : ?>
                    <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $i ); ?>"><?php echo $i; ?></a>
                <?php endif;
            endfor; ?>
            
            <?php if ( $current_page < $total_pages ) : ?>
                <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $current_page + 1 ); ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.smarttable-search-box {
    background: #fff;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-container {
    display: flex;
    gap: 10px;
    align-items: center;
    max-width: 500px;
}

.smarttable-search {
    flex: 1;
    padding: 10px 15px;
    border: 2px solid #007cba;
    border-radius: 25px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s ease;
}

.smarttable-search:focus {
    border-color: #005a87;
    box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
}

.smarttable-search-btn,
.smarttable-clear-search {
    padding: 10px 20px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.smarttable-search-btn {
    background: #007cba;
    color: white;
}

.smarttable-search-btn:hover {
    background: #005a87;
    transform: translateY(-1px);
}

.smarttable-clear-search {
    background: #6c757d;
    color: white;
}

.smarttable-clear-search:hover {
    background: #545b62;
}

.smarttable-bulk-controls {
    background: #fff;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.bulk-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.select-all-container {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 500;
    color: #1d2327;
}

.select-all-container input[type="checkbox"] {
    margin: 0;
    transform: scale(1.1);
}

.smarttable-bulk-cart {
    padding: 10px 20px;
    background: #007cba;
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.smarttable-bulk-cart:hover:not(:disabled) {
    background: #005a87;
    transform: translateY(-1px);
}

.smarttable-bulk-cart:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.selected-count {
    background: rgba(255,255,255,0.2);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.smarttable-col-select {
    width: 40px;
    text-align: center;
}

.smarttable-product-select,
.smarttable-header-select {
    transform: scale(1.1);
    cursor: pointer;
}

.smarttable-frontend-filters {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-item label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.filter-item select,
.filter-item input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.price-filter {
    display: flex;
    align-items: center;
    gap: 8px;
}

.price-filter input {
    flex: 1;
}

.price-filter span {
    color: #6c757d;
    font-weight: 500;
}

.smarttable-apply-filters,
.smarttable-reset-filters {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    margin-right: 8px;
}

.smarttable-apply-filters {
    background: #007cba;
    color: white;
}

.smarttable-apply-filters:hover {
    background: #005a87;
}

.smarttable-reset-filters {
    background: #6c757d;
    color: white;
}

.smarttable-reset-filters:hover {
    background: #545b62;
}

.smarttable-pagination {
    margin-top: 20px;
    text-align: center;
    padding: 15px 0;
}

.smarttable-pagination a,
.smarttable-pagination span {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 2px;
    text-decoration: none;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #007cba;
    transition: all 0.3s ease;
}

.smarttable-pagination a:hover {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

.smarttable-page-current {
    background: #007cba !important;
    color: white !important;
    border-color: #007cba !important;
    font-weight: bold;
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .price-filter {
        flex-direction: column;
        gap: 5px;
    }
    
    .price-filter span {
        display: none;
    }
    
    .smarttable-pagination a,
    .smarttable-pagination span {
        padding: 6px 10px;
        font-size: 14px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    function applyFilters() {
        var postId = $('.smarttable-wrapper').data('post-id');
        var filters = {};
        
        $('.smarttable-filter').each(function() {
            var filterType = $(this).data('filter');
            var value = $(this).val();
            if (value && value !== '') {
                filters[filterType] = value;
            }
        });
        
        console.log('Applying filters:', filters);
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'smarttable_filter_products',
                post_id: postId,
                filters: filters,
                nonce: '<?php echo wp_create_nonce('smarttable_filter'); ?>'
            },
            beforeSend: function() {
                $('.smarttable-results').html('<div style="text-align:center;padding:20px;">Loading...</div>');
            },
            success: function(response) {
                console.log('Filter response:', response);
                if (response.success) {
                    $('.smarttable-results').html(response.data);
                } else {
                    $('.smarttable-results').html('<div>Error loading products</div>');
                }
            },
            error: function() {
                $('.smarttable-results').html('<div>Error loading products</div>');
            }
        });
    }
    
    $('.smarttable-apply-filters').on('click', function(e) {
        e.preventDefault();
        applyFilters();
    });
    
    $('.smarttable-reset-filters').on('click', function(e) {
        e.preventDefault();
        $('.smarttable-filter').val('');
        applyFilters();
    });
    
    // Auto-apply on Enter key
    $('.smarttable-filter').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            applyFilters();
        }
    });
    
    // Search functionality
    $('.smarttable-search-btn').on('click', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    $('.smarttable-clear-search').on('click', function(e) {
        e.preventDefault();
        $('.smarttable-search').val('');
        performSearch();
    });
    
    $('.smarttable-search').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            performSearch();
        }
    });
    
    function performSearch() {
        var searchTerm = $('.smarttable-search').val();
        var postId = $('.smarttable-wrapper').data('post-id');
        var filters = {};
        
        // Get current filters
        $('.smarttable-filter').each(function() {
            var filterType = $(this).data('filter');
            var value = $(this).val();
            if (value && value !== '') {
                filters[filterType] = value;
            }
        });
        
        // Add search term
        if (searchTerm && searchTerm !== '') {
            filters['search'] = searchTerm;
        }
        
        console.log('Searching for:', searchTerm);
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'smarttable_filter_products',
                post_id: postId,
                filters: filters,
                page: 1,
                nonce: '<?php echo wp_create_nonce('smarttable_filter'); ?>'
            },
            beforeSend: function() {
                $('.smarttable-results').html('<div style="text-align:center;padding:20px;">Searching...</div>');
            },
            success: function(response) {
                if (response.success) {
                    $('.smarttable-results').html(response.data);
                }
            }
        });
    }
    
    // Pagination click handler
    $(document).on('click', '.smarttable-page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        var postId = $('.smarttable-wrapper').data('post-id');
        var filters = {};
        
        $('.smarttable-filter').each(function() {
            var filterType = $(this).data('filter');
            var value = $(this).val();
            if (value && value !== '') {
                filters[filterType] = value;
            }
        });
        
        // Preserve search term
        var searchTerm = $('.smarttable-search').val();
        if (searchTerm && searchTerm !== '') {
            filters['search'] = searchTerm;
        }
        
        console.log('Loading page:', page);
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'smarttable_filter_products',
                post_id: postId,
                filters: filters,
                page: page,
                nonce: '<?php echo wp_create_nonce('smarttable_filter'); ?>'
            },
            beforeSend: function() {
                $('.smarttable-results').html('<div style="text-align:center;padding:20px;">Loading...</div>');
            },
            success: function(response) {
                if (response.success) {
                    $('.smarttable-results').html(response.data);
                    $('html, body').animate({
                        scrollTop: $('.smarttable-wrapper').offset().top - 50
                    }, 500);
                }
            }
        });
    });
    
    // Bulk cart functionality
    $('.smarttable-select-all, .smarttable-header-select').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.smarttable-product-select').prop('checked', isChecked);
        updateBulkCartButton();
    });
    
    $(document).on('change', '.smarttable-product-select', function() {
        updateBulkCartButton();
        
        // Update select all checkbox
        var totalProducts = $('.smarttable-product-select').length;
        var selectedProducts = $('.smarttable-product-select:checked').length;
        
        $('.smarttable-select-all, .smarttable-header-select').prop('checked', totalProducts === selectedProducts);
    });
    
    $('.smarttable-bulk-cart').on('click', function() {
        var selectedProducts = [];
        
        $('.smarttable-product-select:checked').each(function() {
            selectedProducts.push({
                id: $(this).val(),
                quantity: 1
            });
        });
        
        if (selectedProducts.length === 0) {
            alert('Please select products to add to cart.');
            return;
        }
        
        console.log('Adding to cart:', selectedProducts);
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'smarttable_bulk_add_to_cart',
                products: selectedProducts,
                nonce: '<?php echo wp_create_nonce('smarttable_bulk_cart'); ?>'
            },
            beforeSend: function() {
                $('.smarttable-bulk-cart').prop('disabled', true).html('<i class="dashicons dashicons-update"></i> Adding...');
            },
            success: function(response) {
                if (response.success) {
                    alert('Products added to cart successfully!');
                    $('.smarttable-product-select:checked').prop('checked', false);
                    updateBulkCartButton();
                } else {
                    alert('Error adding products to cart: ' + response.data);
                }
            },
            error: function() {
                alert('Error adding products to cart.');
            },
            complete: function() {
                $('.smarttable-bulk-cart').prop('disabled', false).html('<i class="dashicons dashicons-cart"></i> Add Selected to Cart <span class="selected-count">(0)</span>');
            }
        });
    });
    
    function updateBulkCartButton() {
        var selectedCount = $('.smarttable-product-select:checked').length;
        var button = $('.smarttable-bulk-cart');
        
        if (selectedCount > 0) {
            button.prop('disabled', false);
            button.find('.selected-count').text('(' + selectedCount + ')');
        } else {
            button.prop('disabled', true);
            button.find('.selected-count').text('(0)');
        }
    }
});
</script>