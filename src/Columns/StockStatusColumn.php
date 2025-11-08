<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class StockStatusColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'stock_status', __( 'Stock Status', 'smart-product-table' ) );
    }

    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\\WC_Product' ) ) {
            return '';
        }

        $stock_status = $product->get_stock_status();
        $stock_quantity = $product->get_stock_quantity();
        
        $class = 'smarttable-stock-' . $stock_status;
        $text = '';
        
        switch ( $stock_status ) {
            case 'instock':
                $text = __( 'In Stock', 'smart-product-table' );
                if ( $stock_quantity ) {
                    $text .= ' (' . $stock_quantity . ')';
                }
                break;
            case 'outofstock':
                $text = __( 'Out of Stock', 'smart-product-table' );
                break;
            case 'onbackorder':
                $text = __( 'On Backorder', 'smart-product-table' );
                break;
            default:
                $text = ucfirst( str_replace( '_', ' ', $stock_status ) );
        }

        return '<span class="' . esc_attr( $class ) . '">' . esc_html( $text ) . '</span>';
    }
}