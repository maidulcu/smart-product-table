<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Category column – renders the product categories.
 */
class CategoryColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'category', __( 'Category', 'smart-product-table' ) );
    }

    /**
     * Render the product category cell.
     *
     * @param \WC_Product $product
     * @param array       $context
     * @return string
     */
    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\\WC_Product' ) ) {
            return '';
        }

        $categories = get_the_terms( $product->get_id(), 'product_cat' );
        
        if ( empty( $categories ) || is_wp_error( $categories ) ) {
            return '<span class="smarttable-no-category">' . esc_html__( 'Uncategorized', 'smart-product-table' ) . '</span>';
        }

        $category_links = [];
        foreach ( $categories as $category ) {
            $category_url = get_term_link( $category );
            if ( ! is_wp_error( $category_url ) ) {
                $category_links[] = '<a class="smarttable-category-link" href="' . esc_url( $category_url ) . '">' . esc_html( $category->name ) . '</a>';
            } else {
                $category_links[] = '<span class="smarttable-category-name">' . esc_html( $category->name ) . '</span>';
            }
        }

        return '<div class="smarttable-categories">' . implode( ', ', $category_links ) . '</div>';
    }
}