<?php
namespace SmartTable\Shortcode;

use SmartTable\Core\ColumnManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Registers and renders the [smarttable_table] shortcode.
 *
 * Usage:
 * [smarttable_table
 *    columns="image,title,price,sku,add_to_cart"
 *    limit="10"
 *    category="hoodies,tshirts"
 *    tag="featured"
 *    orderby="date"
 *    order="DESC"
 *    image_size="woocommerce_thumbnail"
 * ]
 */

class TableShortcode {

    /**
     * Hook the shortcode registration.
     */
    public function init(){
       
        add_shortcode( 'smarttable', [ $this, 'render' ] );
        add_shortcode( 'smarttable_simple', [ $this, 'render_simple' ] );
    }

    /**
     * Render the table by delegating to the template and ColumnManager.
     *
     * @param array $atts
     * @return string
     */
    public function render( $atts ) : string {
        $post_id = isset( $atts['id'] ) ? absint( $atts['id'] ) : 0;
        if ( ! $post_id || get_post_type( $post_id ) !== 'smarttable_product' ) {
            return '<div class="smarttable-error">Invalid or missing Product Table ID.</div>';
        }

        $meta = get_post_custom( $post_id );

        $layout_json = $meta['_smarttable_column_layout'][0] ?? '';
        $layout = json_decode( $layout_json, true );

        $columns = [];
        if ( is_array( $layout ) ) {
            foreach ( $layout as $item ) {
                if ( ! is_array( $item ) || empty( $item['type'] ) ) {
                    continue;
                }
                $column_id = sanitize_key( (string) $item['type'] );
                if ( $column_id === '' ) {
                    continue;
                }
                $columns[] = $column_id;
            }
        }

        $limit      = $meta['smarttable_limit'][0] ?? '';
        $category   = $meta['smarttable_categories'][0] ?? '';
        $tag        = $meta['smarttable_tags'][0] ?? '';
        $orderby    = $meta['smarttable_orderby'][0] ?? '';
        $order      = $meta['smarttable_order'][0] ?? '';
        $image_size = $meta['smarttable_image_size'][0] ?? '';

        $filter_cats   = $meta['_smarttable_filter_categories'][0] ?? '';
        $filter_tags   = $meta['_smarttable_filter_tags'][0] ?? '';
        $include_ids   = $meta['_smarttable_include_ids'][0] ?? '';
        $exclude_ids   = $meta['_smarttable_exclude_ids'][0] ?? '';

        $enable_pagination = $meta['_smarttable_enable_pagination'][0] ?? '';
        $products_per_page = $meta['_smarttable_products_per_page'][0] ?? '';
        $default_sort      = $meta['_smarttable_default_sort'][0] ?? '';
        $tax_query_relation = $meta['_smarttable_tax_query_relation'][0] ?? 'AND';
        $design_style      = $meta['_smarttable_design_style'][0] ?? 'default';
        
        // Get admin default if metabox value is empty
        $admin_posts_per_page = class_exists( '\SmartTable\Admin\Settings' ) ? \SmartTable\Admin\Settings::get_posts_per_page() : 10;
        $final_products_per_page = (int) $products_per_page ?: $admin_posts_per_page;

        // Get price filter settings
        $min_price = get_post_meta( $post_id, '_smarttable_min_price', true );
        $max_price = get_post_meta( $post_id, '_smarttable_max_price', true );
        
        $context = [
            'post_id'           => $post_id,
            'columns'           => $columns,
            'limit'             => (int) $limit ?: $final_products_per_page,
            'category'          => $this->normalize_list( $category ),
            'tag'               => $this->normalize_list( $tag ),
            'orderby'           => sanitize_key( (string) $orderby ),
            'order'             => strtoupper( (string) $order ) === 'ASC' ? 'ASC' : 'DESC',
            'image_size'        => sanitize_key( (string) $image_size ) ?: 'woocommerce_thumbnail',
            'filter_categories' => $this->normalize_list( $filter_cats ),
            'filter_tags'       => $this->normalize_list( $filter_tags ),
            'include_ids'       => array_filter( array_map( 'absint', explode( ',', $include_ids ) ) ),
            'exclude_ids'       => array_filter( array_map( 'absint', explode( ',', $exclude_ids ) ) ),
            'enable_pagination'  => $enable_pagination === '1' ? '1' : '0',
            'products_per_page'  => $final_products_per_page,
            'default_sort'       => sanitize_key( $default_sort ),
            'paged'              => isset( $atts['paged'] ) ? max( 1, (int) $atts['paged'] ) : 1,
            'tax_query_relation' => in_array( strtoupper( $tax_query_relation ), ['AND', 'OR'], true ) ? strtoupper( $tax_query_relation ) : 'AND',
            'design_style'       => sanitize_key( $design_style ),
            'min_price'         => floatval( $min_price ),
            'max_price'         => floatval( $max_price ),
        ];

        // Ensure ColumnManager is booted.
        if ( class_exists( '\\SmartTable\\Core\\ColumnManager' ) ) {
            ColumnManager::instance()->boot();
        }

        /**
         * Allow last‑minute customization of context from themes/plugins.
         *
         * @param array $context
         * @param array $atts
         */
        $context = apply_filters( 'smarttable_table_shortcode_context', $context, $atts );

        // Render via template.
        return $this->render_template( 'table.php', $context );
    }

