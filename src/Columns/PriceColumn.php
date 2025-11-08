<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Price column – renders the product price HTML (handles sales, variable products, taxes).
 */
class PriceColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'price', __( 'Price', 'smart-product-table' ) );
    }

    /**
     * Render the product price cell.
     *
     * @param \WC_Product $product
     * @param array       $context
     * @return string
     */
    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\WC_Product' ) ) {
            return '';
        }

        // WooCommerce provides formatted price HTML that already handles:
        // - on sale display (regular price vs sale price)
        // - variable product "From: " price range
        // - tax display settings
        $price_html = $product->get_price_html();

        if ( empty( $price_html ) ) {
            // Fallback: show "—" if price is not set (e.g., external product without price).
            $price_html = '&mdash;';
        }

        // Wrap for styling.
        return '<span class="smarttable-price">' . wp_kses_post( $price_html ) . '</span>';
    }
}