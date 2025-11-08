<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Title column – renders the product title linked to the product page.
 */
class TitleColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'title', __( 'Title', 'smart-product-table' ) );
    }

    /**
     * Render the product title cell.
     *
     * @param \WC_Product $product
     * @param array       $context
     * @return string
     */
    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\\WC_Product' ) ) {
            return '';
        }

        $name = $product->get_name();
        $url  = get_permalink( $product->get_id() );

        $name = esc_html( $name );
        $url  = esc_url( $url );

        return '<a class="smarttable-title" href="' . $url . '">' . $name . '</a>';
    }
}