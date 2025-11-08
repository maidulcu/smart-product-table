<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * SKU column – renders the product SKU (or a dash if missing).
 */
class SKUColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'sku', __( 'SKU', 'smart-product-table' ) );
    }

    /**
     * Render the SKU cell.
     *
     * @param \WC_Product $product
     * @param array       $context
     * @return string
     */
    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\WC_Product' ) ) {
            return '';
        }

        $sku = $product->get_sku();

        if ( '' === $sku || null === $sku ) {
            $sku = '&mdash;'; // Show em dash when no SKU
            return '<span class="smarttable-sku smarttable-sku-missing">' . $sku . '</span>';
        }

        return '<span class="smarttable-sku">' . esc_html( $sku ) . '</span>';
    }
}