<?php
namespace SmartTable\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Contract that all SmartTable columns must implement.
 *
 * Each column must provide a stable ID (used for registration and lookups),
 * a human-readable label (shown in the table header), and a method to render
 * the HTML for a single table cell for a given WooCommerce product.
 */
interface ColumnInterface {

    /**
     * Unique, machine-safe identifier for the column.
     * Example: "title", "price", "sku", "image", "add_to_cart".
     *
     * @return string
     */
    public function get_id() : string;

    /**
     * Human-readable label for the column header.
     * Example: "Title", "Price", "SKU".
     *
     * @return string
     */
    public function get_label() : string;

    /**
     * Render the HTML for this column's cell for the given product.
     *
     * @param \WC_Product $product WooCommerce product object.
     * @param array       $context Optional rendering context (e.g., settings).
     * @return string HTML markup for the table cell content.
     */
    public function render_cell( $product, array $context = [] ) : string;
}