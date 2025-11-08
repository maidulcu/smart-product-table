<?php
namespace SmartTable\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BulkCartHandler {
    
    public function __construct() {
        add_action( 'wp_ajax_smarttable_bulk_add_to_cart', [ $this, 'handle_bulk_add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_smarttable_bulk_add_to_cart', [ $this, 'handle_bulk_add_to_cart' ] );
    }
    
    public function handle_bulk_add_to_cart() {
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'smarttable_bulk_cart' ) ) {
            wp_send_json_error( 'Security check failed' );
        }
        
        $products = $_POST['products'] ?? [];
        
        if ( empty( $products ) || ! is_array( $products ) ) {
            wp_send_json_error( 'No products selected' );
        }
        
        // Check if WooCommerce cart is available
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            wp_send_json_error( 'WooCommerce cart not available' );
        }
        
        $added_count = 0;
        $errors = [];
        
        foreach ( $products as $product_data ) {
            $product_id = absint( $product_data['id'] ?? 0 );
            $quantity = absint( $product_data['quantity'] ?? 1 );
            
            if ( ! $product_id ) {
                continue;
            }
            
            // Get product
            $product = wc_get_product( $product_id );
            
            if ( ! $product || ! $product->is_purchasable() ) {
                $errors[] = sprintf( 'Product ID %d is not available for purchase', $product_id );
                continue;
            }
            
            // Check stock
            if ( ! $product->is_in_stock() ) {
                $errors[] = sprintf( 'Product "%s" is out of stock', $product->get_name() );
                continue;
            }
            
            // Add to cart
            $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );
            
            if ( $cart_item_key ) {
                $added_count++;
            } else {
                $errors[] = sprintf( 'Failed to add product "%s" to cart', $product->get_name() );
            }
        }
        
        if ( $added_count > 0 ) {
            $message = sprintf( 
                _n( '%d product added to cart', '%d products added to cart', $added_count, 'smart-product-table' ), 
                $added_count 
            );
            
            if ( ! empty( $errors ) ) {
                $message .= ' Some products could not be added: ' . implode( ', ', $errors );
            }
            
            wp_send_json_success( $message );
        } else {
            wp_send_json_error( 'No products could be added to cart: ' . implode( ', ', $errors ) );
        }
    }
}