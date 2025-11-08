<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add to Cart column – renders WooCommerce add-to-cart button/link.
 */
class AddToCartColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'add_to_cart', __( 'Add to cart', 'smart-product-table' ) );
    }

    /**
     * Render the add-to-cart cell for a product.
     *
     * @param \WC_Product $product
     * @param array       $context
     * @return string
     */
    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\WC_Product' ) ) {
            return '';
        }

        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            return '<span class="smarttable-out-of-stock">' . esc_html__( 'Out of stock', 'smart-product-table' ) . '</span>';
        }

        $quantity = isset( $context['quantity'] ) ? max( 1, (int) $context['quantity'] ) : 1;

        // Build classes and attributes similarly to WooCommerce loop button.
        $classes = array_filter( [
            'button',
            'smarttable-add-to-cart',
            'product_type_' . $product->get_type(),
            $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
            $product->is_type( 'simple' ) ? 'add_to_cart_button' : '',
        ] );

        $attributes = [
            'data-product_id'  => $product->get_id(),
            'data-product_sku' => $product->get_sku(),
            'data-quantity'    => $quantity,
            'aria-label'       => wp_strip_all_tags( $product->add_to_cart_description() ),
            'rel'              => 'nofollow',
        ];

        $args = [
            'class'      => implode( ' ', $classes ),
            'attributes' => $attributes,
            'quantity'   => $quantity,
        ];

        $url  = $product->add_to_cart_url();
        $text = $product->add_to_cart_text();

        // Fallback for wc_implode_html_attributes if needed (very old WC versions).
        if ( ! function_exists( 'wc_implode_html_attributes' ) ) {
            $attr_str = '';
            foreach ( $attributes as $k => $v ) {
                $attr_str .= ' ' . esc_attr( $k ) . '="' . esc_attr( (string) $v ) . '"';
            }
        } else {
            $attr_str = wc_implode_html_attributes( $attributes );
        }

        $link = sprintf(
            '<a href="%1$s" data-quantity="%2$s" class="%3$s"%4$s>%5$s</a>',
            esc_url( $url ),
            esc_attr( (string) $quantity ),
            esc_attr( $args['class'] ),
            $attr_str ? ' ' . $attr_str : '',
            esc_html( $text )
        );

        /**
         * Filter matches WooCommerce convention for 3rd-party compatibility.
         *
         * @param string     $link
         * @param \WC_Product $product
         * @param array      $args
         */
        $link = apply_filters( 'woocommerce_loop_add_to_cart_link', $link, $product, $args );

        return '<span class="smarttable-addtocart-wrap">' . $link . '</span>';
    }
}