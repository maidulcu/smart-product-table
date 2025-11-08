<?php
namespace SmartTable\Columns;

use SmartTable\Interfaces\ColumnInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TagsColumn extends BaseColumn implements ColumnInterface {

    public function __construct() {
        parent::__construct( 'tags', __( 'Tags', 'smart-product-table' ) );
    }

    public function render_cell( $product, array $context = [] ) : string {
        if ( ! $product || ! is_a( $product, '\\WC_Product' ) ) {
            return '';
        }

        $tags = get_the_terms( $product->get_id(), 'product_tag' );
        
        if ( empty( $tags ) || is_wp_error( $tags ) ) {
            return '<span class="smarttable-no-tags">' . esc_html__( 'No tags', 'smart-product-table' ) . '</span>';
        }

        $tag_links = [];
        foreach ( $tags as $tag ) {
            $tag_url = get_term_link( $tag );
            if ( ! is_wp_error( $tag_url ) ) {
                $tag_links[] = '<a class="smarttable-tag-link" href="' . esc_url( $tag_url ) . '">' . esc_html( $tag->name ) . '</a>';
            } else {
                $tag_links[] = '<span class="smarttable-tag-name">' . esc_html( $tag->name ) . '</span>';
            }
        }

        return '<div class="smarttable-tags">' . implode( ', ', $tag_links ) . '</div>';
    }
}