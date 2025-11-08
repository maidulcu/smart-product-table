<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RatingColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'rating', __( 'Rating', 'smart-product-table' ) );
    }

    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\\WC_Product' ) ) {
            return '';
        }

        $average_rating = $product->get_average_rating();
        $rating_count = $product->get_rating_count();
        
        if ( ! $average_rating ) {
            return '<span class="smarttable-no-rating">' . esc_html__( 'No rating', 'smart-product-table' ) . '</span>';
        }

        $stars = '';
        for ( $i = 1; $i <= 5; $i++ ) {
            if ( $i <= $average_rating ) {
                $stars .= '<span class="star filled">★</span>';
            } elseif ( $i - 0.5 <= $average_rating ) {
                $stars .= '<span class="star half">★</span>';
            } else {
                $stars .= '<span class="star empty">☆</span>';
            }
        }

        $rating_html = '<div class="smarttable-rating">';
        $rating_html .= '<div class="stars">' . $stars . '</div>';
        $rating_html .= '<span class="rating-text">(' . number_format( $average_rating, 1 ) . ')</span>';
        if ( $rating_count > 0 ) {
            $rating_html .= '<span class="rating-count"> - ' . $rating_count . ' ' . _n( 'review', 'reviews', $rating_count, 'smart-product-table' ) . '</span>';
        }
        $rating_html .= '</div>';

        return $rating_html;
    }
}