    /**
     * Simple shortcode handler for direct column specification
     * Usage: [smarttable_simple columns="image,title,category,price" limit="10"]
     */
    public function render_simple( $atts ) : string {
        $atts = shortcode_atts( [
            'columns' => 'image,title,category,stock_status,price,add_to_cart',
            'limit' => 10,
            'category' => '',
            'tag' => '',
            'orderby' => 'date',
            'order' => 'DESC'
        ], $atts, 'smarttable_simple' );

        $admin_posts_per_page = class_exists( '\SmartTable\Admin\Settings' ) ? \SmartTable\Admin\Settings::get_posts_per_page() : 10;
        $limit = (int) $atts['limit'];
        
        $context = [
            'columns' => $atts['columns'],
            'limit' => $limit,
            'category' => $this->normalize_list( $atts['category'] ),
            'tag' => $this->normalize_list( $atts['tag'] ),
            'orderby' => sanitize_key( $atts['orderby'] ),
            'order' => strtoupper( $atts['order'] ) === 'ASC' ? 'ASC' : 'DESC',
            'enable_pagination' => '1',
            'products_per_page' => $limit > 0 ? $limit : $admin_posts_per_page,
        ];

        // Ensure ColumnManager is booted
        if ( class_exists( '\\SmartTable\\Core\\ColumnManager' ) ) {
            ColumnManager::instance()->boot();
        }

        return $this->render_template( 'table.php', $context );
    }

    /**
     * Load a template from the plugin's templates directory and return output.
     *
     * @param string $template
     * @param array  $context
     * @return string
     */
    protected function render_template( string $template, array $context = [] ) : string {
        $base_dir = defined( 'SMARTTABLE_PLUGIN_DIR' )
            ? SMARTTABLE_PLUGIN_DIR
            : trailingslashit( dirname( __DIR__, 2 ) );

        $template_path = trailingslashit( $base_dir ) . 'templates/' . ltrim( $template, '/' );

        if ( ! file_exists( $template_path ) ) {
            return '<div class="smarttable-error">Template not found.</div>';
        }

        ob_start();
        /** @var array $context */
        include $template_path;
        return (string) ob_get_clean();
    }

    /**
     * Accepts comma-separated string or array; returns array of sanitized slugs.
     *
     * @param string|array $value
     * @return array
     */
    protected function normalize_list( $value ) : array {
        if ( is_array( $value ) ) {
            return array_values( array_filter( array_map( 'sanitize_title', $value ) ) );
        }
        $value = (string) $value;
        if ( $value === '' ) {
            return [];
        }
        $parts = array_map( 'trim', explode( ',', $value ) );
        return array_values( array_filter( array_map( 'sanitize_title', $parts ) ) );
    }
}
