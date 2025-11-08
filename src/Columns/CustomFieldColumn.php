<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CustomFieldColumn extends BaseColumn implements ColumnInterface {

    protected $meta_key;
    protected $fallback;

    public function __construct( $meta_key = '', $label = '', $fallback = '' ) {
        $this->meta_key = $meta_key;
        $this->fallback = $fallback;
        $display_label = $label ?: ( $meta_key ?: __( 'Custom Field', 'smart-product-table' ) );
        parent::__construct( 'custom_field', $display_label );
    }

    public function set_meta_key( $meta_key ) {
        $this->meta_key = $meta_key;
        return $this;
    }

    public function set_fallback( $fallback ) {
        $this->fallback = $fallback;
        return $this;
    }

    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\\WC_Product' ) ) {
            return '';
        }

        // Get meta key from context if not set
        $meta_key = $this->meta_key;
        if ( empty( $meta_key ) && isset( $context['meta_key'] ) ) {
            $meta_key = $context['meta_key'];
        }

        if ( empty( $meta_key ) ) {
            return '<span class="smarttable-no-meta">' . esc_html__( 'No meta key specified', 'smart-product-table' ) . '</span>';
        }

        $value = get_post_meta( $product->get_id(), $meta_key, true );
        
        if ( empty( $value ) ) {
            $fallback = $this->fallback ?: ( $context['fallback'] ?? __( 'N/A', 'smart-product-table' ) );
            return '<span class="smarttable-custom-fallback">' . esc_html( $fallback ) . '</span>';
        }

        // Handle different value types
        if ( is_array( $value ) ) {
            $value = implode( ', ', array_map( 'esc_html', $value ) );
        } else {
            $value = esc_html( $value );
        }

        return '<div class="smarttable-custom-field">' . $value . '</div>';
    }
}