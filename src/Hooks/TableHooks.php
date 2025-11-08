<?php
namespace SmartTable\Hooks;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TableHooks {

    /**
     * Render filter section
     */
    public static function render_filters( $context = [] ) {
        $url_filter_category = isset( $context['url_filter_category'] ) ? $context['url_filter_category'] : ( isset( $_GET['filter_category'] ) ? sanitize_text_field( $_GET['filter_category'] ) : '' );
        $url_min_price = isset( $context['url_min_price'] ) ? $context['url_min_price'] : ( isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : 0 );
        $url_max_price = isset( $context['url_max_price'] ) ? $context['url_max_price'] : ( isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : 0 );
        ?>
        <div class="smarttable-filters">
            <form class="smarttable-filter-form">
                <div class="filter-row">
                    <div class="filter-field">
                        <label><?php esc_html_e( 'Category:', 'smart-product-table' ); ?></label>
                        <select name="filter_category" class="smarttable-category-filter">
                            <option value=""><?php esc_html_e( 'All Categories', 'smart-product-table' ); ?></option>
                            <?php
                            $categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
                            foreach ( $categories as $cat ) {
                                $selected = $url_filter_category === $cat->slug ? 'selected' : '';
                                echo '<option value="' . esc_attr( $cat->slug ) . '" ' . $selected . '>' . esc_html( $cat->name ) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label><?php esc_html_e( 'Min Price:', 'smart-product-table' ); ?></label>
                        <input type="number" name="min_price" value="<?php echo esc_attr( $url_min_price ?: '' ); ?>" placeholder="0" min="0" step="0.01">
                    </div>
                    <div class="filter-field">
                        <label><?php esc_html_e( 'Max Price:', 'smart-product-table' ); ?></label>
                        <input type="number" name="max_price" value="<?php echo esc_attr( $url_max_price ?: '' ); ?>" placeholder="1000" min="0" step="0.01">
                    </div>
                    <div class="filter-field">
                        <button type="button" class="smarttable-filter-btn"><?php esc_html_e( 'Apply Filter', 'smart-product-table' ); ?></button>
                        <button type="button" class="smarttable-clear-btn"><?php esc_html_e( 'Clear', 'smart-product-table' ); ?></button>
                    </div>
                </div>
            </form>
            
            <!-- Bulk Actions -->
            <div class="smarttable-bulk-actions">
                <button type="button" class="smarttable-bulk-cart" disabled><?php esc_html_e( 'Add Selected to Cart', 'smart-product-table' ); ?></button>
                <span class="selected-count">0 selected</span>
            </div>
        </div>
        <?php
    }

    /**
     * Render pagination
     */
    public static function render_pagination( $total_products, $products_per_page, $paged, $context = [] ) {
        if ( $total_products <= $products_per_page ) return;
        
        $total_pages = ceil( $total_products / $products_per_page );
        $current_page = $paged;
        
        $requested_columns_attr = isset( $context['columns'] ) ? ( is_array( $context['columns'] ) ? implode( ',', $context['columns'] ) : $context['columns'] ) : '';
        $category = isset( $context['category'] ) ? $context['category'] : [];
        $tag = isset( $context['tag'] ) ? $context['tag'] : [];
        $filter_cats = isset( $context['filter_categories'] ) ? $context['filter_categories'] : [];
        $filter_tags = isset( $context['filter_tags'] ) ? $context['filter_tags'] : [];
        $orderby = isset( $context['orderby'] ) ? $context['orderby'] : '';
        $order = isset( $context['order'] ) ? $context['order'] : 'DESC';
        ?>
        <div class="smarttable-pagination" 
             data-columns="<?php echo esc_attr( $requested_columns_attr ); ?>"
             data-limit="<?php echo esc_attr( $products_per_page ); ?>"
             data-category="<?php echo esc_attr( is_array( $category ) ? implode( ',', $category ) : $category ); ?>"
             data-tag="<?php echo esc_attr( is_array( $tag ) ? implode( ',', $tag ) : $tag ); ?>"
             data-filter-categories="<?php echo esc_attr( is_array( $filter_cats ) ? implode( ',', $filter_cats ) : '' ); ?>"
             data-filter-tags="<?php echo esc_attr( is_array( $filter_tags ) ? implode( ',', $filter_tags ) : '' ); ?>"
             data-orderby="<?php echo esc_attr( $orderby ); ?>"
             data-order="<?php echo esc_attr( $order ); ?>"
             data-post-id="<?php echo esc_attr( isset( $context['post_id'] ) ? $context['post_id'] : 0 ); ?>">
            <?php if ( $current_page > 1 ) : ?>
                <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $current_page - 1 ); ?>">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php 
            $start_page = max( 1, $current_page - 2 );
            $end_page = min( $total_pages, $current_page + 2 );
            
            if ( $start_page > 1 ) {
                echo '<a href="#" class="smarttable-page-link" data-page="1">1</a>';
                if ( $start_page > 2 ) {
                    echo '<span class="smarttable-dots">...</span>';
                }
            }
            
            for ( $i = $start_page; $i <= $end_page; $i++ ) :
                if ( $i == $current_page ) : ?>
                    <span class="smarttable-page-current"><?php echo $i; ?></span>
                <?php else : ?>
                    <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $i ); ?>"><?php echo $i; ?></a>
                <?php endif;
            endfor;
            
            if ( $end_page < $total_pages ) {
                if ( $end_page < $total_pages - 1 ) {
                    echo '<span class="smarttable-dots">...</span>';
                }
                echo '<a href="#" class="smarttable-page-link" data-page="' . esc_attr( $total_pages ) . '">' . $total_pages . '</a>';
            }
            ?>
            
            <?php if ( $current_page < $total_pages ) : ?>
                <a href="#" class="smarttable-page-link" data-page="<?php echo esc_attr( $current_page + 1 ); ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Add select column header
     */
    public static function render_select_header() {
        ?>
        <th class="smarttable-col smarttable-col-select">
            <input type="checkbox" class="smarttable-select-all" title="<?php esc_attr_e( 'Select All', 'smart-product-table' ); ?>">
        </th>
        <?php
    }

    /**
     * Add select column cell
     */
    public static function render_select_cell( $product ) {
        ?>
        <td class="smarttable-cell smarttable-col-select">
            <input type="checkbox" class="smarttable-product-select" value="<?php echo esc_attr( $product->get_id() ); ?>" data-price="<?php echo esc_attr( $product->get_price() ); ?>">
        </td>
        <?php
    }
}