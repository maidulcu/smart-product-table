<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ShortDescriptionColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'short_description', __( 'Short Description', 'smart-product-table' ) );
    }

    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\\WC_Product' ) ) {
            return '';
        }

        $description = $product->get_short_description();
        
        if ( empty( $description ) ) {
            return '<span class="smarttable-no-description">' . esc_html__( 'No description', 'smart-product-table' ) . '</span>';
        }

        // Limit description length
        $max_length = 100;
        if ( strlen( $description ) > $max_length ) {
            $description = substr( $description, 0, $max_length ) . '...';
        }

        return '<div class="smarttable-short-description">' . wp_kses_post( $description ) . '</div>';
    }
}