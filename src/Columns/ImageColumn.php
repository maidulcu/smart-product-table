<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Image column – renders the product thumbnail linked to the product page.
 */
class ImageColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'image', __( 'Image', 'smart-product-table' ) );
    }

    /**
     * Render the image cell.
     *
     * @param \WC_Product $product
     * @param array       $context
     * @return string
     */
    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\WC_Product' ) ) {
            return '';
        }

        // Allow size override via shortcode/context, fallback to WooCommerce thumbnail size.
        $size = isset( $context['image_size'] ) && is_string( $context['image_size'] )
            ? sanitize_key( $context['image_size'] )
            : 'woocommerce_thumbnail';

        // Get product image HTML (WooCommerce handles srcset/sizes).
        $image_html = $product->get_image( $size );

        if ( empty( $image_html ) ) {
            // Fallback to placeholder if no image is set.
            if ( function_exists( 'wc_placeholder_img' ) ) {
                $image_html = wc_placeholder_img( $size );
            } else {
                $image_html = '<span class="smarttable-no-image">&mdash;</span>';
            }
        }

        $permalink = get_permalink( $product->get_id() );

        return sprintf(
            '<a class="smarttable-image" href="%s">%s</a>',
            esc_url( $permalink ),
            wp_kses_post( $image_html )
        );
    }
}